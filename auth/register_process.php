<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php?error=invalid_request');
}

// التحقق من رمز CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirect('register.php?error=invalid_csrf');
}

// الحصول على البيانات وتنظيفها
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$agree_terms = isset($_POST['agree_terms']);

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

if (empty($confirm_password)) {
    $errors[] = 'تأكيد كلمة المرور مطلوب';
}

if (!$agree_terms) {
    $errors[] = 'يجب الموافقة على الشروط والأحكام';
}

// التحقق من صحة البريد الإلكتروني
if (!validateEmail($email)) {
    $errors[] = 'البريد الإلكتروني غير صحيح';
}

// التحقق من قوة كلمة المرور
if (!validatePassword($password)) {
    $errors[] = 'كلمة المرور يجب أن تكون على الأقل 8 أحرف';
}

// التحقق من تطابق كلمات المرور
if ($password !== $confirm_password) {
    $errors[] = 'كلمات المرور غير متطابقة';
}

// التحقق من صحة اسم المستخدم (لا يحتوي على مسافات أو رموز خاصة)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'اسم المستخدم يجب أن يحتوي على أحرف وأرقام فقط';
}

if (strlen($username) < 3 || strlen($username) > 20) {
    $errors[] = 'اسم المستخدم يجب أن يكون بين 3 و 20 حرف';
}

// إذا كان هناك أخطاء، إرجاع المستخدم مع رسالة الخطأ
if (!empty($errors)) {
    $error_message = implode(', ', $errors);
    redirect('register.php?error=validation_failed&message=' . urlencode($error_message));
}

try {
    // الاتصال بقاعدة البيانات
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        redirect('register.php?error=database_error');
    }
    
    // إنشاء مدير المستخدمين
    $userManager = new UserManager($db);
    
    // التحقق من وجود اسم المستخدم
    if ($userManager->usernameExists($username)) {
        redirect('register.php?error=username_exists');
    }
    
    // التحقق من وجود البريد الإلكتروني
    if ($userManager->emailExists($email)) {
        redirect('register.php?error=email_exists');
    }
    
    // إنشاء المستخدم الجديد
    $user_id = $userManager->createUser($username, $email, $password, 0);
    
    if ($user_id) {
        // تسجيل النشاط
        logActivity($user_id, 'register', 'تم إنشاء حساب جديد');
        
        // إنشاء اشتراك تجريبي للمستخدم الجديد (اختياري)
        $subscriptionManager = new SubscriptionManager($db);
        // اشتراك تجريبي لمدة ساعة واحدة (3600 ثانية)
        $subscriptionManager->createSubscription($user_id, 3600);
        
        // توجيه المستخدم إلى صفحة تسجيل الدخول مع رسالة نجاح
        redirect('login.php?success=registered');
    } else {
        redirect('register.php?error=registration_failed');
    }
    
} catch (Exception $e) {
    // تسجيل الخطأ
    error_log("خطأ في إنشاء الحساب: " . $e->getMessage());
    redirect('register.php?error=system_error');
} finally {
    // إغلاق الاتصال
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>

