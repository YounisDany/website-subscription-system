<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// إذا كان المستخدم مسجل دخول بالفعل، توجيهه إلى لوحة التحكم
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

$error_message = '';

// معالجة رسائل الخطأ من URL
if (isset($_GET['error'])) {
    $error_message = getErrorMessage($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد - <?php echo SITE_NAME; ?></title>
    
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
            <div class="col-lg-6 d-none d-lg-flex bg-success text-white align-items-center justify-content-center">
                <div class="text-center">
                    <i class="bi bi-person-plus display-1 mb-4"></i>
                    <h2 class="mb-4">انضم إلى <?php echo SITE_NAME; ?></h2>
                    <p class="lead">ابدأ رحلتك معنا اليوم</p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i> تسجيل سريع وآمن</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i> وصول فوري للخدمات</li>
                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i> دعم فني متميز</li>
                    </ul>
                </div>
            </div>
            
            <!-- الجانب الأيمن - نموذج إنشاء الحساب -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="bi bi-person-plus-fill display-4 text-success"></i>
                                <h3 class="mt-3">إنشاء حساب جديد</h3>
                                <p class="text-muted">املأ البيانات التالية لإنشاء حسابك</p>
                            </div>

                            <!-- عرض الرسائل -->
                            <?php if (!empty($error_message)): ?>
                                <?php echo displayMessage('error', $error_message); ?>
                            <?php endif; ?>

                            <form action="register_process.php" method="POST" id="registerForm">
                                <!-- رمز CSRF -->
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person me-1"></i>اسم المستخدم
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="username" name="username" required>
                                    <div class="form-text">يجب أن يكون فريداً ولا يحتوي على مسافات</div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-1"></i>البريد الإلكتروني
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
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
                                    <div class="form-text">يجب أن تكون على الأقل 8 أحرف</div>
                                    
                                    <!-- مؤشر قوة كلمة المرور -->
                                    <div class="mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small id="passwordStrengthText" class="text-muted"></small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="bi bi-lock-fill me-1"></i>تأكيد كلمة المرور
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="agreeTerms" name="agree_terms" required>
                                    <label class="form-check-label" for="agreeTerms">
                                        أوافق على <a href="#" class="text-decoration-none">الشروط والأحكام</a>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="bi bi-person-plus me-2"></i>إنشاء الحساب
                                </button>
                            </form>

                            <div class="text-center">
                                <p>
                                    لديك حساب بالفعل؟ 
                                    <a href="login.php" class="text-decoration-none fw-bold">تسجيل الدخول</a>
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

        // فحص قوة كلمة المرور
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');
            
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length >= 8) strength += 25;
            if (/[a-z]/.test(password)) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            
            if (strength <= 25) {
                text = 'ضعيفة';
                color = 'bg-danger';
            } else if (strength <= 50) {
                text = 'متوسطة';
                color = 'bg-warning';
            } else if (strength <= 75) {
                text = 'جيدة';
                color = 'bg-info';
            } else {
                text = 'قوية';
                color = 'bg-success';
            }
            
            strengthBar.style.width = strength + '%';
            strengthBar.className = 'progress-bar ' + color;
            strengthText.textContent = text;
        });

        // التحقق من تطابق كلمات المرور
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
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

        document.getElementById('password').addEventListener('input', checkPasswordMatch);
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

        // التحقق من صحة النموذج
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const agreeTerms = document.getElementById('agreeTerms').checked;

            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('يرجى ملء جميع الحقول المطلوبة');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('كلمة المرور يجب أن تكون على الأقل 8 أحرف');
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('كلمات المرور غير متطابقة');
                return false;
            }

            if (!agreeTerms) {
                e.preventDefault();
                alert('يجب الموافقة على الشروط والأحكام');
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

