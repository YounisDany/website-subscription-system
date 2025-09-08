<?php
require_once 'config.php';
require_once 'database.php';

// دالة للتحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// دالة للتحقق من صلاحيات الإدارة
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// دالة لتسجيل الدخول
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['login_time'] = time();
    
    // تسجيل النشاط
    logActivity($user['id'], 'login', 'تسجيل دخول المستخدم');
}

// دالة لتسجيل الخروج
function logoutUser() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'تسجيل خروج المستخدم');
    }
    
    session_unset();
    session_destroy();
    session_start();
}

// دالة للتحقق من انتهاء صلاحية الجلسة
function checkSessionTimeout() {
    if (isLoggedIn() && isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            logoutUser();
            return false;
        }
    }
    return true;
}

// دالة لتوجيه المستخدم
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// دالة للتحقق من الصلاحيات وتوجيه المستخدم
function requireLogin($admin_only = false) {
    if (!checkSessionTimeout()) {
        redirect('../auth/login.php?error=session_expired');
    }
    
    if (!isLoggedIn()) {
        redirect('../auth/login.php?error=login_required');
    }
    
    if ($admin_only && !isAdmin()) {
        redirect('../user/dashboard.php?error=access_denied');
    }
}

// دالة لتسجيل النشاط
function logActivity($user_id, $action, $description = '', $ip_address = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($ip_address === null) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                  VALUES (:user_id, :action, :description, :ip_address)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip_address', $ip_address);
        
        $stmt->execute();
        $database->closeConnection();
    } catch (Exception $e) {
        // تجاهل أخطاء تسجيل النشاط لتجنب توقف النظام
        error_log("خطأ في تسجيل النشاط: " . $e->getMessage());
    }
}

// دالة لعرض الرسائل
function displayMessage($type, $message) {
    $alert_class = '';
    switch ($type) {
        case 'success':
            $alert_class = 'alert-success';
            break;
        case 'error':
            $alert_class = 'alert-danger';
            break;
        case 'warning':
            $alert_class = 'alert-warning';
            break;
        case 'info':
            $alert_class = 'alert-info';
            break;
        default:
            $alert_class = 'alert-info';
    }
    
    return '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

// دالة لتنسيق الوقت المتبقي
function formatRemainingTime($remaining_time) {
    if (!$remaining_time || $remaining_time['total_seconds'] <= 0) {
        return 'منتهي الصلاحية';
    }
    
    $output = '';
    if ($remaining_time['days'] > 0) {
        $output .= $remaining_time['days'] . ' يوم ';
    }
    if ($remaining_time['hours'] > 0) {
        $output .= $remaining_time['hours'] . ' ساعة ';
    }
    if ($remaining_time['minutes'] > 0) {
        $output .= $remaining_time['minutes'] . ' دقيقة ';
    }
    if ($remaining_time['seconds'] > 0 && $remaining_time['days'] == 0) {
        $output .= $remaining_time['seconds'] . ' ثانية';
    }
    
    return trim($output);
}

// دالة لتحويل الثواني إلى تنسيق قابل للقراءة
function secondsToReadable($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    $output = '';
    if ($days > 0) $output .= $days . ' يوم ';
    if ($hours > 0) $output .= $hours . ' ساعة ';
    if ($minutes > 0) $output .= $minutes . ' دقيقة ';
    if ($secs > 0 && $days == 0) $output .= $secs . ' ثانية';
    
    return trim($output);
}

// دالة للحصول على رسائل الخطأ المترجمة
function getErrorMessage($error_code) {
    $messages = [
        'login_required' => 'يجب تسجيل الدخول للوصول إلى هذه الصفحة',
        'access_denied' => 'ليس لديك صلاحية للوصول إلى هذه الصفحة',
        'session_expired' => 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى',
        'invalid_credentials' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
        'username_exists' => 'اسم المستخدم موجود بالفعل',
        'email_exists' => 'البريد الإلكتروني موجود بالفعل',
        'weak_password' => 'كلمة المرور ضعيفة، يجب أن تكون على الأقل 8 أحرف',
        'invalid_email' => 'البريد الإلكتروني غير صحيح',
        'registration_failed' => 'فشل في إنشاء الحساب، يرجى المحاولة مرة أخرى',
        'subscription_expired' => 'انتهت صلاحية اشتراكك',
        'invalid_csrf' => 'رمز الأمان غير صحيح'
    ];
    
    return isset($messages[$error_code]) ? $messages[$error_code] : 'حدث خطأ غير معروف';
}
?>

