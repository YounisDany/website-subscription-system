<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
requireLogin();

// إذا كان المستخدم مديراً، توجيهه إلى لوحة تحكم الإدارة
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$subscription_valid = false;
$remaining_time = null;
$subscription_info = null;

try {
    // الاتصال بقاعدة البيانات
    $database = new Database();
    $db = $database->getConnection();
    
    $userManager = new UserManager($db);
    $subscriptionManager = new SubscriptionManager($db);
    
    // الحصول على معلومات المستخدم
    $user = $userManager->getUserById($user_id);
    
    // التحقق من صحة الاشتراك
    $subscription_valid = $subscriptionManager->isSubscriptionValid($user_id);
    
    // الحصول على معلومات الاشتراك
    $subscription_info = $subscriptionManager->getUserSubscription($user_id);
    
    // الحصول على الوقت المتبقي
    if ($subscription_valid) {
        $remaining_time = $subscriptionManager->getRemainingTime($user_id);
    }
    
} catch (Exception $e) {
    error_log("خطأ في داشبورد المستخدم: " . $e->getMessage());
    $error_message = "حدث خطأ في تحميل البيانات";
}

$success_message = '';
$error_message = '';

// معالجة الرسائل
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'login':
            $success_message = 'مرحباً بك، تم تسجيل الدخول بنجاح';
            break;
    }
}

if (isset($_GET['error'])) {
    $error_message = getErrorMessage($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-gradient" href="#">
                <i class="bi bi-house-door me-2"></i><?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>لوحة التحكم
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person me-1"></i>الملف الشخصي
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="bi bi-person me-2"></i>الملف الشخصي
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../admin/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- عرض الرسائل -->
        <?php if (!empty($success_message)): ?>
            <?php echo displayMessage('success', $success_message); ?>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <?php echo displayMessage('error', $error_message); ?>
        <?php endif; ?>

        <div class="row">
            <!-- معلومات الاشتراك -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-custom">
                    <div class="card-header <?php echo $subscription_valid ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check me-2"></i>حالة الاشتراك
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($subscription_valid): ?>
                            <div class="subscription-status subscription-active mb-4">
                                <i class="bi bi-check-circle-fill display-4 mb-3"></i>
                                <h4>اشتراكك نشط</h4>
                                <p class="mb-0">يمكنك الوصول إلى جميع الخدمات</p>
                            </div>
                            
                            <?php if ($subscription_info): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-calendar-plus me-2"></i>تاريخ البدء:</h6>
                                        <p class="text-muted"><?php echo date('Y-m-d H:i:s', strtotime($subscription_info['start_date'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-calendar-x me-2"></i>تاريخ الانتهاء:</h6>
                                        <p class="text-muted"><?php echo date('Y-m-d H:i:s', strtotime($subscription_info['end_date'])); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="subscription-status subscription-expired mb-4">
                                <i class="bi bi-x-circle-fill display-4 mb-3"></i>
                                <h4>انتهت صلاحية اشتراكك</h4>
                                <p class="mb-0">يرجى تجديد الاشتراك للوصول إلى الخدمات</p>
                            </div>
                            
                            <div class="text-center">
                                <button class="btn btn-primary btn-lg" onclick="contactSupport()">
                                    <i class="bi bi-telephone me-2"></i>تواصل مع الدعم لتجديد الاشتراك
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- العداد التنازلي -->
            <div class="col-lg-4 mb-4">
                <?php if ($subscription_valid && $remaining_time): ?>
                    <div class="countdown-timer" id="countdownTimer">
                        <h5 class="mb-3">
                            <i class="bi bi-clock me-2"></i>الوقت المتبقي
                        </h5>
                        <div class="countdown-display">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="countdown-number" id="days"><?php echo $remaining_time['days']; ?></div>
                                    <div class="countdown-label">يوم</div>
                                </div>
                                <div class="col-6">
                                    <div class="countdown-number" id="hours"><?php echo $remaining_time['hours']; ?></div>
                                    <div class="countdown-label">ساعة</div>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col-6">
                                    <div class="countdown-number" id="minutes"><?php echo $remaining_time['minutes']; ?></div>
                                    <div class="countdown-label">دقيقة</div>
                                </div>
                                <div class="col-6">
                                    <div class="countdown-number" id="seconds"><?php echo $remaining_time['seconds']; ?></div>
                                    <div class="countdown-label">ثانية</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-custom">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
                            <h5>لا يوجد اشتراك نشط</h5>
                            <p class="text-muted">يرجى تجديد اشتراكك للاستمتاع بالخدمات</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- محتوى الداشبورد الرئيسي -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-custom">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-grid me-2"></i>الخدمات المتاحة
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($subscription_valid): ?>
                            <!-- محتوى متاح للمشتركين النشطين -->
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-text display-4 text-success mb-3"></i>
                                            <h5>إدارة الملفات</h5>
                                            <p class="text-muted">رفع وإدارة ملفاتك بأمان</p>
                                            <button class="btn btn-success">الوصول</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-info">
                                        <div class="card-body text-center">
                                            <i class="bi bi-graph-up display-4 text-info mb-3"></i>
                                            <h5>التقارير</h5>
                                            <p class="text-muted">عرض وتحليل البيانات</p>
                                            <button class="btn btn-info">الوصول</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body text-center">
                                            <i class="bi bi-gear display-4 text-warning mb-3"></i>
                                            <h5>الإعدادات</h5>
                                            <p class="text-muted">تخصيص حسابك وإعداداتك</p>
                                            <button class="btn btn-warning">الوصول</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-success mt-4" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>مرحباً بك!</strong> يمكنك الآن الوصول إلى جميع الخدمات المتاحة في نظامنا.
                            </div>
                        <?php else: ?>
                            <!-- محتوى للمستخدمين غير المشتركين -->
                            <div class="text-center py-5">
                                <i class="bi bi-lock display-1 text-muted mb-4"></i>
                                <h3 class="text-muted">الخدمات غير متاحة</h3>
                                <p class="lead text-muted mb-4">
                                    انتهت صلاحية اشتراكك. يرجى تجديد الاشتراك للوصول إلى الخدمات.
                                </p>
                                
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="alert alert-warning" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>تنبيه:</strong> لا يمكنك الوصول إلى الخدمات بدون اشتراك نشط.
                                        </div>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary btn-lg" onclick="contactSupport()">
                                    <i class="bi bi-telephone me-2"></i>تواصل مع الدعم
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // العداد التنازلي
        <?php if ($subscription_valid && $remaining_time): ?>
        let totalSeconds = <?php echo $remaining_time['total_seconds']; ?>;
        
        function updateCountdown() {
            if (totalSeconds <= 0) {
                // انتهى الاشتراك، إعادة تحميل الصفحة
                location.reload();
                return;
            }
            
            const days = Math.floor(totalSeconds / (24 * 60 * 60));
            const hours = Math.floor((totalSeconds % (24 * 60 * 60)) / (60 * 60));
            const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
            const seconds = totalSeconds % 60;
            
            document.getElementById('days').textContent = days;
            document.getElementById('hours').textContent = hours;
            document.getElementById('minutes').textContent = minutes;
            document.getElementById('seconds').textContent = seconds;
            
            totalSeconds--;
        }
        
        // تحديث العداد كل ثانية
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
        
        // دالة التواصل مع الدعم
        function contactSupport() {
            alert('يرجى التواصل مع الدعم الفني لتجديد اشتراكك.\n\nالبريد الإلكتروني: support@example.com\nالهاتف: +966-XX-XXX-XXXX');
        }
        
        // إزالة رسائل التنبيه تلقائياً
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // تحديث الصفحة كل 5 دقائق للتحقق من حالة الاشتراك
        setInterval(function() {
            // تحديث صامت للتحقق من حالة الاشتراك
            fetch('check_subscription.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.log('خطأ في التحقق من الاشتراك:', error);
                });
        }, 300000); // كل 5 دقائق
    </script>
</body>
</html>

