<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'db.fr-pari1.bengt.wasmernet.com');
define('DB_PORT', '10272');

define('DB_NAME', 'subscription_system1');
define('DB_USER', 'e5d3894679cf80002ca7cec82685');
define('DB_PASS', '068be5d3-8946-7b8d-8000-c2edb328ab08');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // مهلة الجلسة بالثواني (ساعة واحدة)

// إعدادات الأمان
define('HASH_ALGO', PASSWORD_DEFAULT);
define('CSRF_TOKEN_NAME', 'csrf_token');

// إعدادات الموقع
define('SITE_NAME', 'نظام إدارة الاشتراكات');
define('SITE_URL', 'https://younis.wasmer.app/');

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تعيين المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');

// دالة الاتصال بقاعدة البيانات باستخدام PDO
function getDBConnection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("❌ خطأ الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
}

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
