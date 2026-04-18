<?php

namespace App\Http\Controllers\Admin\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BvnUser;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BvnUserController extends Controller
{
    /**
     * List BVN User requests with filters and pagination
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $query = BvnUser::with('user');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bvn', 'like', "%$search%")
                  ->orWhere('phone_no', 'like', "%$search%")
                  ->orWhere('reference', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $enrollments = $query
            ->orderByRaw("CASE status
                WHEN 'pending' THEN 1
                WHEN 'processing' THEN 2
                WHEN 'query' THEN 3
                WHEN 'successful' THEN 4
                WHEN 'failed' THEN 5
                ELSE 99 END")
            ->orderByDesc('submission_date')
            ->paginate(10);

        $statusCounts = [
            'pending'    => BvnUser::where('status', 'pending')->count(),
            'processing' => BvnUser::where('status', 'processing')->count(),
            'resolved'   => BvnUser::whereIn('status', ['successful'])->count(),
            'rejected'   => BvnUser::whereIn('status', ['failed'])->count(),
        ];

        return view('admin.bvn-user.index', compact('enrollments', 'search', 'statusFilter', 'statusCounts'));
    }

    /**
     * Show details of a single BVN User request
     */
    public function show($id)
    {
        $enrollmentInfo = BvnUser::with(['user', 'serviceField', 'service', 'transaction'])->findOrFail($id);
        $user = $enrollmentInfo->user;

        $statusHistory = collect([
            [
                'status' => $enrollmentInfo->status,
                'comment' => $enrollmentInfo->comment,
                'submission_date' => $enrollmentInfo->submission_date ?? $enrollmentInfo->created_at,
                'updated_at' => $enrollmentInfo->updated_at,
            ]
        ]);

        return view('admin.bvn-user.show', compact('enrollmentInfo', 'statusHistory', 'user'));
    }

    /**
     * Update the status of a BVN User request
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,successful,failed,query,remark',
            'comment' => 'nullable|string',
            'force_refund' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $enrollment = BvnUser::findOrFail($id);
            $oldStatus = $enrollment->status;

            // Update enrollment
            $enrollment->status = $request->status;
            $enrollment->comment = $request->comment;
            $enrollment->save();

            // Handle refund logic if failed/rejected
            if ($request->status === 'failed') {
                if ($oldStatus !== 'failed' || $request->force_refund) {
                    $this->processRefund($enrollment, $request->force_refund);
                }
            }

            DB::commit();
            return redirect()->route('admin.bvn-user.index')
                ->with('successMessage', 'Status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.bvn-user.index')
                ->with('errorMessage', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Handle refund when a request is failed
     */
    private function processRefund($enrollment, $forceRefund = false)
    {
        $user = User::find($enrollment->user_id);

        if (!$user) {
            throw new \Exception('User not found.');
        }

        // Check if refund already exists
        $refundExists = Transaction::where('type', 'refund')
            ->where('description', 'LIKE', "%Request #{$enrollment->id}%")
            ->exists();

        if ($refundExists && !$forceRefund) {
            throw new \Exception('Refund already processed for this request.');
        }

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
            'description' => "Refund 80% for failed BVN User request [#{$enrollment->id}]",
            'type' => 'refund',
            'status' => 'completed',
            'metadata' => json_encode([
                'bvn_user_id' => $enrollment->id,
                'total_paid' => $paidAmount,
                'percentage_refunded' => 80,
                'amount_debited_by_system' => $debitAmount,
                'forced_refund' => $forceRefund,
            ]),
        ]);
    }
}
