<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول وصلاحيات الإدارة
requireLogin(true);

// التحقق أن الطلب جاء بـ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php?error=invalid_request');
}

// التحقق من رمز CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirect('dashboard.php?error=invalid_csrf');
}

// الحصول على البيانات وتنظيفها
$user_id = intval($_POST['user_id'] ?? 0);
$duration_seconds = intval($_POST['duration_seconds'] ?? 0);

// التحقق من صحة البيانات
$errors = [];

if ($user_id <= 0) {
    $errors[] = 'معرف المستخدم غير صحيح';
}

if ($duration_seconds < 60) {
    $errors[] = 'مدة الاشتراك يجب أن تكون على الأقل 60 ثانية (دقيقة واحدة)';
}

// حد أقصى للمدة (سنة واحدة = 365 يوم)
$max_duration = 365 * 24 * 60 * 60;
if ($duration_seconds > $max_duration) {
    $errors[] = 'مدة الاشتراك لا يمكن أن تتجاوز سنة واحدة';
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

    // إنشاء مدير المستخدمين والاشتراكات
    $userManager = new UserManager($db);
    $subscriptionManager = new SubscriptionManager($db);

    // التحقق من وجود المستخدم
    $user = $userManager->getUserById($user_id);
    if (!$user) {
        redirect('dashboard.php?error=user_not_found');
    }

    // تحديث اشتراك المستخدم
    $subscription_updated = $subscriptionManager->updateUserSubscription($user_id, $duration_seconds);

    if ($subscription_updated) {
        // تسجيل النشاط
        $duration_readable = secondsToReadable($duration_seconds);
        logActivity(
            $_SESSION['user_id'],
            'admin_manage_subscription',
            'تم تحديث اشتراك المستخدم: ' . htmlspecialchars($user['username']) . ' لمدة: ' . $duration_readable
        );

        redirect('dashboard.php?success=subscription_updated');
    } else {
        redirect('dashboard.php?error=subscription_update_failed');
    }

} catch (Exception $e) {
    // تسجيل الخطأ
    error_log("خطأ في إدارة الاشتراك: " . $e->getMessage());
    redirect('dashboard.php?error=system_error');
} finally {
    // إغلاق الاتصال
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>
