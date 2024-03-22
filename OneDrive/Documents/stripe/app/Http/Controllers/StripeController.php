<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeController extends Controller
{

    public function create()
    {
        $stripe = new StripeClient('sk_test_51OwZmCRutpyIFE5KjOrHlss8Bwx6ukF0c53cVowOzNFp0hixgi7ZrtNRo6cjcfjPFMJuXnFviEq6Vtqhhw9aY4ov00apSzsfUi');

        // Use an existing Customer ID if this is a returning customer.
        $customer = $stripe->customers->create();

        $ephemeralKey = $stripe->ephemeralKeys->create([
            'customer' => $customer->id,
        ], [
            'stripe_version' => '2023-10-16',
        ]);
        $setupIntent = $stripe->setupIntents->create([
            'customer' => $customer->id,
        ]);

        return response()->json([
            'setupIntent' => $setupIntent->client_secret,
            'setupIntentId' => $setupIntent->id,
            'ephemeralKey' => $ephemeralKey->secret,
            'customer' => $customer->id,
            'publishableKey' => 'pk_test_51OwZmCRutpyIFE5Kf1t2ZVZouevmzCZREOryYz7mFL93x1UHLRCcpyvx7eUDnnqUwZ3GXdd2qdl0V6o21x3FEdQv000RZ3tPmz'
        ]);
    }

    public function confirm(Request $request, $id)
    {
        $stripe = new StripeClient('sk_test_51OwZmCRutpyIFE5KjOrHlss8Bwx6ukF0c53cVowOzNFp0hixgi7ZrtNRo6cjcfjPFMJuXnFviEq6Vtqhhw9aY4ov00apSzsfUi');
        Stripe::setApiKey('sk_test_51OwZmCRutpyIFE5KjOrHlss8Bwx6ukF0c53cVowOzNFp0hixgi7ZrtNRo6cjcfjPFMJuXnFviEq6Vtqhhw9aY4ov00apSzsfUi');
        try {
            $paymentMethod = $stripe->paymentMethods->all([
                'customer' => $id,
                'type' => 'card',
            ]);
            $paymentMethodId = $paymentMethod['data'][0]['id'];
            PaymentIntent::create([
                'amount' => 1099,
                'currency' => 'usd',
                // In the latest version of the API, specifying the `automatic_payment_methods` parameter is optional because Stripe enables its functionality by default.
                'automatic_payment_methods' => ['enabled' => true],
                'customer' => $id,
                'payment_method' => $paymentMethodId,
                'return_url' => 'https://test.com',
                'off_session' => true,
                'confirm' => true,
            ]);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
        }

        return response()->json(['message' => $paymentMethod->data], 200);
    }
}
