# دليل التثبيت السريع - نظام إدارة الاشتراكات

## التثبيت السريع (5 دقائق)

### الخطوة 1: تحضير البيئة
```bash
# تأكد من تشغيل خدمات MySQL و Apache
sudo systemctl start mysql
sudo systemctl start apache2

# أو باستخدام XAMPP
sudo /opt/lampp/lampp start
```

### الخطوة 2: رفع الملفات
```bash
# انسخ جميع ملفات المشروع إلى مجلد الويب
cp -r website/* /var/www/html/subscription-system/

# أو في XAMPP
cp -r website/* /opt/lampp/htdocs/subscription-system/
```

### الخطوة 3: إعداد قاعدة البيانات
```sql
-- إنشاء قاعدة البيانات
CREATE DATABASE subscription_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- إنشاء مستخدم (اختياري)
CREATE USER 'sub_user'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON subscription_system.* TO 'sub_user'@'localhost';
FLUSH PRIVILEGES;
```

### الخطوة 4: تشغيل معالج الإعداد
1. افتح المتصفح واذهب إلى: `http://localhost/subscription-system/setup.php`
2. اتبع خطوات المعالج:
   - أدخل معلومات قاعدة البيانات
   - انتظر إنشاء الجداول
   - أنشئ حساب الإدارة
3. احذف ملف `setup.php` بعد اكتمال التثبيت

### الخطوة 5: اختبار النظام
- اذهب إلى: `http://localhost/subscription-system/test_system.php`
- تأكد من نجاح جميع الاختبارات
- إذا فشل أي اختبار، راجع الرسائل وأصلح المشاكل

## الوصول للنظام

### حساب الإدارة
- الرابط: `http://localhost/subscription-system/admin/dashboard.php`
- اسم المستخدم: الذي أدخلته في معالج الإعداد
- كلمة المرور: الذي أدخلتها في معالج الإعداد

### إنشاء حساب مستخدم جديد
- الرابط: `http://localhost/subscription-system/auth/register.php`

## إعدادات متقدمة

### تخصيص الإعدادات
عدّل ملف `includes/config.php`:

```php
// مهلة الجلسة (بالثواني)
define('SESSION_TIMEOUT', 7200); // ساعتان

// اسم الموقع
define('SITE_NAME', 'اسم موقعك');

// رابط الموقع
define('SITE_URL', 'https://yoursite.com');
```

### إعداد البريد الإلكتروني (اختياري)
أضف إعدادات SMTP في `includes/config.php`:

```php
// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### تأمين النظام للإنتاج

1. **تحديث كلمات المرور الافتراضية**
2. **تمكين HTTPS**
3. **تحديث صلاحيات الملفات:**
   ```bash
   chmod 644 *.php
   chmod 755 includes/
   chmod 600 includes/config.php
   ```
4. **إخفاء معلومات PHP:**
   ```php
   // في .htaccess
   php_flag display_errors off
   php_flag log_errors on
   ```

## استكشاف الأخطاء

### مشاكل شائعة

#### "خطأ في الاتصال بقاعدة البيانات"
- تحقق من تشغيل MySQL
- تأكد من صحة معلومات الاتصال في `config.php`
- تحقق من وجود قاعدة البيانات

#### "الصفحة لا تعمل"
- تحقق من تشغيل Apache/Nginx
- تأكد من صحة مسار الملفات
- راجع سجلات الأخطاء

#### "مشاكل في التصميم"
- تحقق من الاتصال بالإنترنت (Bootstrap CDN)
- تأكد من وجود ملف `css/style.css`

### سجلات الأخطاء
راجع سجلات الأخطاء في:
- `/var/log/apache2/error.log`
- `/var/log/mysql/error.log`
- أو في XAMPP: `/opt/lampp/logs/`

## الدعم

إذا واجهت أي مشاكل:
1. راجع ملف `README.md` للتفاصيل الكاملة
2. شغّل `test_system.php` لتشخيص المشاكل
3. تحقق من متطلبات النظام
4. راجع سجلات الأخطاء

---

**ملاحظة:** هذا الدليل للتثبيت المحلي فقط. للنشر في الإنتاج، راجع دليل الأمان في `README.md`.

