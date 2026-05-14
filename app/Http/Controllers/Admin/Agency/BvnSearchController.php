<?php

namespace App\Http\Controllers\Admin\Agency;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\AgentService;
use App\Models\User;
use App\Models\ServiceField;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class BvnSearchController extends Controller
{
    /**
     * List BVN Search requests with filters and pagination
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $bankFilter = $request->input('bank');

        // Base query filtering by service_type with user email join
        $query = AgentService::query()
            ->select('agent_services.*', 'users.email as user_email')
            ->join('users', 'agent_services.user_id', '=', 'users.id')
            ->where('agent_services.service_type', 'bvn_search');

        // Enhanced search: BVN, NIN, transaction_ref, agent name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('agent_services.ticket_id', 'like', "%$search%")
                  ->orWhere('agent_services.batch_id', 'like', "%$search%")
                  ->orWhere('agent_services.reference', 'like', "%$search%")
                  ->orWhere('agent_services.performed_by', 'like', "%$search%")
                  ->orWhere('agent_services.user_id', 'like', "%$search%")
                  ->orWhere('agent_services.number', 'like', "%$search%");
            });
        }

        if ($statusFilter) {
            $query->where('agent_services.status', $statusFilter);
        }

        if ($bankFilter) {
            $query->where('agent_services.bank', $bankFilter);
        }

        // Apply custom status order + submission_date
        $enrollments = $query
            ->orderByRaw("CASE agent_services.status
                WHEN 'pending' THEN 1
                WHEN 'processing' THEN 2
                WHEN 'in-progress' THEN 3
                WHEN 'query' THEN 4
                WHEN 'resolved' THEN 5
                WHEN 'successful' THEN 6
                WHEN 'rejected' THEN 7
                WHEN 'failed' THEN 8
                WHEN 'remark' THEN 9
                ELSE 999 END")
            ->orderByDesc('agent_services.submission_date')
            ->paginate(10);

        // Status counts filtered by service_type
        $statusCounts = [
            'pending'    => AgentService::where('service_type', 'bvn_search')->where('status', 'pending')->count(),
            'processing' => AgentService::where('service_type', 'bvn_search')->where('status', 'processing')->count(),
            'resolved'   => AgentService::where('service_type', 'bvn_search')->whereIn('status', ['resolved', 'successful'])->count(),
            'rejected'   => AgentService::where('service_type', 'bvn_search')->whereIn('status', ['rejected', 'failed'])->count(),
        ];

        // Get distinct banks for filter
        $banks = $this->getDistinctBanks();

        return view('admin.bvn-search.index', compact('enrollments', 'search', 'statusFilter', 'bankFilter', 'statusCounts', 'banks'));
    }

    /**
     * Show details of a single BVN Search request
     */
    public function show($id)
    {
        $enrollmentInfo = AgentService::findOrFail($id);
        $user = User::find($enrollmentInfo->user_id);

        $statusHistory = collect([
            [
                'status' => $enrollmentInfo->status,
                'comment' => $enrollmentInfo->comment,
                'submission_date' => $enrollmentInfo->created_at,
                'updated_at' => $enrollmentInfo->updated_at,
                'file_url' => $enrollmentInfo->file_url,
            ]
        ]);

        return view('admin.bvn-search.show', compact('enrollmentInfo', 'statusHistory', 'user'));
    }

    /**
     * Update the status of a BVN Search request
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,in-progress,resolved,successful,rejected,failed,query,remark',
            'comment' => 'nullable|string',
            'bvn' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
            'force_refund' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $enrollment = AgentService::findOrFail($id);
            $oldStatus = $enrollment->status;
            $user = User::find($enrollment->user_id);

            // Handle file upload
            $fileUrl = $enrollment->file_url;
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($fileUrl && Storage::disk('public')->exists($fileUrl)) {
                    Storage::disk('public')->delete($fileUrl);
                }

                // Store new file
                $file = $request->file('file');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('bvn-search-files', $fileName, 'public');
                $baseUrl = rtrim(config('app.url'), '/');
                $fileUrl = $baseUrl . '/storage/bvn-search-files/' . $fileName;
            }

            // Update enrollment
            $enrollment->status = $request->status;
            $enrollment->comment = $request->comment;
            $enrollment->bvn = $request->bvn; // Update BVN
            $enrollment->file_url = $fileUrl;
            $enrollment->save();

            // Handle refund logic if rejected
            if ($request->status === 'rejected') {
                if ($oldStatus !== 'rejected' || $request->force_refund) {
                    $this->processRefund($enrollment, $request->force_refund);
                }
            }

            DB::commit();
            return redirect()->route('admin.bvn-search.index')
                ->with('successMessage', 'Status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.bvn-search.index')
                ->with('errorMessage', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Handle refund when a request is rejected
     */
    private function processRefund($enrollment, $forceRefund = false)
    {
        $user = User::find($enrollment->user_id);

        if (!$user) {
            throw new \Exception('User not found.');
        }

        $role = strtolower($user->role ?? 'default');

        // Check if refund already exists
        $refundExists = Transaction::where('type', 'refund')
            ->where('description', 'LIKE', "%Request ID #{$enrollment->id}%")
            ->exists();

        if ($refundExists && !$forceRefund) {
            throw new \Exception('Refund already processed for this request.');
        }

        // Use the actual amount paid by the user stored in the enrollment record
        $paidAmount = $enrollment->amount;

        if (!$paidAmount || $paidAmount <= 0) {
            throw new \Exception('No valid payment amount found for refund.');
        }

        $refundAmount = round($paidAmount * 0.8, 2);
        $debitAmount = round($paidAmount * 0.2, 2);

        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

        if (!$wallet) {
            throw new \Exception('Wallet not found for user.');
        }

        // Update wallet balance
        $wallet->balance += $refundAmount;
        $wallet->save();

        // Create refund transaction
        Transaction::create([
            'transaction_ref' => strtoupper(Str::random(12)),
            'user_id' => $user->id,
            'performed_by' => Auth::user()->first_name . ' ' . (Auth::user()->last_name ?? ''),
            'amount' => $refundAmount,
            'fee' => 0.00,
            'net_amount' => $refundAmount,
            'description' => "Refund 80% for rejected service [{$enrollment->service_field_name}], Request ID #{$enrollment->id}",
            'type' => 'refund',
            'status' => 'completed',
            'metadata' => json_encode([
                'service_id' => $enrollment->service_id,
                'service_field_id' => $enrollment->service_field_id,
                'field_code' => $enrollment->field_code,
                'field_name' => $enrollment->service_field_name ?? null,
                'user_role' => $role,
                'total_paid' => $paidAmount,
                'percentage_refunded' => 80,
                'amount_debited_by_system' => $debitAmount,
                'forced_refund' => $forceRefund,
            ]),
        ]);
    }


    /**
     * Check status of a BVN Search request using Arewa Smart API
     */
    public function checkStatus($id)
    {
        try {
            $enrollment = AgentService::findOrFail($id);
            $result = $this->performStatusCheck($enrollment);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully using reference: ' . ($enrollment->reference ?? 'N/A'),
                    'data' => [
                        'status' => $enrollment->status,
                        'comment' => $enrollment->comment,
                        'bvn' => $enrollment->bvn,
                        'updated_at' => $enrollment->updated_at->format('M j, Y g:i A')
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch status from API: ' . ($result['message'] ?? 'Unknown error'),
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch check status for up to 10 requests
     */
    public function checkBatchStatus()
    {
        try {
            $enrollments = AgentService::where('service_type', 'bvn_search')
                ->whereIn('status', ['pending', 'processing', 'in-progress', 'in-prograce', 'query'])
                ->orderBy('created_at', 'asc')
                ->limit(10)
                ->get();

            if ($enrollments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No eligible requests found for batch status check.'
                ], 404);
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($enrollments as $enrollment) {
                $result = $this->performStatusCheck($enrollment);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
                
                // Rate limiting delay (0.5 seconds)
                usleep(500000);
            }

            return response()->json([
                'success' => true,
                'message' => "Batch check completed. Success: {$successCount}, Failed: {$failedCount}.",
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch check failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Core logic to perform status check via API
     */
    private function performStatusCheck($enrollment)
    {
        try {
            $apiToken = env('AREWA_API_TOKEN');
            $baseUrl = env('AREWA_BASE_URL', 'https://api.arewasmart.com.ng/api/v1');
            $endpoint = rtrim($baseUrl, '/') . '/bvn/phone-search';

            $response = Http::withToken($apiToken)
                ->withoutVerifying()
                ->acceptJson()
                ->get($endpoint, [
                    'reference' => $enrollment->reference,
                ]);

            if ($response->successful()) {
                $apiResponse = $response->json();
                $cleanResponse = $this->cleanApiResponse($apiResponse);
                
                $updateData = [
                    'comment' => $cleanResponse,
                ];

                $data = $apiResponse['data'] ?? $apiResponse;

                if (isset($data['status'])) {
                    $updateData['status'] = $this->normalizeStatus($data['status']);
                } elseif (isset($apiResponse['status'])) {
                    $updateData['status'] = $this->normalizeStatus($apiResponse['status']);
                }
                
                if (isset($data['bvn'])) {
                    $updateData['bvn'] = $data['bvn'];
                }

                $enrollment->update($updateData);
                return ['success' => true];
            }

            $lastError = $response->json('message') ?? $response->json('error') ?? 'Record not found or API error.';
            Log::warning("BvnSearch Status Check Failed for ID {$enrollment->id}: " . $lastError);
            return ['success' => false, 'message' => $lastError];

        } catch (\Exception $e) {
            Log::error("BvnSearch Status Check Exception for ID {$enrollment->id}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function cleanApiResponse($response): string
    {
        if (is_array($response)) {
            $data = $response['data'] ?? $response;

            if (isset($data['comment']) && is_string($data['comment'])) {
                return $data['comment'];
            }
            if (isset($response['message']) && is_string($response['message'])) {
                return $response['message'];
            }

            $toExclude = ['status', 'success', 'bvn', 'response', 'message', 'comment', 'reference', 'phone_number', 'field_code'];
            $toKeep = array_diff_key($data, array_flip($toExclude));

            if (empty($toKeep)) {
                return (isset($response['success']) && $response['success']) ? 'Successful' : (isset($data['status']) ? ucfirst($data['status']) : 'Processed');
            }

            $parts = [];
            foreach ($toKeep as $key => $value) {
                if (!is_scalar($value) || strlen((string)$value) > 255) continue;
                $label = ucfirst(str_replace(['_', '-'], ' ', $key));
                $parts[] = $label . ': ' . (is_bool($value) ? ($value ? 'Yes' : 'No') : $value);
            }

            return !empty($parts) ? implode(', ', $parts) : 'Processed';
        }

        return (string) $response;
    }

    private function normalizeStatus($status): string
    {
        $s = strtolower(trim((string) $status));
        
        return match ($s) {
            'successful', 'success', 'resolved', 'approved', 'completed' => 'successful',
            'processing', 'in_progress', 'in-progress', 'submitted', 'new', 'pending' => 'processing',
            'failed', 'rejected', 'error', 'declined', 'invalid', 'no record' => 'failed',
            'query', 'queried' => 'query',
            default => 'pending',
        };
    }

    /**
     * Get distinct banks from agent_services table
     */
    private function getDistinctBanks()
    {
        return AgentService::whereNotNull('bank')
            ->where('bank', '!=', '')
            ->distinct()
            ->pluck('bank')
            ->sort()
            ->values();
    }
}

