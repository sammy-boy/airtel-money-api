<?php

namespace App\Services;

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirtelService
{
    protected $client;
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $publicKey;
    protected $xSignature;
    protected $xKey;
    protected $privateKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.airtel.base_url');
        $this->clientId = config('services.airtel.client_id');
        $this->clientSecret = config('services.airtel.client_secret');
        $this->publicKey = config('services.airtel.public_key');
        $this->xSignature = config('services.airtel.x_signature');
        $this->xKey = config('services.airtel.x_key');
        $this->privateKey = config('services.airtel.private_key');
    }

    protected function getAccessToken()
    {
        try {
            $response = $this->client->post($this->baseUrl . '/auth/oauth2/token', [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => '*/*'],
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $accessToken = $data['access_token'];
            
            return $accessToken;

        } catch (ClientException $e) {
            Log::error('Airtel Authentication Error: ' . $e->getResponse()->getBody());
            throw new \Exception('Airtel Authentication Failed');
        }
    }

    public function encryptData($data)
    {
        try {
            openssl_public_encrypt($data, $encrypted, $this->publicKey, OPENSSL_PKCS1_OAEP_PADDING);
            return base64_encode($encrypted);
        } catch (\Exception $e) {
            Log::error('Airtel Encryption Error: ' . $e->getMessage());
            throw new \Exception('Airtel Encryption Failed');
        }
    }

    public function ussdPushPayment($reference, $msisdn, $amount)
    {
        $accessToken = $this->getAccessToken();

        try {
            $response = $this->client->post($this->baseUrl . '/merchant/v2/payments/', [
                'headers' => [
                    'Accept' => '*/*',
                    'Content-Type' => 'application/json',
                    'X-Country' => 'UG',
                    'X-Currency' => 'UGX',
                    'Authorization' => 'Bearer ' . $accessToken,
                    'x-signature' => $this->xSignature,
                    'x-key' => $this->xKey,
                ],
                'json' => [
                    'reference' => $reference,
                    'subscriber' => [
                        'country' => 'UG',
                        'currency' => 'UGX',
                        'msisdn' => $msisdn, // e.g., 772123456 without country code
                    ],
                    'transaction' => [
                        'amount' => (int) $amount,
                        'country' => 'UG',
                        'currency' => 'UGX',
                        'id' => Uuid::uuid4()->toString(),
                    ],
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            Log::error('Airtel Payment Error: ' . $e->getResponse()->getBody());
            throw new \Exception('Airtel Payment Failed');
        }
    }

    public function refund($airtelMoneyId)
    {
        $accessToken = $this->getAccessToken();
        try{
            $response = $this->client->post($this->baseUrl . '/standard/v2/payments/refund', [
                'headers' => [
                    'Accept' => '*/*',
                    'Content-Type' => 'application/json',
                    'X-Country' => 'UG',
                    'X-Currency' => 'UGX',
                    'Authorization' => 'Bearer ' . $accessToken,
                    'x-signature' => $this->xSignature,
                    'x-key' => $this->xKey,
                ],
                'json' => [
                    'transaction' => [
                        'airtel_money_id' => $airtelMoneyId,
                    ],
                ],
            ]);
            return json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            Log::error('Airtel Refund Error: ' . $e->getResponse()->getBody());
            throw new \Exception('Airtel Refund Failed');
        }
    }

    public function transactionEnquiry($transactionId)
    {
        $accessToken = $this->getAccessToken();
        try {
            $response = $this->client->get($this->baseUrl . '/standard/v1/payments/' . $transactionId, [
                'headers' => [
                    'Accept' => '*/*',
                    'X-Country' => 'UG',
                    'X-Currency' => 'UGX',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            Log::error('Airtel Transaction Enquiry Error: ' . $e->getResponse()->getBody());
            throw new \Exception('Airtel Transaction Enquiry Failed');
        }
    }

    public function verifyCallback($requestBody, $receivedHash)
    {
        $calculatedHash = base64_encode(hash_hmac('sha256', json_encode($requestBody), $this->privateKey, true));
        return $calculatedHash === $receivedHash;
    }
}
