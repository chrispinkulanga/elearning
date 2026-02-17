<?php
// app/Services/PaymentService.php
namespace App\Services;

use App\Models\Payment;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Course $course, User $user, array $options = [])
    {
        $amount = $course->getCurrentPriceAttribute() * 100; // Convert to cents

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => $options['currency'] ?? 'usd',
            'metadata' => [
                'course_id' => $course->id,
                'user_id' => $user->id,
                'course_title' => $course->title,
                'user_email' => $user->email,
            ],
            'description' => "Enrollment in course: {$course->title}",
        ]);

        // Create pending payment record
        $payment = Payment::create([
            'transaction_id' => Str::uuid(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->getCurrentPriceAttribute(),
            'currency' => strtoupper($options['currency'] ?? 'usd'),
            'payment_method' => 'stripe',
            'status' => 'pending',
            'gateway_transaction_id' => $paymentIntent->id,
            'gateway_response' => $paymentIntent->toArray(),
        ]);

        return [
            'payment' => $payment,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $course->getCurrentPriceAttribute(),
        ];
    }

    public function confirmPayment(string $paymentIntentId)
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $payment = Payment::where('gateway_transaction_id', $paymentIntentId)->first();

        if (!$payment) {
            throw new \Exception('Payment record not found');
        }

        if ($paymentIntent->status === 'succeeded') {
            $payment->update([
                'status' => 'completed',
                'gateway_response' => $paymentIntent->toArray(),
                'completed_at' => now(),
            ]);

            // Create enrollment
            $enrollment = $this->createEnrollment($payment);

            return [
                'payment' => $payment,
                'enrollment' => $enrollment,
                'success' => true
            ];
        }

        $payment->update([
            'status' => 'failed',
            'gateway_response' => $paymentIntent->toArray(),
        ]);

        return [
            'payment' => $payment,
            'success' => false,
            'error' => 'Payment was not successful'
        ];
    }

    public function processRefund(Payment $payment, string $reason = '')
    {
        if ($payment->status !== 'completed') {
            throw new \Exception('Payment is not eligible for refund');
        }

        try {
            // Process refund with Stripe
            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->gateway_transaction_id,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'refund_reason' => $reason,
                    'refunded_by' => auth()->id(),
                ]
            ]);

            $payment->update([
                'status' => 'refunded',
                'refund_reason' => $reason,
                'refunded_at' => now(),
                'refund_gateway_response' => $refund->toArray(),
            ]);

            // Cancel enrollment
            $enrollment = Enrollment::where('user_id', $payment->user_id)
                ->where('course_id', $payment->course_id)
                ->first();

            if ($enrollment) {
                $enrollment->update(['status' => 'cancelled']);
            }

            return $payment;

        } catch (\Exception $e) {
            throw new \Exception('Refund processing failed: ' . $e->getMessage());
        }
    }

    private function createEnrollment(Payment $payment)
    {
        $course = Course::find($payment->course_id);
        
        $enrollmentData = [
            'user_id' => $payment->user_id,
            'course_id' => $payment->course_id,
            'amount_paid' => $payment->amount,
            'enrolled_at' => now(),
            'status' => 'active',
        ];

        // Set expiry date if limited access
        if ($course->access_type === 'limited' && $course->access_days) {
            $enrollmentData['expires_at'] = now()->addDays($course->access_days);
        }

        return Enrollment::create($enrollmentData);
    }

    public function calculatePlatformFee(float $amount, float $feePercentage = 10)
    {
        return round($amount * ($feePercentage / 100), 2);
    }

    public function getInstructorEarnings(User $instructor, \Carbon\Carbon $startDate = null, \Carbon\Carbon $endDate = null)
    {
        $query = Payment::whereIn('course_id', $instructor->instructedCourses()->pluck('id'))
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $totalRevenue = $query->sum('amount');
        $platformFee = $this->calculatePlatformFee($totalRevenue);
        $instructorEarnings = $totalRevenue - $platformFee;

        return [
            'total_revenue' => $totalRevenue,
            'platform_fee' => $platformFee,
            'instructor_earnings' => $instructorEarnings,
            'transaction_count' => $query->count(),
        ];
    }
}
