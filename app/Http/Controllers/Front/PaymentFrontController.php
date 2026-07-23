<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MiscellaneousTrait;
use App\Http\Controllers\Traits\PaymentStatusUpdaterTrait;
use App\Models\Booking;
use App\Models\GeneralSetting;
use App\Strategies\KeepzSplitStrategy;
use App\Strategies\PayduniyaStrategy;
use App\Strategies\PaypalStrategy;
use App\Strategies\RazorpayStrategy;
use App\Strategies\StripeStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentFrontController extends Controller
{
    use MiscellaneousTrait, PaymentStatusUpdaterTrait;

    public function handlePayment(Request $request)
    {
        $bookingId = $request->input('booking');
        $method = $request->input('method');
        $bookingData = Booking::find($bookingId);

        if (! $bookingData) {
            return redirect('/invalid-order')->with('error', 'Invalid booking ID');
        }
        if ($bookingData->payment_status === 'paid') {
            return redirect('/invalid-order')->with('error', 'Invalid booking ID');
        }

        $strategy = $this->getPaymentStrategy($method);

        if (! $strategy) {
            return redirect('/invalid-order')->with('error', 'Invalid booking ID');
        }

        return $strategy->process($bookingId, $bookingData, $request);
    }

    public function handleReturn(Request $request)
    {
        $bookingId = $request->input('booking');
        $method = $request->input('method');

        $strategy = $this->getPaymentStrategy($method);

        if (! $strategy) {
            return redirect('/invalid-order')->with('error', 'Invalid booking ID');
        }

        $returnURL = $strategy->return($bookingId, $request);

        return redirect($returnURL);
    }

    public function handleCallback(Request $request)
    {
        $bookingId = $request->input('booking');
        $method = $request->input('method');
        $strategy = $this->getPaymentStrategy($method);

        if (! $strategy) {
            return response()->json(['error' => 'Invalid payment method'], 400);
        }

        $strategy->callback($bookingId, $request->all());

        return response()->json(['message' => 'Callback processed'], 200);
    }

    public function handleCancel(Request $request)
    {
        $bookingId = $request->input('booking');
        $method = $request->input('method');
        $strategy = $this->getPaymentStrategy($method);
        if (! $strategy) {
            return redirect('/invalid-order')->with('error', 'Invalid booking ID');
        }
        $returnURL = $strategy->cancel($bookingId, $request->all());

        return redirect($returnURL);
    }

    protected function getPaymentStrategy($method)
    {
        return match ($method) {
            'paypal' => new PaypalStrategy,
            'stripe' => new StripeStrategy,
            'payduniya' => new PayduniyaStrategy,
            'razorpay' => new RazorpayStrategy,
            'keepz' => new KeepzSplitStrategy,
            default => null,
        };
    }

    public function showPaymentPage(Request $request)
    {
        $bookingId = $request->booking;

        $keys = [
            'stripe_status',
            'paypal_status',
            'paydunya_status',
            'razorpay_status',
            'keepz_status',
        ];

        $settings = GeneralSetting::whereIn('meta_key', $keys)->get()->keyBy('meta_key');
        $stripe_status = $settings->get('stripe_status') ?? null;
        $paypal_status = $settings->get('paypal_status') ?? null;
        $keepz_status = $settings->get('keepz_status') ?? null;

        $stripeMode = $this->getGeneralSettingValue('stripe_options');
        $stripePublicKey = $stripeMode === 'test'
            ? $this->getGeneralSettingValue('test_stripe_public_key')
            : $this->getGeneralSettingValue('live_stripe_public_key');

        $status_stripe = ($stripe_status?->meta_value ?? 'Inactive') === 'Active';
        $status_paypal = ($paypal_status?->meta_value ?? 'Inactive') === 'Active';

        $paymentMethods = [
            'stripe' => [
                'active' => $status_stripe,
                'route' => '#',
                'image' => '/front/paymentLogo/Stripe.png',
                'id' => 'stripe-link',
                'public_key' => $stripePublicKey,
                'form' => false,
            ],
            'paypal' => [
                'active' => $status_paypal,
                'route' => route('payment', ['booking' => $bookingId, 'method' => 'paypal']),
                'image' => '/front/paymentLogo/Paypal.png',
                'id' => 'paypal-form',
                'form' => true,
            ],
            'keepz' => [
                'active' => ($keepz_status?->meta_value ?? 'Inactive') === 'Active',
                'route' => route('payment', ['booking' => $bookingId, 'method' => 'keepz']),
                'image' => '/front/paymentLogo/Keepz.svg',
                'id' => 'keepz-form',
                'form' => true,
            ],
        ];

        return view('Front.payment', compact('bookingId', 'paymentMethods'));
    }

    public function paymentSuccess(Request $request)
    {
        $bookingId = $request->bookingId;

        return view('Front.Success', compact('bookingId'));
    }

    public function paymentFail(Request $request)
    {
        $bookingId = $request->bookingId;

        return view('Front.Fail', compact('bookingId'));
    }

    public function handlePaypalIPN(Request $request)
    {
        $ipnData = $request->all();
        $verify = $this->verifyPaypalIPN($ipnData);

        if ($verify) {
            $this->processPaypalPayment($ipnData);
        } else {
            Log::error('PayPal IPN verification failed.');
        }
    }

    private function verifyPaypalIPN($ipnData)
    {
        $verifyURL = 'https://www.paypal.com/cgi-bin/webscr';
        $paypalMode = $this->getGeneralSettingValue('paypal_options');
        if ($paypalMode === 'test') {
            $verifyURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        $ipnData['cmd'] = '_notify-validate';
        $response = Http::asForm()->post($verifyURL, $ipnData);

        return $response->body() === 'VERIFIED';
    }

    private function processPaypalPayment($ipnData)
    {
        $transactionType = $ipnData['txn_type'];
        $paymentStatus = $ipnData['payment_status'];
        $txnId = $ipnData['txn_id'];
        $bookingId = $ipnData['custom'];

        if ($transactionType === 'web_accept') {
            if ($paymentStatus === 'Completed') {
                $booking = Booking::findOrFail($bookingId);
                $transactionData = new \stdClass;
                $transactionData->response_data = json_encode($ipnData);
                $transactionData->gateway_name = 'paypal';
                $transactionData->payment_status = 'completed';
                $transactionData->transaction_id = $txnId;

                if ($booking->payment_status !== 'completed') {
                    $this->updateBookingStatus($bookingId, $transactionData);
                }
            } elseif ($paymentStatus === 'Pending') {
                $booking = Booking::findOrFail($bookingId);

                if ($booking->payment_status !== 'pending') {
                    $transactionData = new \stdClass;
                    $transactionData->response_data = json_encode($ipnData);
                    $transactionData->gateway_name = 'paypal';
                    $transactionData->payment_status = 'pending';
                    $transactionData->transaction_id = $txnId;
                    $this->updateBookingStatus($bookingId, $transactionData);
                }
            }
        }
    }
}
