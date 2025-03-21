<?php

namespace App\Http\Controllers;

use App\Services\AirtelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $airtelService;

    public function __construct(AirtelService $airtelService)
    {
        $this->airtelService = $airtelService;
    }

    public function initiatePayment(Request $request)
    {
        Log::debug($request->all());
        try {
            $response = $this->airtelService->ussdPushPayment(
                'Order-123',
                $request->msisdn,
                $request->amount
            );

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $requestBody = $request->all();

        if (isset($requestBody['hash'])) {
            // Authenticated Callback
            $receivedHash = $requestBody['hash'];
            unset($requestBody['hash']);

            if ($this->airtelService->verifyCallback($requestBody, $receivedHash)) {
                // Callback is valid
                Log::info('Airtel Callback Valid: ' . json_encode($requestBody));
                // Process the callback data here (e.g., update transaction status)

                return response('OK', 200); // Respond with 200 OK
            } else {
                // Callback is invalid
                Log::error('Airtel Callback Invalid: ' . json_encode($requestBody));
                return response('Invalid Signature', 400); // Respond with 400 Bad Request
            }
        } else {
            // Unauthenticated Callback
            Log::info('Airtel Callback Received (Unauthenticated): ' . json_encode($requestBody));
            // Process the callback data here
            return response('OK', 200); // Respond with 200 OK
        }
    }

    public function refundPayment(Request $request)
    {
        try {
            $response = $this->airtelService->refund($request->airtel_money_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function transactionEnquiry(Request $request)
    {
        try {
            $response = $this->airtelService->transactionEnquiry($request->transaction_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}