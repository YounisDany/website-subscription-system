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
$user = null;
$subscription_info = null;

try {
    // الاتصال بقاعدة البيانات
    $database = new Database();
    $db = $database->getConnection();
    
    $userManager = new UserManager($db);
    $subscriptionManager = new SubscriptionManager($db);
    
    // الحصول على معلومات المستخدم
    $user = $userManager->getUserById($user_id);
    
    // الحصول على معلومات الاشتراك
    $subscription_info = $subscriptionManager->getUserSubscription($user_id);
    
} catch (Exception $e) {
    error_log("خطأ في صفحة الملف الشخصي: " . $e->getMessage());
    $error_message = "حدث خطأ في تحميل البيانات";
}

$success_message = '';
$error_message = '';

// معالجة الرسائل
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'profile_updated':
            $success_message = 'تم تحديث الملف الشخصي بنجاح';
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
    <title>الملف الشخصي - <?php echo SITE_NAME; ?></title>
    
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
            <a class="navbar-brand text-gradient" href="dashboard.php">
                <i class="bi bi-house-door me-2"></i><?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>لوحة التحكم
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">
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

    <div class="container mt-4">
        <!-- عرض الرسائل -->
        <?php if (!empty($success_message)): ?>
            <?php echo displayMessage('success', $success_message); ?>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <?php echo displayMessage('error', $error_message); ?>
        <?php endif; ?>

        <div class="row">
            <!-- معلومات الحساب -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-custom">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person me-2"></i>معلومات الحساب
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($user): ?>
                            <div class="text-center mb-4">
                                <i class="bi bi-person-circle display-1 text-primary"></i>
                                <h4 class="mt-2"><?php echo htmlspecialchars($user['username']); ?></h4>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>اسم المستخدم:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                            </div>
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>البريد الإلكتروني:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>تاريخ التسجيل:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="bi bi-pencil me-2"></i>تعديل المعلومات
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-exclamation-triangle display-4 mb-3"></i>
                                <p>لا يمكن تحميل معلومات الحساب</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- معلومات الاشتراك -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-custom">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check me-2"></i>معلومات الاشتراك
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($subscription_info): ?>
                            <div class="text-center mb-4">
                                <?php if (strtotime($subscription_info['end_date']) > time()): ?>
                                    <i class="bi bi-check-circle-fill display-4 text-success"></i>
                                    <h5 class="text-success mt-2">اشتراك نشط</h5>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill display-4 text-danger"></i>
                                    <h5 class="text-danger mt-2">اشتراك منتهي</h5>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>تاريخ البدء:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo date('Y-m-d H:i', strtotime($subscription_info['start_date'])); ?>
                                </div>
                            </div>
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>تاريخ الانتهاء:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo date('Y-m-d H:i', strtotime($subscription_info['end_date'])); ?>
                                </div>
                            </div>
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>المدة الإجمالية:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo secondsToReadable($subscription_info['duration_seconds']); ?>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-exclamation-triangle display-4 mb-3"></i>
                                <h5>لا يوجد اشتراك</h5>
                                <p>لم يتم العثور على اشتراك نشط لحسابك</p>
                                <button class="btn btn-primary mt-3" onclick="contactSupport()">
                                    <i class="bi bi-telephone me-2"></i>طلب اشتراك
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصائيات الحساب -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-custom">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>إحصائيات الحساب
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="bi bi-calendar-plus display-4 mb-2"></i>
                                    <h6>تاريخ التسجيل</h6>
                                    <p class="mb-0"><?php echo $user ? date('Y-m-d', strtotime($user['created_at'])) : 'غير متاح'; ?></p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                    <i class="bi bi-check-circle display-4 mb-2"></i>
                                    <h6>حالة الحساب</h6>
                                    <p class="mb-0">نشط</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                                    <i class="bi bi-shield-check display-4 mb-2"></i>
                                    <h6>مستوى الأمان</h6>
                                    <p class="mb-0">عالي</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <i class="bi bi-star display-4 mb-2"></i>
                                    <h6>نوع الحساب</h6>
                                    <p class="mb-0">مستخدم عادي</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة تعديل الملف الشخصي -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الملف الشخصي</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_profile.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">اسم المستخدم</label>
                            <input type="text" class="form-control" id="edit_username" name="username" 
                                   value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="edit_email" name="email" 
                                   value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">كلمة المرور الجديدة (اتركها فارغة إذا لم تريد تغييرها)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // دالة التواصل مع الدعم
        function contactSupport() {
            alert('يرجى التواصل مع الدعم الفني لطلب اشتراك جديد.\n\nالبريد الإلكتروني: support@example.com\nالهاتف: +966-XX-XXX-XXXX');
        }
        
        // التحقق من تطابق كلمات المرور
        function checkPasswordMatch() {
            const password = document.getElementById('edit_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmField = document.getElementById('confirm_password');
            
            if (confirmPassword && password !== confirmPassword) {
                confirmField.setCustomValidity('كلمات المرور غير متطابقة');
                confirmField.classList.add('is-invalid');
            } else {
                confirmField.setCustomValidity('');
                confirmField.classList.remove('is-invalid');
            }
        }

        document.getElementById('edit_password').addEventListener('input', checkPasswordMatch);
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        // إزالة رسائل التنبيه تلقائياً
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

