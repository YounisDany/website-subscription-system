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
$user_id = intval($_POST['user_id'] ?? 0);
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// التحقق من صحة البيانات
$errors = [];

if ($user_id <= 0) {
    $errors[] = 'معرف المستخدم غير صحيح';
}

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
if (!empty($password) && !validatePassword($password)) {
    $errors[] = 'كلمة المرور يجب أن تكون على الأقل 8 أحرف';
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
    
    // التحقق من وجود المستخدم
    $existing_user = $userManager->getUserById($user_id);
    if (!$existing_user) {
        redirect('dashboard.php?error=user_not_found');
    }
    
    // التحقق من وجود اسم المستخدم (إذا كان مختلف عن الحالي)
    if ($username !== $existing_user['username'] && $userManager->usernameExists($username)) {
        redirect('dashboard.php?error=username_exists');
    }
    
    // التحقق من وجود البريد الإلكتروني (إذا كان مختلف عن الحالي)
    if ($email !== $existing_user['email'] && $userManager->emailExists($email)) {
        redirect('dashboard.php?error=email_exists');
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
        // تسجيل النشاط
        logActivity($_SESSION['user_id'], 'admin_edit_user', 'تم تعديل بيانات المستخدم: ' . $username);
        
        redirect('dashboard.php?success=user_updated');
    } else {
        redirect('dashboard.php?error=update_failed');
    }
    
} catch (Exception $e) {
    // تسجيل الخطأ
    error_log("خطأ في تعديل المستخدم: " . $e->getMessage());
    redirect('dashboard.php?error=system_error');
} finally {
    // إغلاق الاتصال
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>

