<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول وصلاحيات الإدارة
requireLogin(true);

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php?error=invalid_request');
}

// التحقق من رمز CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirect('dashboard.php?error=invalid_csrf');
}

// الحصول على البيانات وتنظيفها
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$subscription_duration = intval($_POST['subscription_duration'] ?? 3600);

// التحقق من صحة البيانات
$errors = [];

if (empty($username)) {
    $errors[] = 'اسم المستخدم مطلوب';
}

if (empty($email)) {
    $errors[] = 'البريد الإلكتروني مطلوب';
}

if (empty($password)) {
    $errors[] = 'كلمة المرور مطلوبة';
}

// التحقق من صحة البريد الإلكتروني
if (!validateEmail($email)) {
    $errors[] = 'البريد الإلكتروني غير صحيح';
}

// التحقق من قوة كلمة المرور
if (!validatePassword($password)) {
    $errors[] = 'كلمة المرور يجب أن تكون على الأقل 8 أحرف';
}

// التحقق من صحة اسم المستخدم
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'اسم المستخدم يجب أن يحتوي على أحرف وأرقام فقط';
}

if (strlen($username) < 3 || strlen($username) > 20) {
    $errors[] = 'اسم المستخدم يجب أن يكون بين 3 و 20 حرف';
}

// التحقق من مدة الاشتراك
if ($subscription_duration < 60) {
    $errors[] = 'مدة الاشتراك يجب أن تكون على الأقل 60 ثانية';
}

// إذا كان هناك أخطاء، إرجاع المستخدم مع رسالة الخطأ
if (!empty($errors)) {
    $error_message = implode(', ', $errors);
    redirect('dashboard.php?error=validation_failed&message=' . urlencode($error_message));
}

try {
    // الاتصال بقاعدة البيانات
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        redirect('dashboard.php?error=database_error');
    }
    
    // إنشاء مدير المستخدمين
    $userManager = new UserManager($db);
    $subscriptionManager = new SubscriptionManager($db);
    
    // التحقق من وجود اسم المستخدم
    if ($userManager->usernameExists($username)) {
        redirect('dashboard.php?error=username_exists');
    }
    
    // التحقق من وجود البريد الإلكتروني
    if ($userManager->emailExists($email)) {
        redirect('dashboard.php?error=email_exists');
    }
    
    // إنشاء المستخدم الجديد
    $user_id = $userManager->createUser($username, $email, $password, 0);
    
    if ($user_id) {
        // إنشاء اشتراك للمستخدم الجديد
        $subscription_created = $subscriptionManager->createSubscription($user_id, $subscription_duration);
        
        if ($subscription_created) {
            // تسجيل النشاط
            logActivity($_SESSION['user_id'], 'admin_add_user', 'تم إضافة مستخدم جديد: ' . $username);
            
            redirect('dashboard.php?success=user_added');
        } else {
            // حذف المستخدم إذا فشل إنشاء الاشتراك
            $userManager->deleteUser($user_id);
            redirect('dashboard.php?error=subscription_creation_failed');
        }
    } else {
        redirect('dashboard.php?error=user_creation_failed');
    }
    
} catch (Exception $e) {
    // تسجيل الخطأ
    error_log("خطأ في إضافة المستخدم: " . $e->getMessage());
    redirect('dashboard.php?error=system_error');
} finally {
    // إغلاق الاتصال
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>

