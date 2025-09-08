<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// تفعيل عرض جميع الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// إذا لم يكن الطلب POST → رجع المستخدم
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("❌ الطلب غير صالح: يجب إرسال البيانات باستخدام POST");
}

// التحقق من CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    die("❌ فشل التحقق من CSRF Token");
}

// استلام البيانات
$username    = sanitizeInput($_POST['username'] ?? '');
$password    = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// التحقق من القيم الفارغة
if (empty($username) || empty($password)) {
    die("❌ خطأ: اسم المستخدم أو كلمة المرور فارغ");
}

try {
    // الاتصال بقاعدة البيانات
    $db = getDBConnection();

    if (!$db) {
        die("❌ خطأ: لم يتم الاتصال بقاعدة البيانات");
    }

    // إنشاء مدير المستخدمين
    $userManager = new UserManager($db);

    // محاولة تسجيل الدخول
    $user = $userManager->login($username, $password);

    if ($user) {
        // ✅ تسجيل الدخول ناجح
        loginUser($user);

        // خيار "تذكرني"
        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            setcookie(
                'remember_token',
                $token,
                time() + (30 * 24 * 60 * 60), // 30 يوم
                '/',
                '',
                false,
                true
            );
            // ملاحظة: هنا ممكن تخزن التوكن في قاعدة البيانات للتحقق لاحقًا
        }

        // توجيه المستخدم
        if (!empty($user['is_admin']) && $user['is_admin'] == 1) {
            echo "✅ تسجيل الدخول ناجح كمدير. سيتم تحويلك...";
            header("Refresh:2; url=../admin/dashboard.php");
        } else {
            echo "✅ تسجيل الدخول ناجح كمستخدم. سيتم تحويلك...";
            header("Refresh:2; url=../user/dashboard.php");
        }

    } else {
        // ❌ فشل تسجيل الدخول
        logActivity(null, 'failed_login', 'محاولة تسجيل دخول فاشلة: ' . $username);
        die("❌ خطأ: بيانات الدخول غير صحيحة");
    }

} catch (Exception $e) {
    // 🛑 عرض الخطأ بشكل مباشر أثناء التطوير
    die("🚨 خطأ في تسجيل الدخول: " . $e->getMessage());
}
