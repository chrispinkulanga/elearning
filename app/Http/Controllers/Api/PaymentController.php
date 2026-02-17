<?php
// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:stripe,paypal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is already enrolled
        if (auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Already enrolled in this course'
            ], 400);
        }

        // Check if course is free
        if ($course->is_free || $course->getCurrentPriceAttribute() <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'This course is free. Use the enroll endpoint instead.'
            ], 400);
        }

        try {
            if ($request->payment_method === 'stripe') {
                return $this->createStripePaymentIntent($course);
            } elseif ($request->payment_method === 'paypal') {
                return $this->createPayPalPayment($course);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmPayment(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
            'payment_method' => 'required|in:stripe,paypal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->payment_method === 'stripe') {
                return $this->confirmStripePayment($request, $course);
            } elseif ($request->payment_method === 'paypal') {
                return $this->confirmPayPalPayment($request, $course);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment confirmation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentHistory(Request $request)
    {
        $payments = auth()->user()->payments()
            ->with('course:id,title,thumbnail')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    public function getPaymentDetails(Payment $payment)
    {
        // Check if payment belongs to current user
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $payment->load('course:id,title,thumbnail');

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    public function downloadInvoice(Payment $payment)
    {
        // Check if payment belongs to current user
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Generate and return invoice PDF
        $pdf = $this->generateInvoicePDF($payment);
        
        return $pdf->download('invoice_' . $payment->transaction_id . '.pdf');
    }

    public function requestRefund(Request $request, Payment $payment)
    {
        // Check if payment belongs to current user
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if payment is eligible for refund
        if ($payment->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment is not eligible for refund'
            ], 400);
        }

        // Check refund policy (e.g., within 30 days)
        if ($payment->created_at->diffInDays(now()) > 30) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refund period has expired'
            ], 400);
        }

        // Update payment status to refund requested
        $payment->update([
            'status' => 'refund_requested',
            'refund_reason' => $request->reason,
            'refund_requested_at' => now(),
        ]);

        // Notify admin about refund request
        // You can implement notification logic here

        return response()->json([
            'status' => 'success',
            'message' => 'Refund request submitted successfully'
        ]);
    }

    public function webhookHandler(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event['data']['object']);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailure($event['data']['object']);
                break;
            default:
                // Unexpected event type
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Verify Flutterwave transaction from client
     * Expects: { transaction_id, amount, currency, course_id }
     */
    public function verifyFlutterwave(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ]);

        try {
            $txId = $request->transaction_id;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.flutterwave.com/v3/transactions/{$txId}/verify");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('FLW_SECRET_KEY'),
            ]);
            $result = curl_exec($ch);
            if ($result === false) {
                return response()->json(['status' => 'error', 'message' => 'Verification request failed'], 500);
            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::warning('Flutterwave verify non-200', ['code' => $httpCode, 'body' => $result]);
                return response()->json(['status' => 'error', 'message' => 'Verification failed'], 400);
            }

            $payload = json_decode($result, true);
            if (($payload['status'] ?? '') !== 'success') {
                return response()->json(['status' => 'error', 'message' => 'Verification response not successful'], 400);
            }

            $data = $payload['data'] ?? [];
            // Basic checks
            $amountOk = (float) $data['amount'] == (float) $request->amount;
            $currencyOk = strtoupper($data['currency'] ?? '') === strtoupper($request->currency);
            $statusOk = ($data['status'] ?? '') === 'successful';

            if (!$amountOk || !$currencyOk || !$statusOk) {
                return response()->json(['status' => 'error', 'message' => 'Verification mismatch'], 400);
            }

            $course = Course::findOrFail($request->course_id);

            // Record payment
            $payment = Payment::create([
                'transaction_id' => $data['tx_ref'] ?? Str::uuid(),
                'user_id' => auth()->id(),
                'course_id' => $course->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'payment_method' => 'flutterwave',
                'status' => 'completed',
                'gateway_transaction_id' => (string)($data['id'] ?? ''),
                'gateway_response' => $payload,
            ]);

            // Create enrollment
            $enrollment = Enrollment::firstOrCreate([
                'user_id' => auth()->id(),
                'course_id' => $course->id,
            ], [
                'amount_paid' => $data['amount'],
                'enrolled_at' => now(),
                'status' => 'active',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified',
                'data' => [
                    'payment' => $payment,
                    'enrollment' => $enrollment,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Flutterwave verify error: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error during verification'], 500);
        }
    }

    private function createStripePaymentIntent($course)
    {
        $amount = $course->getCurrentPriceAttribute() * 100; // Convert to cents

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'metadata' => [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
            ],
        ]);

        // Create pending payment record
        $payment = Payment::create([
            'transaction_id' => Str::uuid(),
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'amount' => $course->getCurrentPriceAttribute(),
            'currency' => 'USD',
            'payment_method' => 'stripe',
            'status' => 'pending',
            'gateway_transaction_id' => $paymentIntent->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'client_secret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id,
                'amount' => $course->getCurrentPriceAttribute(),
            ]
        ]);
    }

    private function confirmStripePayment($request, $course)
    {
        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

        if ($paymentIntent->status === 'succeeded') {
            // Update payment record
            $payment = Payment::where('gateway_transaction_id', $request->payment_intent_id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'gateway_response' => $paymentIntent->toArray(),
                ]);

                // Create enrollment
                $enrollment = Enrollment::create([
                    'user_id' => auth()->id(),
                    'course_id' => $course->id,
                    'amount_paid' => $course->getCurrentPriceAttribute(),
                    'enrolled_at' => now(),
                    'status' => 'active',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment confirmed and enrollment created',
                    'data' => [
                        'payment' => $payment,
                        'enrollment' => $enrollment,
                    ]
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Payment confirmation failed'
        ], 400);
    }

    private function createPayPalPayment($course)
    {
        // PayPal integration would go here
        // This is a simplified implementation
        return response()->json([
            'status' => 'success',
            'data' => [
                'paypal_order_id' => 'PAYPAL_' . Str::random(20),
                'amount' => $course->getCurrentPriceAttribute(),
            ]
        ]);
    }

    private function confirmPayPalPayment($request, $course)
    {
        // PayPal payment confirmation would go here
        // This is a simplified implementation
        return response()->json([
            'status' => 'success',
            'message' => 'PayPal payment confirmed'
        ]);
    }

    private function generateInvoicePDF($payment)
    {
        $payment->load(['user', 'course']);
        
        $data = [
            'payment' => $payment,
            'user' => $payment->user,
            'course' => $payment->course,
            'invoice_number' => 'INV-' . $payment->id,
            'invoice_date' => $payment->created_at->format('F d, Y'),
        ];

        return \PDF::loadView('invoices.template', $data);
    }

    private function handlePaymentSuccess($paymentIntent)
    {
        $payment = Payment::where('gateway_transaction_id', $paymentIntent['id'])->first();
        
        if ($payment && $payment->status === 'pending') {
            $payment->update([
                'status' => 'completed',
                'gateway_response' => $paymentIntent,
            ]);

            // Create enrollment if not exists
            $enrollment = Enrollment::firstOrCreate([
                'user_id' => $payment->user_id,
                'course_id' => $payment->course_id,
            ], [
                'amount_paid' => $payment->amount,
                'enrolled_at' => now(),
                'status' => 'active',
            ]);
        }
    }

    private function handlePaymentFailure($paymentIntent)
    {
        $payment = Payment::where('gateway_transaction_id', $paymentIntent['id'])->first();
        
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => $paymentIntent,
            ]);
        }
    }
}