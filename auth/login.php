<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// إذا كان المستخدم مسجل دخول بالفعل، توجيهه إلى لوحة التحكم
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

$error_message = '';
$success_message = '';

// معالجة رسائل الخطأ من URL
if (isset($_GET['error'])) {
    $error_message = getErrorMessage($_GET['error']);
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'registered') {
        $success_message = 'تم إنشاء الحساب بنجاح، يمكنك الآن تسجيل الدخول';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- الجانب الأيسر - معلومات الموقع -->
            <div class="col-lg-6 d-none d-lg-flex bg-primary text-white align-items-center justify-content-center">
                <div class="text-center">
                    <i class="bi bi-shield-check display-1 mb-4"></i>
                    <h2 class="mb-4">مرحباً بك في <?php echo SITE_NAME; ?></h2>
                    <p class="lead">نظام إدارة الاشتراكات الآمن والموثوق</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i> أمان عالي المستوى</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i> واجهة سهلة الاستخدام</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i> إدارة متقدمة للاشتراكات</li>
                    </ul>
                </div>
            </div>
            
            <!-- الجانب الأيمن - نموذج تسجيل الدخول -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 400px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="bi bi-person-circle display-4 text-primary"></i>
                                <h3 class="mt-3">تسجيل الدخول</h3>
                                <p class="text-muted">أدخل بياناتك للوصول إلى حسابك</p>
                            </div>

                            <!-- عرض الرسائل -->
                            <?php if (!empty($error_message)): ?>
                                <?php echo displayMessage('error', $error_message); ?>
                            <?php endif; ?>

                            <?php if (!empty($success_message)): ?>
                                <?php echo displayMessage('success', $success_message); ?>
                            <?php endif; ?>

                            <form action="login_process.php" method="POST" id="loginForm">
                                <!-- رمز CSRF -->
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person me-1"></i>اسم المستخدم أو البريد الإلكتروني
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="username" name="username" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock me-1"></i>كلمة المرور
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                                    <label class="form-check-label" for="rememberMe">
                                        تذكرني
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل الدخول
                                </button>
                            </form>

                            <div class="text-center">
                                <p class="mb-2">
                                    <a href="#" class="text-decoration-none">نسيت كلمة المرور؟</a>
                                </p>
                                <p>
                                    ليس لديك حساب؟ 
                                    <a href="register.php" class="text-decoration-none fw-bold">إنشاء حساب جديد</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // إظهار/إخفاء كلمة المرور
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        });

        // التحقق من صحة النموذج
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (username === '' || password === '') {
                e.preventDefault();
                alert('يرجى ملء جميع الحقول المطلوبة');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('كلمة المرور يجب أن تكون على الأقل 6 أحرف');
                return false;
            }
        });

        // إزالة رسائل التنبيه تلقائياً بعد 5 ثوان
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

