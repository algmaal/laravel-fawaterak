# دليل التثبيت والإعداد - Laravel Fawaterak

## متطلبات النظام

- PHP 8.1 أو أحدث
- Laravel 10.0 أو أحدث
- Composer
- GuzzleHTTP 7.0 أو أحدث

## خطوات التثبيت

### 1. تثبيت الحزمة

```bash
composer require algmaal/laravel-fawaterak
```

### 2. نشر ملف التكوين

```bash
php artisan vendor:publish --provider="Algmaal\LaravelFawaterak\FawaterakServiceProvider" --tag="fawaterak-config"
```

### 3. إعداد متغيرات البيئة

أضف المتغيرات التالية إلى ملف `.env`:

```env
# البيئة الافتراضية
FAWATERAK_ENVIRONMENT=staging

# إعدادات بيئة التطوير
FAWATERAK_STAGING_API_KEY=your_staging_api_key_here
FAWATERAK_STAGING_BASE_URL=https://staging.fawaterk.com
FAWATERAK_STAGING_WEBHOOK_SECRET=your_staging_webhook_secret_here

# إعدادات بيئة الإنتاج (عند الجاهزية)
FAWATERAK_PRODUCTION_API_KEY=your_production_api_key_here
FAWATERAK_PRODUCTION_BASE_URL=https://app.fawaterak.com
FAWATERAK_PRODUCTION_WEBHOOK_SECRET=your_production_webhook_secret_here

# إعدادات إضافية
FAWATERAK_DEFAULT_CURRENCY=EGP
FAWATERAK_CACHE_ENABLED=true
FAWATERAK_LOGGING_ENABLED=true
```

### 4. الحصول على API Keys من Fawaterak

1. سجل حساب في [Fawaterak](https://fawaterak.com)
2. اذهب إلى لوحة التحكم
3. انسخ API Key من قسم الإعدادات
4. احصل على Webhook Secret من إعدادات الـ Webhooks

### 5. إعداد Webhooks

في لوحة تحكم Fawaterak:

1. اذهب إلى إعدادات Webhooks
2. أضف URL: `https://yoursite.com/fawaterak/webhook`
3. فعل الأحداث المطلوبة
4. احفظ Webhook Secret في متغيرات البيئة

### 6. إعداد Routes (اختياري)

إذا كنت تريد تخصيص routes الـ webhooks، أضف في `routes/web.php`:

```php
use Algmaal\LaravelFawaterak\Http\Controllers\WebhookController;

Route::post('custom-webhook-url', [WebhookController::class, 'handle'])
    ->name('custom.fawaterak.webhook');
```

### 7. إعداد Event Listeners

في `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    'fawaterak.webhook.received' => [
        'App\Listeners\ProcessFawaterakWebhook',
    ],
    'fawaterak.payment.paid' => [
        'App\Listeners\HandleSuccessfulPayment',
    ],
    'fawaterak.payment.failed' => [
        'App\Listeners\HandleFailedPayment',
    ],
];
```

### 8. إنشاء Listeners

```bash
php artisan make:listener ProcessFawaterakWebhook
php artisan make:listener HandleSuccessfulPayment
php artisan make:listener HandleFailedPayment
```

## التحقق من التثبيت

### تشغيل الاختبارات

```bash
# تثبيت dependencies للتطوير
composer install --dev

# تشغيل الاختبارات
vendor/bin/phpunit
```

### اختبار الاتصال بـ API

```php
use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;

// في Controller أو Artisan Command
$fawaterak = app(FawaterakServiceInterface::class);
$methods = $fawaterak->getPaymentMethods();

if ($methods['status'] === 'success') {
    echo "الاتصال بـ API نجح!";
} else {
    echo "فشل الاتصال بـ API";
}
```

## إعداد البيئة الإنتاجية

### 1. تغيير البيئة

```env
FAWATERAK_ENVIRONMENT=production
```

### 2. استخدام API Keys الإنتاجية

```env
FAWATERAK_PRODUCTION_API_KEY=your_real_production_key
FAWATERAK_PRODUCTION_WEBHOOK_SECRET=your_real_webhook_secret
```

### 3. تفعيل SSL Verification

```env
FAWATERAK_HTTP_VERIFY_SSL=true
```

### 4. إعداد Logging

```env
FAWATERAK_LOG_CHANNEL=fawaterak
FAWATERAK_LOG_LEVEL=warning
```

## استكشاف الأخطاء

### مشاكل شائعة

1. **خطأ في API Key**

   ```
   InvalidConfigurationException: API key is not configured
   ```

   **الحل**: تأكد من إعداد `FAWATERAK_STAGING_API_KEY` في `.env`

2. **فشل Webhook Signature**

   ```
   Webhook signature verification failed
   ```

   **الحل**: تأكد من `FAWATERAK_STAGING_WEBHOOK_SECRET` صحيح

3. **خطأ في الاتصال**
   ```
   HTTP request failed: Connection timeout
   ```
   **الحل**: تحقق من الاتصال بالإنترنت وإعدادات Firewall

### تفعيل Debug Mode

```env
FAWATERAK_LOGGING_ENABLED=true
FAWATERAK_LOG_REQUESTS=true
FAWATERAK_LOG_RESPONSES=true
```

### فحص Logs

```bash
tail -f storage/logs/laravel.log | grep Fawaterak
```

## الدعم

إذا واجهت مشاكل في التثبيت:

1. تحقق من [Issues على GitHub](https://github.com/algmaal/laravel-fawaterak/issues)
2. راجع [التوثيق الرسمي](README.md)
3. تواصل عبر [البريد الإلكتروني](mailto:mohamedalgamal@gmail.com)
