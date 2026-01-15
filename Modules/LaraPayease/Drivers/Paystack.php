<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class Paystack extends BasePaymentDriver
{
    use Currency;

    public function chargeCustomer(array $params)
    {
        if (empty($this->getKeys()['paystack_public_key']) || empty($this->getKeys()['paystack_secret_key'])) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing Paystack public key or secret key')];
        }

        return view('larapayease::paystack', ['paystack_data' => array_merge($params, [
            'paystack_public_key' => $this->getKeys()['paystack_public_key'],
            'currency' => $this->getCurrency(),
            'paystack_secret_key' => base64_encode($this->getKeys()['paystack_secret_key']),
            'charge_amount' => $this->chargeableAmount($params['amount']),
        ])]);
    }

    public function driverName(): string
    {
        return 'paystack';
    }

    public function paymentResponse(array $params = [])
    {
        $reference = $params['reference'] ?? null;
        $orderId = $params['order_id'] ?? null;

        if (empty($reference)) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing Reference')];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getKeys()['paystack_secret_key'],
            'Content-Type' => 'application/json',
        ])->get('https://api.paystack.co/transaction/verify/' . $reference);

        $responseData = $response->json();

        if ($response->successful() && isset($responseData['data']['status']) && $responseData['data']['status'] === 'success') {
            $transaction_id = $responseData['data']['reference'];
            
            return [
                'status' => Response::HTTP_OK,
                'data' => [
                    'transaction_id' => $transaction_id,
                    'order_id' => $orderId
                ]
            ];
        }

        return ['status' => Response::HTTP_BAD_REQUEST, 'order_id' => $orderId];
    }

    public function prepareCharge(array $params)
    {
        $secretKey = base64_decode($params['paystack_secret_key']);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => $params['email'],
            'amount' => $params['charge_amount'],
            'currency' => $params['currency'],
            'reference' => $params['order_id'] . '-' . uniqid(),
            'callback_url' => $params['ipn_url'],
            'metadata' => [
                'order_id' => $params['order_id'],
                'title' => $params['title'],
                'description' => $params['description'],
            ],
        ]);

        $responseData = $response->json();

        if ($response->successful() && $responseData['status']) {
            return [
                'id' => $responseData['data']['reference'],
                'authorization_url' => $responseData['data']['authorization_url'],
            ];
        }

        return [
            'error' => true,
            'message' => $responseData['message'] ?? 'Failed to initialize Paystack transaction'
        ];
    }
}
