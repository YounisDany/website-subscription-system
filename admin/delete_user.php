<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول وصلاحيات الإدارة
requireLogin(true);

// التحقق من أن الطلب GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    redirect('dashboard.php?error=invalid_request');
}

// التحقق من رمز CSRF
if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
    redirect('dashboard.php?error=invalid_csrf');
}

// الحصول على معرف المستخدم
$user_id = intval($_GET['id'] ?? 0);

// التحقق من صحة البيانات
if ($user_id <= 0) {
    redirect('dashboard.php?error=invalid_user_id');
}

// التأكد من عدم حذف المستخدم الحالي
if ($user_id == $_SESSION['user_id']) {
    redirect('dashboard.php?error=cannot_delete_self');
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
    
    // التحقق من وجود المستخدم والحصول على بياناته
    $user = $userManager->getUserById($user_id);
    if (!$user) {
        redirect('dashboard.php?error=user_not_found');
    }
    
    // التأكد من أن المستخدم ليس مديراً
    if ($user['is_admin'] == 1) {
        redirect('dashboard.php?error=cannot_delete_admin');
    }
    
    // حذف المستخدم (سيتم حذف الاشتراكات تلقائياً بسبب CASCADE)
    $deleted = $userManager->deleteUser($user_id);
    
    if ($deleted) {
        // تسجيل النشاط
        logActivity($_SESSION['user_id'], 'admin_delete_user', 'تم حذف المستخدم: ' . $user['username']);
        
        redirect('dashboard.php?success=user_deleted');
    } else {
        redirect('dashboard.php?error=delete_failed');
    }
    
} catch (Exception $e) {
    // تسجيل الخطأ
    error_log("خطأ في حذف المستخدم: " . $e->getMessage());
    redirect('dashboard.php?error=system_error');
} finally {
    // إغلاق الاتصال
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>

