<?php

namespace App\Http\Controllers\Agency;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Controllers\Controller;

class NinPersonalisationController extends Controller
{
    /**
     * Display the service form and submission history for NIN Personalisation.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $serviceKey = 'NIN Personalisation';

        // Query only this user's submissions
        $submissions = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->where('service_type', 'nin_personalization')
            ->when($request->filled('search'), fn($q) =>
                $q->where('tracking_id', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn($q) =>
                $q->where('status', $request->status))
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    WHEN status = 'successful' THEN 3
                    WHEN status = 'query' THEN 4
                    ELSE 99
                END
            ")->orderByDesc('submission_date')
            ->paginate(10)
            ->withQueryString();

        // Load active service and its fields
        $service = Service::where('name', $serviceKey)
            ->where('is_active', true)
            ->with(['fields' => fn($q) => $q->where('is_active', true), 'prices'])
            ->first();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $fields = $service?->fields ?? collect();
        $prices = $service?->prices ?? collect();

        return view('nin.personalisation', [
            'fieldname'     => $fields,
            'services'      => Service::where('is_active', true)->get(),
            'serviceName'   => $serviceKey,
            'submissions'   => $submissions,
            'servicePrices' => $prices,
            'wallet'        => $wallet,
        ]);
    }

    /**
     * Store submission for NIN Personalisation.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $serviceKey = 'NIN Personalisation';

        // 1. Validation
        $rules = [
            'field_code'  => 'required|exists:service_fields,id',
            'tracking_id' => 'required|string|max:50',
        ];

        $validated = $request->validate($rules);

        // 2. Fetch Service Field and Price
        $serviceField = ServiceField::with(['service', 'prices'])->findOrFail($validated['field_code']);
        $serviceName = $serviceField->service->name;
        $fieldName = $serviceField->field_name;

        $servicePrice = $serviceField->prices
            ->where('user_type', $user->role)
            ->first()?->price ?? $serviceField->base_price;

        if ($servicePrice === null) {
            return back()->with([
                'status'  => 'error',
                'message' => 'Service price not configured for your account type.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            // 3. Lock Wallet and Check Wallet Status
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new \Exception('Your wallet is not active.');
            }

            // 4. Check Balance
            if ($wallet->balance < $servicePrice) {
                throw new \Exception('Insufficient balance. You need NGN ' . number_format($servicePrice - $wallet->balance, 2) . ' more.');
            }

            $reference = 'NINP' . date('is') . strtoupper(substr(uniqid(mt_rand(), true), -5));
            $performedBy = trim($user->first_name . ' ' . ($user->last_name ?? $user->surname));

            // 5. Create Transaction Record
            $transaction = Transaction::create([
                'transaction_ref' => $reference,
                'user_id'         => $user->id,
                'amount'          => $servicePrice,
                'performed_by'    => $performedBy,
                'description'     => "{$serviceName} Request for {$fieldName}",
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => [
                    'service_key'   => 'NIN_PERSONALISATION',
                    'field_details' => [
                        'id'   => $serviceField->id,
                        'name' => $fieldName,
                        'code' => $serviceField->field_code,
                    ],
                    'request_data'  => $validated,
                ],
            ]);

            // 6. Create AgentService Record
            AgentService::create([
                'reference'       => $reference,
                'user_id'         => $user->id,
                'service_id'      => $serviceField->service_id,
                'service_field_id' => $serviceField->id,
                'field_code'      => $serviceField->field_code,
                'service_name'    => $serviceName,
                'field_name'      => $fieldName,
                'tracking_id'     => $validated['tracking_id'],
                'amount'          => $servicePrice,
                'performed_by'    => $performedBy,
                'transaction_id'  => $transaction->id,
                'submission_date' => now(),
                'status'          => 'pending',
                'service_type'    => 'nin_personalization', // Admin expects this specific string
            ]);

            // 7. Deduct Wallet Balance
            $wallet->decrement('balance', $servicePrice);

            // Manual Service - No API Call

            DB::commit();

            return redirect()->route('nin-personalisation.index')->with([
                'status'  => 'success',
                'message' => "NIN Personalisation request submitted successfully. Ref: {$reference}. Charged: ₦" . number_format($servicePrice, 2),
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('NIN Personalisation Store Exception', [
                'user_id' => $user->id,
                'error'   => $e->getMessage()
            ]);

            return back()->with([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ])->withInput();
        }
    }
}
