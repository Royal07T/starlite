<?php

namespace Modules\LaraPayease\Http\Controllers;

use Modules\LaraPayease\Facades\PaymentDriver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaystackPaymentController extends Controller
{
    public function prepareCharge(Request $request)
    {
        try {
            $paystack_session = getGatewayObject('paystack')->prepareCharge([
                'amount' => $request->amount,
                'charge_amount' => $request->charge_amount,
                'title' => $request->title,
                'description' => $request->description,
                'ipn_url' => $request->ipn_url,
                'order_id' => $request->order_id,
                'track' => $request->track,
                'cancel_url' => $request->cancel_url,
                'success_url' => $request->success_url,
                'email' => $request->email,
                'name' => $request->name,
                'payment_type' => $request->payment_type,
                'currency' => $request->currency,
            ]);
            
            if (isset($paystack_session['error'])) {
                return response()->json(['msg' => $paystack_session['message'], 'type' => 'danger']);
            }
            
            return response()->json([
                'id' => $paystack_session['id'],
                'authorization_url' => $paystack_session['authorization_url']
            ]);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'type' => 'danger']);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            $response = getGatewayObject('paystack')->paymentResponse([
                'reference' => $request->reference,
                'order_id' => $request->order_id
            ]);
            
            if ($response['status'] === 200) {
                return redirect()->route('payment.success', ['order_id' => $response['data']['order_id']]);
            }
            
            return redirect()->route('payment.cancel', ['order_id' => $response['order_id']]);
        } catch (\Exception $e) {
            return redirect()->route('payment.cancel')->with('error', $e->getMessage());
        }
    }
}
