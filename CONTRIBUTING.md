# دليل المساهمة في Laravel Fawaterak

شكراً لاهتمامك بالمساهمة في حزمة Laravel Fawaterak! نرحب بجميع أنواع المساهمات.

## كيفية المساهمة

### الإبلاغ عن الأخطاء

إذا وجدت خطأ، يرجى إنشاء Issue جديد مع:

- وصف واضح للمشكلة
- خطوات إعادة إنتاج الخطأ
- إصدار PHP وLaravel المستخدم
- رسائل الخطأ كاملة

### اقتراح ميزات جديدة

لاقتراح ميزة جديدة:

1. تأكد أن الميزة لم يتم اقتراحها من قبل
2. اشرح الحاجة للميزة
3. قدم أمثلة على الاستخدام
4. ناقش التنفيذ المقترح

### إرسال Pull Requests

1. **Fork المشروع**

   ```bash
   git clone https://github.com/algmaal/laravel-fawaterak.git
   cd laravel-fawaterak
   ```

2. **إنشاء branch جديد**

   ```bash
   git checkout -b feature/new-feature
   # أو
   git checkout -b fix/bug-description
   ```

3. **تثبيت Dependencies**

   ```bash
   composer install
   ```

4. **إجراء التغييرات**

   - اتبع معايير الكود المحددة
   - أضف اختبارات للميزات الجديدة
   - تأكد من تمرير جميع الاختبارات

5. **تشغيل الاختبارات**

   ```bash
   vendor/bin/phpunit
   ```

6. **Commit التغييرات**

   ```bash
   git add .
   git commit -m "Add: وصف واضح للتغيير"
   ```

7. **Push إلى GitHub**

   ```bash
   git push origin feature/new-feature
   ```

8. **إنشاء Pull Request**

## معايير الكود

### PHP Standards

- اتبع PSR-12 coding standard
- استخدم type hints للمعاملات والإرجاع
- أضف DocBlocks للدوال والكلاسات
- استخدم أسماء واضحة للمتغيرات والدوال

### Laravel Best Practices

- استخدم Laravel conventions
- اتبع نمط Repository/Service
- استخدم Eloquent بدلاً من Query Builder عند الإمكان
- اتبع Laravel naming conventions

### الاختبارات

- اكتب Unit tests لجميع الدوال الجديدة
- اكتب Feature tests للوظائف المعقدة
- تأكد من تغطية 80% على الأقل
- استخدم أسماء واضحة للاختبارات

```php
public function test_it_creates_payment_successfully()
{
    // Arrange
    $customerData = $this->getValidCustomerData();

    // Act
    $result = $this->paymentService->createPayment(...);

    // Assert
    $this->assertArrayHasKey('invoice_id', $result);
}
```

### التوثيق

- أضف تعليقات باللغة العربية للدوال المعقدة
- حدث README.md عند إضافة ميزات جديدة
- أضف أمثلة للاستخدام
- حدث CHANGELOG.md

## هيكل المشروع

```
src/
├── Contracts/          # Interfaces
├── Services/           # Business logic
├── Http/
│   └── Controllers/    # HTTP controllers
├── Facades/           # Laravel facades
├── Exceptions/        # Custom exceptions
└── FawaterakServiceProvider.php

tests/
├── Unit/              # Unit tests
├── Feature/           # Feature tests
└── TestCase.php       # Base test class

config/
└── fawaterak.php      # Configuration file
```

## Git Workflow

### Commit Messages

استخدم الصيغة التالية:

```
Type: وصف قصير بالعربية

وصف مفصل إذا لزم الأمر
```

أنواع Commits:

- `Add:` إضافة ميزة جديدة
- `Fix:` إصلاح خطأ
- `Update:` تحديث ميزة موجودة
- `Remove:` حذف كود
- `Refactor:` إعادة هيكلة الكود
- `Test:` إضافة أو تحديث اختبارات
- `Docs:` تحديث التوثيق

### Branch Naming

- `feature/feature-name` للميزات الجديدة
- `fix/bug-description` لإصلاح الأخطاء
- `docs/update-readme` لتحديث التوثيق
- `test/add-unit-tests` للاختبارات

## إرشادات الأمان

- لا تضع API keys في الكود
- استخدم environment variables
- تحقق من صحة جميع المدخلات
- استخدم HTTPS في جميع الطلبات
- تحقق من webhook signatures

## الحصول على المساعدة

إذا كنت بحاجة لمساعدة:

1. راجع [التوثيق](README.md)
2. ابحث في [Issues المغلقة](https://github.com/algmaal/laravel-fawaterak/issues?q=is%3Aissue+is%3Aclosed)
3. اسأل في [Discussions](https://github.com/algmaal/laravel-fawaterak/discussions)
4. تواصل عبر البريد الإلكتروني: mohamedalgamal@gmail.com

## Code of Conduct

نتوقع من جميع المساهمين:

- الاحترام المتبادل
- التعاون البناء
- تقبل النقد البناء
- المساعدة في تحسين المشروع

## الترخيص

بمساهمتك في هذا المشروع، توافق على أن مساهماتك ستكون مرخصة تحت [رخصة MIT](LICENSE.md).

شكراً لمساهمتك! 🚀
