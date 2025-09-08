<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// تعيين نوع المحتوى JSON
header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    echo json_encode(['valid' => false, 'error' => 'not_logged_in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['valid' => false, 'remaining_time' => null];

try {
    // الاتصال بقاعدة البيانات
    $database = new Database();
    $db = $database->getConnection();
    
    $subscriptionManager = new SubscriptionManager($db);
    
    // التحقق من صحة الاشتراك
    $subscription_valid = $subscriptionManager->isSubscriptionValid($user_id);
    $response['valid'] = $subscription_valid;
    
    if ($subscription_valid) {
        // الحصول على الوقت المتبقي
        $remaining_time = $subscriptionManager->getRemainingTime($user_id);
        $response['remaining_time'] = $remaining_time;
    }
    
    $database->closeConnection();
    
} catch (Exception $e) {
    error_log("خطأ في التحقق من الاشتراك: " . $e->getMessage());
    $response['error'] = 'system_error';
}

echo json_encode($response);
?>

