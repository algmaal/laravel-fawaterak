<?php

/**
 * مثال شامل لاستخدام حزمة Laravel Fawaterak
 * 
 * هذا المثال يوضح كيفية استخدام الحزمة في تطبيق Laravel حقيقي
 */

namespace App\Http\Controllers;

use Algmaal\LaravelFawaterak\Facades\Fawaterak;
use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Exceptions\FawaterakException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected FawaterakServiceInterface $fawaterakService;

    public function __construct(FawaterakServiceInterface $fawaterakService)
    {
        $this->fawaterakService = $fawaterakService;
    }

    /**
     * عرض صفحة اختيار طريقة الدفع
     */
    public function showPaymentMethods(): View
    {
        try {
            $paymentMethods = $this->fawaterakService->getPaymentMethods();
            
            return view('payment.methods', [
                'methods' => $paymentMethods['data'] ?? []
            ]);
        } catch (FawaterakException $e) {
            return view('payment.error', [
                'message' => 'حدث خطأ في تحميل طرق الدفع: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة طلب الدفع
     */
    public function processPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method_id' => 'required|integer',
            'customer.first_name' => 'required|string|max:255',
            'customer.last_name' => 'required|string|max:255',
            'customer.email' => 'required|email',
            'customer.phone' => 'required|string',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.name' => 'required|string',
            'cart_items.*.price' => 'required|numeric|min:0',
            'cart_items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            // حساب المجموع
            $total = collect($request->cart_items)->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            // إنشاء الدفعة
            $payment = Fawaterak::createPayment(
                $request->customer,
                $request->cart_items,
                $total,
                $request->payment_method_id,
                [
                    'currency' => 'EGP',
                    'invoice_number' => 'INV-' . time(),
                    'redirection_urls' => [
                        'successUrl' => route('payment.success'),
                        'failUrl' => route('payment.failed'),
                        'pendingUrl' => route('payment.pending')
                    ]
                ]
            );

            // حفظ معرف الفاتورة في الجلسة
            session(['invoice_key' => $payment['data']['invoice_key']]);

            // التعامل مع أنواع الدفع المختلفة
            $paymentData = $payment['data']['payment_data'];

            if (isset($paymentData['redirectTo'])) {
                // Visa/Mastercard - إعادة توجيه
                return redirect($paymentData['redirectTo']);
            } elseif (isset($paymentData['fawryCode'])) {
                // Fawry - عرض الكود
                return redirect()->route('payment.fawry', [
                    'code' => $paymentData['fawryCode'],
                    'expire_date' => $paymentData['expireDate']
                ]);
            } elseif (isset($paymentData['meezaQrCode'])) {
                // Meeza - عرض QR Code
                return redirect()->route('payment.meeza', [
                    'qr_code' => $paymentData['meezaQrCode'],
                    'reference' => $paymentData['meezaReference']
                ]);
            } elseif (isset($paymentData['amanCode'])) {
                // Aman - عرض الكود
                return redirect()->route('payment.aman', [
                    'code' => $paymentData['amanCode']
                ]);
            } elseif (isset($paymentData['masaryCode'])) {
                // Basta - عرض الكود
                return redirect()->route('payment.basta', [
                    'code' => $paymentData['masaryCode']
                ]);
            }

            return redirect()->route('payment.error')
                ->with('error', 'نوع الدفع غير مدعوم');

        } catch (FawaterakException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ في معالجة الدفع: ' . $e->getMessage());
        }
    }

    /**
     * صفحة نجاح الدفع
     */
    public function paymentSuccess(): View
    {
        $invoiceKey = session('invoice_key');
        
        if (!$invoiceKey) {
            return redirect()->route('home');
        }

        try {
            $isSuccessful = Fawaterak::isPaymentSuccessful($invoiceKey);
            $paymentDetails = Fawaterak::getPayment($invoiceKey);

            return view('payment.success', [
                'is_successful' => $isSuccessful,
                'payment' => $paymentDetails['data'] ?? []
            ]);
        } catch (FawaterakException $e) {
            return view('payment.error', [
                'message' => 'حدث خطأ في التحقق من الدفع: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * صفحة فشل الدفع
     */
    public function paymentFailed(): View
    {
        $invoiceKey = session('invoice_key');
        
        return view('payment.failed', [
            'invoice_key' => $invoiceKey
        ]);
    }

    /**
     * صفحة الدفع المعلق
     */
    public function paymentPending(): View
    {
        $invoiceKey = session('invoice_key');
        
        return view('payment.pending', [
            'invoice_key' => $invoiceKey
        ]);
    }

    /**
     * التحقق من حالة الدفع عبر AJAX
     */
    public function checkPaymentStatus(Request $request)
    {
        $invoiceKey = $request->input('invoice_key');
        
        if (!$invoiceKey) {
            return response()->json(['error' => 'معرف الفاتورة مطلوب'], 400);
        }

        try {
            $status = Fawaterak::getPaymentStatus($invoiceKey);
            $isSuccessful = Fawaterak::isPaymentSuccessful($invoiceKey);

            return response()->json([
                'status' => $status,
                'is_successful' => $isSuccessful,
                'message' => $this->getStatusMessage($status)
            ]);
        } catch (FawaterakException $e) {
            return response()->json([
                'error' => 'حدث خطأ في التحقق من الدفع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على رسالة الحالة
     */
    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'paid' => 'تم الدفع بنجاح',
            'pending' => 'الدفع معلق',
            'failed' => 'فشل الدفع',
            'cancelled' => 'تم إلغاء الدفع',
            'expired' => 'انتهت صلاحية الدفع',
            default => 'حالة غير معروفة'
        };
    }
}
