<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'subscription_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // مهلة الجلسة بالثواني (ساعة واحدة)

// إعدادات الأمان
define('HASH_ALGO', PASSWORD_DEFAULT);
define('CSRF_TOKEN_NAME', 'csrf_token');

// إعدادات الموقع
define('SITE_NAME', 'نظام إدارة الاشتراكات');
define('SITE_URL', 'http://localhost/website');

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تعيين المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');

// دالة لتوليد رمز CSRF
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// دالة للتحقق من رمز CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// دالة لتنظيف البيانات المدخلة
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// دالة للتحقق من صحة البريد الإلكتروني
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// دالة للتحقق من قوة كلمة المرور
function validatePassword($password) {
    // كلمة المرور يجب أن تكون على الأقل 8 أحرف
    return strlen($password) >= 8;
}
?>