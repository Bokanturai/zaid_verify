<?php

namespace App\Repositories;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VirtualAccountRepository
{
    public function createVirtualAccount($loginUserId)
    {
        $userDetails = User::where('id', $loginUserId)->first();

        if (!$userDetails) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Monnify requires BVN and Phone
        if (empty($userDetails->first_name) || empty($userDetails->last_name) || empty($userDetails->phone_no) || empty($userDetails->email) || empty($userDetails->bvn)) {
            return ['success' => false, 'message' => 'Please complete your profile details (First Name, Last Name, Phone, Email, BVN) to create a virtual account.'];
        }

        try {
            $accessToken = $this->getMonnifyAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to authenticate with payment provider.'];
            }

            $baseUrl = rtrim(env('MONNIFY_BASE_URL'), '/');
            $url = $baseUrl . '/v2/bank-transfer/reserved-accounts';

            $accountReference = 'QS-' . $loginUserId . '-' . uniqid();

            $data = [
                'accountReference' => $accountReference,
                'accountName' => $userDetails->first_name . ' ' . $userDetails->last_name,
                'currencyCode' => 'NGN',
                'contractCode' => env('MONNIFY_CONTRACT'),
                'customerEmail' => $userDetails->email,
                'customerName' => $userDetails->first_name . ' ' . $userDetails->last_name,
                'bvn' => $userDetails->bvn,
                'getAllAvailableBanks' => true,
            ];

            if (!empty($userDetails->nin)) {
                $data['nin'] = $userDetails->nin;
            }

            Log::info('Monnify Reserved Account Request: ', $data);

            $response = Http::withToken($accessToken)
                ->post($url, $data);

            Log::info('Monnify Reserved Account Response: ' . $response->body());

            $responseData = $response->json();

            if ($response->successful() && ($responseData['requestSuccessful'] ?? false) === true) {
                $accounts = $responseData['responseBody']['accounts'] ?? [];
                $reservationReference = $responseData['responseBody']['reservationReference'] ?? null;

                if (empty($accounts)) {
                    throw new Exception("No account details returned from provider.");
                }

                // Delete existing virtual accounts for this user to avoid confusion if re-creating
                DB::table('virtual_accounts')->where('user_id', $loginUserId)->delete();

                foreach ($accounts as $account) {
                    DB::table('virtual_accounts')->insert([
                        'user_id' => $loginUserId,
                        'accountReference' => $accountReference,
                        'reservation_reference' => $reservationReference,
                        'accountNo' => $account['accountNumber'],
                        'accountName' => $account['accountName'],
                        'bankName' => $account['bankName'],
                        'bankCode' => $account['bankCode'],
                        'status' => '1',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return ['success' => true, 'message' => 'Virtual Account(s) Created Successfully'];
            } else {
                $errorMessage = $responseData['responseMessage'] ?? 'Failed to create virtual account';
                throw new Exception($errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Error creating Monnify virtual account for user ' . $loginUserId . ': ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get Monnify Access Token
     */
    private function getMonnifyAccessToken()
    {
        try {
            $apiKey = env('MONNIFY_API_KEY');
            $secretKey = env('MONNIFY_SECRET');
            $baseUrl = rtrim(env('MONNIFY_BASE_URL'), '/');

            $url = $baseUrl . '/v1/auth/login';

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $secretKey),
            ])->post($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['responseBody']['accessToken'] ?? null;
            }

            Log::error('Monnify Auth Error details:', [
                'status' => $response->status(),
                'body' => $response->body(),
                'apiKey' => env('MONNIFY_API_KEY'),
                'url' => $url
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Monnify Auth Exception: ' . $e->getMessage());
            return null;
        }
    }
}
