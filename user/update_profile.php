<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
requireLogin();

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php?error=invalid_request');
}

// التحقق من رمز CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirect('profile.php?error=invalid_csrf');
}

$user_id = $_SESSION['user_id'];

// الحصول على البيانات وتنظيفها
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// التحقق من صحة البيانات
$errors = [];

if (empty($username)) {
    $errors[] = 'اسم المستخدم مطلوب';
}

if (empty($email)) {
    $errors[] = 'البريد الإلكتروني مطلوب';
}

// التحقق من صحة البريد الإلكتروني
if (!validateEmail($email)) {
    $errors[] = 'البريد الإلكتروني غير صحيح';
}

// التحقق من قوة كلمة المرور (إذا تم إدخالها)
if (!empty($password)) {
    if (!validatePassword($password)) {
        $errors[] = 'كلمة المرور يجب أن تكون على الأقل 8 أحرف';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'كلمات المرور غير متطابقة';
    }
}

// التحقق من صحة اسم المستخدم
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'اسم المستخدم يجب أن يحتوي على أحرف وأرقام فقط';
}

if (strlen($username) < 3 || strlen($username) > 20) {
    $errors[] = 'اسم المستخدم يجب أن يكون بين 3 و 20 حرف';
}

// إذا كان هناك أخطاء، إرجاع المستخدم مع رسالة الخطأ
if (!empty($errors)) {
    $error_message = implode(', ', $errors);
    redirect('profile.php?error=validation_failed&message=' . urlencode($error_message));
}

try {
    // الاتصال بقاعدة البيانات
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        redirect('profile.php?error=database_error');
    }
    
    // إنشاء مدير المستخدمين
    $userManager = new UserManager($db);
    
    // الحصول على المعلومات الحالية للمستخدم
    $current_user = $userManager->getUserById($user_id);
    if (!$current_user) {
        redirect('profile.php?error=user_not_found');
    }
    
    // التحقق من وجود اسم المستخدم (إذا كان مختلف عن الحالي)
    if ($username !== $current_user['username'] && $userManager->usernameExists($username)) {
        redirect('profile.php?error=username_exists');
    }
    
    // التحقق من وجود البريد الإلكتروني (إذا كان مختلف عن الحالي)
    if ($email !== $current_user['email'] && $userManager->emailExists($email)) {
        redirect('profile.php?error=email_exists');
    }
    
    // تحديث بيانات المستخدم
    $query = "UPDATE users SET username = :username, email = :email";
    $params = [
        ':username' => $username,
        ':email' => $email,
        ':user_id' => $user_id
    ];
    
    // إضافة كلمة المرور إذا تم إدخالها
    if (!empty($password)) {
        $query .= ", password = :password";
        $params[':password'] = password_hash($password, HASH_ALGO);
    }
    
    $query .= " WHERE id = :user_id";
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        // تحديث معلومات الجلسة
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        // تسجيل النشاط
        logActivity($user_id, 'profile_update', 'تم تحديث الملف الشخصي');
        
        redirect('profile.php?success=profile_updated');
    } else {
        redirect('profile.php?error=update_failed');
    }
    
} catch (Exception $e) {
    // تسجيل الخطأ
    error_log("خطأ في تحديث الملف الشخصي: " . $e->getMessage());
    redirect('profile.php?error=system_error');
} finally {
    // إغلاق الاتصال
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>

