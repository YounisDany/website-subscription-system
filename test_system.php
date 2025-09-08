<?php
/**
 * ملف اختبار شامل لنظام إدارة الاشتراكات
 * يقوم بفحص جميع المكونات والوظائف الأساسية
 */

// التحقق من وجود الملفات المطلوبة
$requiredFiles = [
    'includes/config.php',
    'includes/database.php',
    'includes/functions.php',
    'auth/login.php',
    'auth/register.php',
    'admin/dashboard.php',
    'user/dashboard.php',
    'css/style.css',
    'js/main.js'
];

$tests = [];
$passed = 0;
$failed = 0;

// اختبار وجود الملفات
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        $tests[] = ['test' => "وجود ملف $file", 'status' => 'pass', 'message' => 'الملف موجود'];
        $passed++;
    } else {
        $tests[] = ['test' => "وجود ملف $file", 'status' => 'fail', 'message' => 'الملف غير موجود'];
        $failed++;
    }
}

// اختبار إعدادات PHP
$phpTests = [
    'PDO' => extension_loaded('pdo'),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'Sessions' => function_exists('session_start'),
    'JSON' => function_exists('json_encode'),
    'Password Hash' => function_exists('password_hash')
];

foreach ($phpTests as $test => $result) {
    if ($result) {
        $tests[] = ['test' => "دعم $test", 'status' => 'pass', 'message' => 'مدعوم'];
        $passed++;
    } else {
        $tests[] = ['test' => "دعم $test", 'status' => 'fail', 'message' => 'غير مدعوم'];
        $failed++;
    }
}

// اختبار الاتصال بقاعدة البيانات
if (file_exists('includes/config.php')) {
    try {
        require_once 'includes/config.php';
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $tests[] = ['test' => 'الاتصال بقاعدة البيانات', 'status' => 'pass', 'message' => 'نجح الاتصال'];
        $passed++;
        
        // اختبار وجود الجداول
        $tables = ['users', 'subscriptions', 'activity_logs'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $tests[] = ['test' => "وجود جدول $table", 'status' => 'pass', 'message' => 'الجدول موجود'];
                $passed++;
            } else {
                $tests[] = ['test' => "وجود جدول $table", 'status' => 'fail', 'message' => 'الجدول غير موجود'];
                $failed++;
            }
        }
        
        // اختبار وجود المستخدم الافتراضي
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount > 0) {
            $tests[] = ['test' => 'وجود حساب إدارة', 'status' => 'pass', 'message' => "يوجد $adminCount حساب إدارة"];
            $passed++;
        } else {
            $tests[] = ['test' => 'وجود حساب إدارة', 'status' => 'fail', 'message' => 'لا يوجد حساب إدارة'];
            $failed++;
        }
        
    } catch (Exception $e) {
        $tests[] = ['test' => 'الاتصال بقاعدة البيانات', 'status' => 'fail', 'message' => $e->getMessage()];
        $failed++;
    }
} else {
    $tests[] = ['test' => 'ملف الإعدادات', 'status' => 'fail', 'message' => 'ملف config.php غير موجود'];
    $failed++;
}

// اختبار صلاحيات الملفات
$writableDirectories = ['includes'];
foreach ($writableDirectories as $dir) {
    if (is_writable($dir)) {
        $tests[] = ['test' => "صلاحيات الكتابة في $dir", 'status' => 'pass', 'message' => 'قابل للكتابة'];
        $passed++;
    } else {
        $tests[] = ['test' => "صلاحيات الكتابة في $dir", 'status' => 'fail', 'message' => 'غير قابل للكتابة'];
        $failed++;
    }
}

// حساب النسبة المئوية للنجاح
$total = $passed + $failed;
$successRate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار النظام - نظام إدارة الاشتراكات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .test-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .test-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .test-pass {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        .test-fail {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-container">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3><i class="bi bi-clipboard-check me-2"></i>تقرير اختبار النظام</h3>
                </div>
                
                <div class="card-body">
                    <!-- ملخص النتائج -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-check-circle display-4"></i>
                                    <h4><?php echo $passed; ?></h4>
                                    <p class="mb-0">اختبار نجح</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-x-circle display-4"></i>
                                    <h4><?php echo $failed; ?></h4>
                                    <p class="mb-0">اختبار فشل</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-percent display-4"></i>
                                    <h4><?php echo $successRate; ?>%</h4>
                                    <p class="mb-0">معدل النجاح</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- شريط التقدم -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>التقدم الإجمالي</span>
                            <span><?php echo $passed; ?>/<?php echo $total; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?php echo $successRate; ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- تفاصيل الاختبارات -->
                    <h5><i class="bi bi-list-check me-2"></i>تفاصيل الاختبارات</h5>
                    
                    <?php foreach ($tests as $test): ?>
                        <div class="test-item test-<?php echo $test['status']; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-<?php echo $test['status'] === 'pass' ? 'check-circle text-success' : 'x-circle text-danger'; ?> me-2"></i>
                                    <strong><?php echo $test['test']; ?></strong>
                                </div>
                                <span class="badge bg-<?php echo $test['status'] === 'pass' ? 'success' : 'danger'; ?>">
                                    <?php echo $test['message']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- التوصيات -->
                    <div class="mt-4">
                        <h5><i class="bi bi-lightbulb me-2"></i>التوصيات</h5>
                        
                        <?php if ($successRate >= 90): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>ممتاز!</strong> النظام جاهز للاستخدام. جميع الاختبارات الأساسية نجحت.
                            </div>
                        <?php elseif ($successRate >= 70): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>جيد!</strong> النظام يعمل بشكل أساسي، لكن هناك بعض المشاكل التي يجب حلها.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle me-2"></i>
                                <strong>تحذير!</strong> هناك مشاكل كبيرة في النظام يجب حلها قبل الاستخدام.
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($failed > 0): ?>
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle me-2"></i>خطوات الإصلاح:</h6>
                                <ul class="mb-0">
                                    <?php if (in_array('includes/config.php', array_column($tests, 'test'))): ?>
                                        <li>تأكد من تشغيل ملف setup.php لإعداد النظام</li>
                                    <?php endif; ?>
                                    <li>تحقق من إعدادات قاعدة البيانات</li>
                                    <li>تأكد من وجود جميع الملفات المطلوبة</li>
                                    <li>تحقق من صلاحيات الملفات والمجلدات</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- أزرار التنقل -->
                    <div class="text-center mt-4">
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>الصفحة الرئيسية
                            </a>
                            
                            <?php if (!file_exists('includes/installed.lock')): ?>
                                <a href="setup.php" class="btn btn-success">
                                    <i class="bi bi-gear me-2"></i>إعداد النظام
                                </a>
                            <?php endif; ?>
                            
                            <button onclick="location.reload()" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-2"></i>إعادة الاختبار
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="bi bi-clock me-1"></i>
                        تم إجراء الاختبار في: <?php echo date('Y-m-d H:i:s'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تأثير تحميل النتائج
        document.addEventListener('DOMContentLoaded', function() {
            const testItems = document.querySelectorAll('.test-item');
            testItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    item.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    }, 50);
                }, index * 100);
            });
        });
    </script>
</body>
</html>

