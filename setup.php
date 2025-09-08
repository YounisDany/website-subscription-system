<?php
/**
 * ملف الإعداد التلقائي لنظام إدارة الاشتراكات
 * يقوم بإنشاء قاعدة البيانات والجداول تلقائياً
 */

// منع الوصول المباشر إذا كان النظام مثبت بالفعل
if (file_exists('includes/installed.lock')) {
    die('النظام مثبت بالفعل. احذف ملف includes/installed.lock لإعادة التثبيت.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// معالجة خطوات التثبيت
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            $result = testDatabaseConnection($_POST);
            if ($result['success']) {
                $success = 'تم الاتصال بقاعدة البيانات بنجاح!';
                // حفظ إعدادات قاعدة البيانات
                saveConfig($_POST);
                $step = 3;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 3:
            $result = createDatabase();
            if ($result['success']) {
                $success = 'تم إنشاء قاعدة البيانات والجداول بنجاح!';
                $step = 4;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 4:
            $result = createAdminUser($_POST);
            if ($result['success']) {
                $success = 'تم إنشاء حساب الإدارة بنجاح!';
                // إنشاء ملف القفل
                file_put_contents('includes/installed.lock', date('Y-m-d H:i:s'));
                $step = 5;
            } else {
                $error = $result['error'];
            }
            break;
    }
}

// دالة اختبار الاتصال بقاعدة البيانات
function testDatabaseConnection($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'خطأ في الاتصال: ' . $e->getMessage()];
    }
}

// دالة حفظ إعدادات قاعدة البيانات
function saveConfig($config) {
    $configContent = "<?php
// إعدادات قاعدة البيانات
define('DB_HOST', '{$config['db_host']}');
define('DB_NAME', '{$config['db_name']}');
define('DB_USER', '{$config['db_user']}');
define('DB_PASS', '{$config['db_pass']}');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // مهلة الجلسة بالثواني (ساعة واحدة)

// إعدادات الأمان
define('HASH_ALGO', PASSWORD_DEFAULT);
define('CSRF_TOKEN_NAME', 'csrf_token');

// إعدادات الموقع
define('SITE_NAME', '{$config['site_name']}');
define('SITE_URL', '{$config['site_url']}');

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تعيين المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');

// دالة لتوليد رمز CSRF
function generateCSRFToken() {
    if (!isset(\$_SESSION[CSRF_TOKEN_NAME])) {
        \$_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return \$_SESSION[CSRF_TOKEN_NAME];
}

// دالة للتحقق من رمز CSRF
function verifyCSRFToken(\$token) {
    return isset(\$_SESSION[CSRF_TOKEN_NAME]) && hash_equals(\$_SESSION[CSRF_TOKEN_NAME], \$token);
}

// دالة لتنظيف البيانات المدخلة
function sanitizeInput(\$data) {
    \$data = trim(\$data);
    \$data = stripslashes(\$data);
    \$data = htmlspecialchars(\$data);
    return \$data;
}

// دالة للتحقق من صحة البريد الإلكتروني
function validateEmail(\$email) {
    return filter_var(\$email, FILTER_VALIDATE_EMAIL);
}

// دالة للتحقق من قوة كلمة المرور
function validatePassword(\$password) {
    // كلمة المرور يجب أن تكون على الأقل 8 أحرف
    return strlen(\$password) >= 8;
}
?>";

    if (!is_dir('includes')) {
        mkdir('includes', 0755, true);
    }
    
    file_put_contents('includes/config.php', $configContent);
}

// دالة إنشاء قاعدة البيانات والجداول
function createDatabase() {
    try {
        require_once 'includes/config.php';
        
        // الاتصال بـ MySQL
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // إنشاء قاعدة البيانات
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        
        // قراءة وتنفيذ مخطط قاعدة البيانات
        $sql = file_get_contents('database_schema.sql');
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'خطأ في إنشاء قاعدة البيانات: ' . $e->getMessage()];
    }
}

// دالة إنشاء حساب الإدارة
function createAdminUser($data) {
    try {
        require_once 'includes/config.php';
        
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // حذف المستخدم الافتراضي إذا كان موجوداً
        $pdo->exec("DELETE FROM users WHERE username = 'admin'");
        
        // إنشاء حساب الإدارة الجديد
        $hashedPassword = password_hash($data['admin_password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
        $stmt->execute([$data['admin_username'], $data['admin_email'], $hashedPassword]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'خطأ في إنشاء حساب الإدارة: ' . $e->getMessage()];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد نظام إدارة الاشتراكات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .setup-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3><i class="bi bi-gear me-2"></i>إعداد نظام إدارة الاشتراكات</h3>
                </div>
                
                <div class="card-body">
                    <!-- شريط التقدم -->
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" style="width: <?php echo ($step / 5) * 100; ?>%"></div>
                    </div>
                    
                    <!-- عرض الرسائل -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($step == 1): ?>
                        <!-- الخطوة 1: مرحباً -->
                        <div class="text-center">
                            <i class="bi bi-house-door display-1 text-primary mb-4"></i>
                            <h4>مرحباً بك في معالج الإعداد</h4>
                            <p class="text-muted mb-4">
                                سيقوم هذا المعالج بإرشادك خلال عملية إعداد نظام إدارة الاشتراكات.
                                تأكد من أن لديك معلومات قاعدة البيانات جاهزة.
                            </p>
                            
                            <div class="alert alert-info text-start">
                                <h6><i class="bi bi-info-circle me-2"></i>المتطلبات:</h6>
                                <ul class="mb-0">
                                    <li>PHP 7.4 أو أحدث</li>
                                    <li>MySQL 5.7 أو أحدث</li>
                                    <li>امتداد PDO مفعل</li>
                                    <li>صلاحيات كتابة في مجلد includes</li>
                                </ul>
                            </div>
                            
                            <a href="?step=2" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-right me-2"></i>ابدأ الإعداد
                            </a>
                        </div>
                        
                    <?php elseif ($step == 2): ?>
                        <!-- الخطوة 2: إعدادات قاعدة البيانات -->
                        <h4><i class="bi bi-database me-2"></i>إعدادات قاعدة البيانات</h4>
                        <p class="text-muted mb-4">أدخل معلومات الاتصال بقاعدة البيانات</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">خادم قاعدة البيانات</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">اسم قاعدة البيانات</label>
                                <input type="text" class="form-control" name="db_name" value="subscription_system" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="db_user" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="db_pass">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">اسم الموقع</label>
                                <input type="text" class="form-control" name="site_name" value="نظام إدارة الاشتراكات" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">رابط الموقع</label>
                                <input type="url" class="form-control" name="site_url" value="http://localhost" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-2"></i>اختبار الاتصال
                            </button>
                        </form>
                        
                    <?php elseif ($step == 3): ?>
                        <!-- الخطوة 3: إنشاء قاعدة البيانات -->
                        <div class="text-center">
                            <i class="bi bi-database-add display-1 text-success mb-4"></i>
                            <h4>إنشاء قاعدة البيانات</h4>
                            <p class="text-muted mb-4">
                                سيتم الآن إنشاء قاعدة البيانات والجداول المطلوبة
                            </p>
                            
                            <form method="POST">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-play me-2"></i>إنشاء قاعدة البيانات
                                </button>
                            </form>
                        </div>
                        
                    <?php elseif ($step == 4): ?>
                        <!-- الخطوة 4: إنشاء حساب الإدارة -->
                        <h4><i class="bi bi-person-badge me-2"></i>إنشاء حساب الإدارة</h4>
                        <p class="text-muted mb-4">أنشئ حساب المدير الرئيسي للنظام</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="admin_username" value="admin" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="admin_email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="admin_password" required>
                                <div class="form-text">يجب أن تكون على الأقل 8 أحرف</div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-plus me-2"></i>إنشاء الحساب
                            </button>
                        </form>
                        
                    <?php elseif ($step == 5): ?>
                        <!-- الخطوة 5: اكتمال التثبيت -->
                        <div class="text-center">
                            <i class="bi bi-check-circle display-1 text-success mb-4"></i>
                            <h4 class="text-success">تم التثبيت بنجاح!</h4>
                            <p class="text-muted mb-4">
                                تم إعداد نظام إدارة الاشتراكات بنجاح. يمكنك الآن البدء في استخدام النظام.
                            </p>
                            
                            <div class="alert alert-warning text-start">
                                <h6><i class="bi bi-exclamation-triangle me-2"></i>تنبيه أمني:</h6>
                                <p class="mb-0">
                                    لأسباب أمنية، يرجى حذف ملف <code>setup.php</code> من الخادم بعد اكتمال التثبيت.
                                </p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-house me-2"></i>الذهاب للصفحة الرئيسية
                                </a>
                                <a href="auth/login.php" class="btn btn-outline-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل الدخول
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer text-center text-muted">
                    <small>الخطوة <?php echo $step; ?> من 5</small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

