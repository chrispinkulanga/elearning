<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPesaController extends Controller
{
    private $baseUrl = 'https://api.clickpesa.com/third-parties';
    private $clientId;
    private $apiKey;
    private $secretKey;

    public function __construct()
    {
        // Original credentials - no separate secret key needed
        $this->clientId = env('CLICKPESA_CLIENT_ID', 'ID00rAuLHbQXnBnQTNYsCCOdWiaOrZpf');
        $this->apiKey = env('CLICKPESA_API_KEY', 'SK8CdsVmhK1NbuqU5lo2P4nbCj5hVboFgthpQVjHlw');
        $this->secretKey = $this->apiKey; // Use API key as secret key like working system
    }

    /**
     * Generate ClickPesa authorization token
     */
    public function generateToken()
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification for development
            ])->withHeaders([
                'api-key' => $this->apiKey,
                'client-id' => $this->clientId,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/generate-token');

            Log::info('ClickPesa Live Token Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'client_id' => $this->clientId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['token'] ?? null;
                
                if ($token) {
                    // Remove 'Bearer ' prefix if present
                    $cleanToken = str_replace('Bearer ', '', $token);
                    
                    return response()->json([
                        'success' => true,
                        'token' => $cleanToken,
                        'expires_in' => 3600, // 1 hour as per ClickPesa docs
                        'message' => 'Live ClickPesa token generated successfully'
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate live ClickPesa token',
                'error' => $response->body(),
                'status_code' => $response->status()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('ClickPesa Token Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview USSD push request
     */
    public function previewUssdPush(Request $request)
    {
        $request->validate([
            'amount' => 'required|string',
            'currency' => 'required|string',
            'orderReference' => 'required|string',
            'phoneNumber' => 'required|string',
            'fetchSenderDetails' => 'boolean'
        ]);

        try {
            // Generate token first
            $tokenResponse = $this->generateToken();
            $tokenData = $tokenResponse->getData(true);
            
            if (!$tokenData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate token'
                ], 500);
            }

            $token = $tokenData['token'];

            // Format phone number for ClickPesa
            $phoneNumber = $request->phoneNumber;
            if (!str_starts_with($phoneNumber, '255')) {
                $phoneNumber = '255' . ltrim($phoneNumber, '0');
            }

            // Prepare payload with formatted phone number
            $payload = [
                'amount' => $request->amount,
                'currency' => $request->currency,
                'orderReference' => $request->orderReference,
                'phoneNumber' => $phoneNumber, // Use formatted phone number
                'fetchSenderDetails' => $request->fetchSenderDetails ?? false
            ];

            // Generate checksum
            $payload['checksum'] = $this->generateChecksum($payload);

            Log::info('ClickPesa Preview Request', [
                'payload' => $payload,
                'checksum' => $payload['checksum']
            ]);

            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification for development
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payments/preview-ussd-push-request', $payload);

            Log::info('ClickPesa Preview Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Preview failed',
                'error' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('ClickPesa Preview Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate USSD push request
     */
    public function initiateUssdPush(Request $request)
    {
        $request->validate([
            'amount' => 'required|string',
            'currency' => 'required|string',
            'orderReference' => 'required|string',
            'phoneNumber' => 'required|string',
            'fetchSenderDetails' => 'boolean'
        ]);

        try {
            // Generate token first
            $tokenResponse = $this->generateToken();
            $tokenData = $tokenResponse->getData(true);
            
            if (!$tokenData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate token'
                ], 500);
            }

            $token = $tokenData['token'];

            // Format phone number for ClickPesa
            $phoneNumber = $request->phoneNumber;
            if (!str_starts_with($phoneNumber, '255')) {
                $phoneNumber = '255' . ltrim($phoneNumber, '0');
            }

            // Prepare payload with formatted phone number
            $payload = [
                'amount' => $request->amount,
                'currency' => $request->currency,
                'orderReference' => $request->orderReference,
                'phoneNumber' => $phoneNumber, // Use formatted phone number
                'fetchSenderDetails' => $request->fetchSenderDetails ?? false
            ];

            // Generate checksum
            $payload['checksum'] = $this->generateChecksum($payload);

            Log::info('ClickPesa Live Initiate Request', [
                'payload' => $payload,
                'checksum' => $payload['checksum'],
                'endpoint' => $this->baseUrl . '/payments/initiate-ussd-push-request'
            ]);

            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification for development
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payments/initiate-ussd-push-request', $payload);

            Log::info('ClickPesa Live Initiate Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Live USSD push request initiated successfully',
                    'data' => $responseData,
                    'transaction_id' => $responseData['id'] ?? null,
                    'status' => $responseData['status'] ?? null,
                    'channel' => $responseData['channel'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Live USSD push initiation failed',
                'error' => $response->body(),
                'status_code' => $response->status()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('ClickPesa Initiate Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Initiation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate HMAC-SHA256 checksum
     */
    private function generateChecksum($payload)
    {
        // Create data structure like your working system (phone number already formatted)
        $data = [
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'reference' => $payload['orderReference'], // Map orderReference to reference
            'phone_number' => $payload['phoneNumber']   // Use already formatted phone number
        ];
        
        // Use exact same algorithm as your working system
        $concatenatedString = $data['amount'] . $data['currency'] . $data['reference'] . $data['phone_number'];
        
        Log::info('ClickPesa Checksum Debug', [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'reference' => $data['reference'],
            'phone_number' => $data['phone_number'],
            'concatenated_string' => $concatenatedString,
            'api_key' => substr($this->apiKey, 0, 10) . '...',
            'api_key_length' => strlen($this->apiKey)
        ]);

        // Generate HMAC-SHA256 hash using API key (like your working system)
        $checksum = hash_hmac('sha256', $concatenatedString, $this->apiKey);

        Log::info('ClickPesa Checksum Generated', [
            'checksum' => $checksum,
            'concatenated_length' => strlen($concatenatedString)
        ]);

        return $checksum;
    }

    /**
     * Get payment status for a transaction
     */
    public function getPaymentStatus($transactionId)
    {
        try {
            // Generate token first
            $tokenResponse = $this->generateToken();
            $tokenData = $tokenResponse->getData(true);
            
            if (!$tokenData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate token for status check'
                ], 500);
            }

            $token = $tokenData['token'];

            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification for development
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/payments/' . $transactionId);

            Log::info('ClickPesa Live Status Check Response', [
                'transaction_id' => $transactionId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Payment status retrieved successfully',
                    'data' => $responseData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status',
                'error' => $response->body(),
                'status_code' => $response->status()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('ClickPesa Status Check Error', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
