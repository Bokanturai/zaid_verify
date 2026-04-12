<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PaymentWebhookController extends Controller
{

    public function handleWebhook(Request $request)
    {
        // Verify the signature
        if (!$this->verifySignature($request)) {
            Log::warning('Monnify webhook signature mismatch.', [
                'received' => $request->header('Monnify-Signature'),
                'payload' => $request->getContent()
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process the webhook payload
        $payload = $request->all();
        Log::info('Monnify webhook received:', $payload);

        $eventType = $payload['eventType'] ?? null;

        if ($eventType === 'SUCCESSFUL_TRANSACTION') {
            try {
                DB::transaction(function () use ($payload) {
                    $this->handleSuccessfulTransaction($payload);
                });
            } catch (\Exception $e) {
                Log::error('Error processing Monnify webhook: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        } else {
            Log::info('Unhandled event type: ' . $eventType);
        }

        return response()->json(['status' => 'success']);
    }

    private function verifySignature(Request $request)
    {
        $signature = $request->header('Monnify-Signature');
        // Correct algorithm: SHA512(clientSecret + requestBody)
        $computedSignature = hash('sha512', config('services.monnify.secret') . $request->getContent());

        return hash_equals((string)$signature, $computedSignature);
    }

    private function handleSuccessfulTransaction($payload)
    {
        $eventData = $payload['eventData'];

        if ($eventData['product']['type'] === 'RESERVED_ACCOUNT') {
            $this->processTransaction($eventData);
        }
    }

    private function processTransaction($eventData)
    {
        $transactionReference = $eventData['transactionReference'];
        $amountPaid = $eventData['amountPaid'];
        $email = $eventData['customer']['email'];

        // Use lockForUpdate to prevent race conditions if multiple webhooks arrive for the same ref
        $transaction = Transaction::where('transaction_ref', $transactionReference)
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            $this->createNewTransaction($email, $transactionReference, $amountPaid, $eventData);
        } else {
            Log::info('Transaction already processed.', ['ref' => $transactionReference]);
        }
    }

    private function createNewTransaction($email, $transactionReference, $amountPaid, $eventData)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->insertTransaction($user->id, $transactionReference, $amountPaid, $user->name, $email, $user->phone_number);
            $this->updateWalletBalance($user->id, $amountPaid);
        } else {
            Log::error('User not found for Monnify transaction', ['email' => $email]);
        }
    }

    private function insertTransaction($userId, $transactionReference, $amountPaid, $payerName, $payerEmail, $payerPhone)
    {
        $fee = $this->calculateFee($amountPaid);
        $netAmount = round($amountPaid - $fee, 2);

        Transaction::create([
            'user_id' => $userId,
            'payer_name' => $payerName,
            'transaction_ref' => $transactionReference,
            'reference_id' => $transactionReference, // Both mapped for safety
            'service_type' => 'Wallet Topup',
            'description' => 'Your wallet has been credited with ₦' . number_format($amountPaid, 2),
            'amount' => $amountPaid,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'type' => 'credit',
            'status' => 'completed',
            'metadata' => [
                'payer_email' => $payerEmail,
                'payer_phone' => $payerPhone,
                'gateway' => 'Monnify'
            ],
        ]);
    }

    public function calculateFee($amountPaid)
    {

        return  $fee = round($amountPaid * 0.019, 2);
    }
    private function updateWalletBalance($userId, $amountPaid)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if ($wallet) {
            $fee = $this->calculateFee($amountPaid);
            $netAmount = round($amountPaid - $fee, 2);

            // Atomic increment to prevent race conditions
            $wallet->increment('balance', $netAmount);
            $wallet->increment('available_balance', $netAmount);

            Log::info("Wallet updated atomically for user $userId. Net Amount: $netAmount");
        } else {
            Log::warning('Wallet not found for user ID: ' . $userId);
        }
    }
}
