# Laravel Fawaterak Payment Gateway

[![Latest Version on Packagist](https://img.shields.io/packagist/v/algmaal/laravel-fawaterak.svg?style=flat-square)](https://packagist.org/packages/algmaal/laravel-fawaterak)
[![Total Downloads](https://img.shields.io/packagist/dt/algmaal/laravel-fawaterak.svg?style=flat-square)](https://packagist.org/packages/algmaal/laravel-fawaterak)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/algmaal/laravel-fawaterak/run-tests?label=tests)](https://github.com/algmaal/laravel-fawaterak/actions?query=workflow%3Arun-tests+branch%3Amain)

حزمة Laravel للتكامل مع بوابة الدفع Fawaterak. تدعم جميع طرق الدفع المتاحة في Fawaterak مع إدارة شاملة للمعاملات والـ webhooks.

## المميزات

- ✅ دعم Laravel 10+ و 11+ و 12+
- ✅ تكامل كامل مع Fawaterak API
- ✅ دعم جميع طرق الدفع (Visa/Mastercard، Fawry، Meeza، Aman، Basta)
- ✅ معالجة Webhooks التلقائية
- ✅ إدارة البيئات المتعددة (Staging/Production)
- ✅ Cache للبيانات المتكررة
- ✅ Logging شامل للعمليات
- ✅ Validation للبيانات
- ✅ معالجة الأخطاء المتقدمة
- ✅ اختبارات شاملة
- ✅ Facade سهل الاستخدام

## التثبيت

يمكنك تثبيت الحزمة عبر Composer:

```bash
composer require algmaal/laravel-fawaterak
```

نشر ملف التكوين:

```bash
php artisan vendor:publish --provider="Algmaal\LaravelFawaterak\FawaterakServiceProvider" --tag="fawaterak-config"
```

## التكوين

أضف المتغيرات التالية إلى ملف `.env`:

```env
# البيئة الافتراضية (staging أو production)
FAWATERAK_ENVIRONMENT=staging

# إعدادات بيئة التطوير
FAWATERAK_STAGING_API_KEY=your_staging_api_key
FAWATERAK_STAGING_BASE_URL=https://staging.fawaterk.com
FAWATERAK_STAGING_WEBHOOK_SECRET=your_staging_webhook_secret

# إعدادات بيئة الإنتاج
FAWATERAK_PRODUCTION_API_KEY=your_production_api_key
FAWATERAK_PRODUCTION_BASE_URL=https://app.fawaterak.com
FAWATERAK_PRODUCTION_WEBHOOK_SECRET=your_production_webhook_secret

# العملة الافتراضية
FAWATERAK_DEFAULT_CURRENCY=EGP

# إعدادات إضافية
FAWATERAK_CACHE_ENABLED=true
FAWATERAK_LOGGING_ENABLED=true
```

## الاستخدام الأساسي

### الحصول على طرق الدفع المتاحة

```php
use Algmaal\LaravelFawaterak\Facades\Fawaterak;

// الحصول على طرق الدفع
$paymentMethods = app(\Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface::class)
    ->getPaymentMethods();

foreach ($paymentMethods['data'] as $method) {
    echo $method['name_ar'] . ' - ' . $method['name_en'];
}
```

### إنشاء دفعة جديدة

```php
use Algmaal\LaravelFawaterak\Facades\Fawaterak;

// بيانات العميل
$customerData = [
    'first_name' => 'محمد',
    'last_name' => 'أحمد',
    'email' => 'mohamed@example.com',
    'phone' => '01234567890',
    'address' => 'القاهرة، مصر'
];

// عناصر السلة
$cartItems = [
    [
        'name' => 'منتج تجريبي 1',
        'price' => '100',
        'quantity' => '1'
    ],
    [
        'name' => 'منتج تجريبي 2',
        'price' => '50',
        'quantity' => '2'
    ]
];

// إنشاء الدفعة
$payment = Fawaterak::createPayment(
    $customerData,
    $cartItems,
    200.0, // المجموع
    2, // معرف طريقة الدفع (Visa/Mastercard)
    [
        'currency' => 'EGP',
        'invoice_number' => 'INV-001',
        'redirection_urls' => [
            'successUrl' => 'https://yoursite.com/payment/success',
            'failUrl' => 'https://yoursite.com/payment/failed',
            'pendingUrl' => 'https://yoursite.com/payment/pending'
        ]
    ]
);

// إعادة توجيه العميل لصفحة الدفع
if (isset($payment['data']['payment_data']['redirectTo'])) {
    return redirect($payment['data']['payment_data']['redirectTo']);
}
```

### التحقق من حالة الدفعة

```php
use Algmaal\LaravelFawaterak\Facades\Fawaterak;

$invoiceKey = 'hyU2vcy3USvT5Tg';

// التحقق من نجاح الدفعة
if (Fawaterak::isPaymentSuccessful($invoiceKey)) {
    echo 'تم الدفع بنجاح!';
} else {
    echo 'الدفعة لم تكتمل بعد';
}

// الحصول على حالة الدفعة
$status = Fawaterak::getPaymentStatus($invoiceKey);
echo "حالة الدفعة: {$status}";

// الحصول على تفاصيل الدفعة كاملة
$paymentDetails = Fawaterak::getPayment($invoiceKey);
```

## معالجة Webhooks

الحزمة تتعامل تلقائياً مع webhooks من Fawaterak. يمكنك الاستماع للأحداث:

```php
// في EventServiceProvider
use Illuminate\Support\Facades\Event;

Event::listen('fawaterak.webhook.received', function ($data) {
    // معالجة عامة لجميع الـ webhooks
    Log::info('Webhook received', $data);
});

Event::listen('fawaterak.payment.paid', function ($data) {
    // معالجة الدفعات المكتملة
    $invoiceKey = $data['invoice_key'];
    // تحديث قاعدة البيانات، إرسال إيميل، إلخ
});

Event::listen('fawaterak.payment.failed', function ($data) {
    // معالجة الدفعات الفاشلة
});

Event::listen('fawaterak.payment.pending', function ($data) {
    // معالجة الدفعات المعلقة
});
```

## طرق الدفع المختلفة

### Visa/Mastercard

```php
$payment = Fawaterak::createPayment($customer, $items, $total, 2);
// سيتم إعادة توجيه العميل لصفحة الدفع
```

### Fawry

```php
$payment = Fawaterak::createPayment($customer, $items, $total, 3);
// سيحصل العميل على كود Fawry للدفع
$fawryCode = $payment['data']['payment_data']['fawryCode'];
```

### Meeza (Mobile Wallet)

```php
$payment = Fawaterak::createPayment($customer, $items, $total, 4, [
    'mobile_wallet_number' => '01234567890'
]);
// سيحصل العميل على QR Code للدفع
$qrCode = $payment['data']['payment_data']['meezaQrCode'];
```

## الخيارات المتقدمة

### تخصيص URLs إعادة التوجيه

```php
$options = [
    'redirection_urls' => [
        'successUrl' => 'https://yoursite.com/payment/success',
        'failUrl' => 'https://yoursite.com/payment/failed',
        'pendingUrl' => 'https://yoursite.com/payment/pending'
    ]
];
```

### إضافة خصم

```php
$options = [
    'discount_data' => [
        'type' => 'pcg', // أو 'literal'
        'value' => 10 // 10% أو 10 جنيه
    ]
];
```

### إضافة ضريبة

```php
$options = [
    'tax_data' => [
        'title' => 'ضريبة القيمة المضافة',
        'value' => 14 // 14%
    ]
];
```

## الاختبار

تشغيل الاختبارات:

```bash
composer test
```

تشغيل الاختبارات مع تقرير التغطية:

```bash
composer test-coverage
```

## المساهمة

نرحب بالمساهمات! يرجى قراءة [دليل المساهمة](CONTRIBUTING.md) للمزيد من التفاصيل.

## الأمان

إذا اكتشفت مشكلة أمنية، يرجى إرسال إيميل إلى mohamedalgamal@gmail.com بدلاً من استخدام issue tracker.

## الترخيص

هذه الحزمة مرخصة تحت [رخصة MIT](LICENSE.md).

## الدعم

- [التوثيق الرسمي لـ Fawaterak](https://fawaterak-api.readme.io/)
- [GitHub Issues](https://github.com/algmaal/laravel-fawaterak/issues)
- [البريد الإلكتروني](mailto:mohamedalgamal@gmail.com)
