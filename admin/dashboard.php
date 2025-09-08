<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول وصلاحيات الإدارة
requireLogin(true);

// الحصول على إحصائيات النظام
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $userManager = new UserManager($db);
    $subscriptionManager = new SubscriptionManager($db);
    
    // إحصائيات عامة
    $total_users_query = "SELECT COUNT(*) as count FROM users WHERE is_admin = 0";
    $total_users_stmt = $db->prepare($total_users_query);
    $total_users_stmt->execute();
    $total_users = $total_users_stmt->fetch()['count'];
    
    $active_subscriptions_query = "SELECT COUNT(*) as count FROM subscriptions WHERE is_active = 1 AND end_date > NOW()";
    $active_subscriptions_stmt = $db->prepare($active_subscriptions_query);
    $active_subscriptions_stmt->execute();
    $active_subscriptions = $active_subscriptions_stmt->fetch()['count'];
    
    $expired_subscriptions_query = "SELECT COUNT(*) as count FROM subscriptions WHERE is_active = 1 AND end_date <= NOW()";
    $expired_subscriptions_stmt = $db->prepare($expired_subscriptions_query);
    $expired_subscriptions_stmt->execute();
    $expired_subscriptions = $expired_subscriptions_stmt->fetch()['count'];
    
    // الحصول على قائمة المستخدمين
    $users = $userManager->getAllUsers();
    
} catch (Exception $e) {
    error_log("خطأ في لوحة تحكم الإدارة: " . $e->getMessage());
    $error_message = "حدث خطأ في تحميل البيانات";
}

$success_message = '';
$error_message = '';

// معالجة الرسائل
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'user_updated':
            $success_message = 'تم تحديث بيانات المستخدم بنجاح';
            break;
        case 'user_deleted':
            $success_message = 'تم حذف المستخدم بنجاح';
            break;
        case 'subscription_updated':
            $success_message = 'تم تحديث الاشتراك بنجاح';
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
    <title>لوحة تحكم الإدارة - <?php echo SITE_NAME; ?></title>
    
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
                <i class="bi bi-shield-check me-2"></i><?php echo SITE_NAME; ?>
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
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-person-plus me-1"></i>إضافة مستخدم
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../user/dashboard.php">
                                <i class="bi bi-house me-2"></i>الصفحة الرئيسية
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
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

        <!-- بطاقات الإحصائيات -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="bi bi-people display-4 mb-3"></i>
                    <h3 class="display-6"><?php echo $total_users ?? 0; ?></h3>
                    <p class="mb-0">إجمالي المستخدمين</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <i class="bi bi-check-circle display-4 mb-3"></i>
                    <h3 class="display-6"><?php echo $active_subscriptions ?? 0; ?></h3>
                    <p class="mb-0">الاشتراكات النشطة</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <i class="bi bi-x-circle display-4 mb-3"></i>
                    <h3 class="display-6"><?php echo $expired_subscriptions ?? 0; ?></h3>
                    <p class="mb-0">الاشتراكات المنتهية</p>
                </div>
            </div>
        </div>

        <!-- جدول المستخدمين -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-custom">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>إدارة المستخدمين
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>اسم المستخدم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>حالة الاشتراك</th>
                                        <th>تاريخ الانتهاء</th>
                                        <th>تاريخ التسجيل</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $user): ?>
                                            <?php if ($user['is_admin'] == 0): // عرض المستخدمين العاديين فقط ?>
                                                <tr>
                                                    <td><?php echo $user['id']; ?></td>
                                                    <td>
                                                        <i class="bi bi-person me-1"></i>
                                                        <?php echo htmlspecialchars($user['username']); ?>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-envelope me-1"></i>
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['subscription_active'] && $user['end_date'] && strtotime($user['end_date']) > time()): ?>
                                                            <span class="badge bg-success">نشط</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">منتهي</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['end_date']): ?>
                                                            <?php echo date('Y-m-d H:i', strtotime($user['end_date'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">لا يوجد</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>')">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                    onclick="manageSubscription(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                                <i class="bi bi-calendar-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">لا توجد بيانات</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة إضافة مستخدم -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مستخدم جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_user.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="new_username" class="form-label">اسم المستخدم</label>
                            <input type="text" class="form-control" id="new_username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="new_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" id="new_password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subscription_duration" class="form-label">مدة الاشتراك (بالثواني)</label>
                            <input type="number" class="form-control" id="subscription_duration" name="subscription_duration" value="3600" min="60">
                            <div class="form-text">3600 ثانية = ساعة واحدة</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة المستخدم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- نافذة تعديل المستخدم -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل بيانات المستخدم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit_user.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">اسم المستخدم</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">كلمة المرور الجديدة (اتركها فارغة إذا لم تريد تغييرها)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
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

    <!-- نافذة إدارة الاشتراك -->
    <div class="modal fade" id="subscriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إدارة الاشتراك</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage_subscription.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" id="sub_user_id" name="user_id">
                        
                        <div class="mb-3">
                            <label class="form-label">المستخدم</label>
                            <input type="text" class="form-control" id="sub_username" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sub_duration" class="form-label">مدة الاشتراك (بالثواني)</label>
                            <input type="number" class="form-control" id="sub_duration" name="duration_seconds" value="3600" min="60" required>
                            <div class="form-text">
                                <small>
                                    دقيقة واحدة = 60 ثانية<br>
                                    ساعة واحدة = 3600 ثانية<br>
                                    يوم واحد = 86400 ثانية
                                </small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <label for="sub_minutes" class="form-label">الدقائق</label>
                                <input type="number" class="form-control" id="sub_minutes" min="0" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="sub_hours" class="form-label">الساعات</label>
                                <input type="number" class="form-control" id="sub_hours" min="0" value="1">
                            </div>
                            <div class="col-md-4">
                                <label for="sub_days" class="form-label">الأيام</label>
                                <input type="number" class="form-control" id="sub_days" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success">تحديث الاشتراك</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // دالة تعديل المستخدم
        function editUser(id, username, email) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_password').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        }

        // دالة إدارة الاشتراك
        function manageSubscription(id, username) {
            document.getElementById('sub_user_id').value = id;
            document.getElementById('sub_username').value = username;
            
            const modal = new bootstrap.Modal(document.getElementById('subscriptionModal'));
            modal.show();
        }

        // دالة حذف المستخدم
        function deleteUser(id, username) {
            if (confirm('هل أنت متأكد من حذف المستخدم "' + username + '"؟\nهذا الإجراء لا يمكن التراجع عنه.')) {
                window.location.href = 'delete_user.php?id=' + id + '&csrf_token=<?php echo generateCSRFToken(); ?>';
            }
        }

        // حساب المدة بالثواني من الدقائق والساعات والأيام
        function calculateDuration() {
            const minutes = parseInt(document.getElementById('sub_minutes').value) || 0;
            const hours = parseInt(document.getElementById('sub_hours').value) || 0;
            const days = parseInt(document.getElementById('sub_days').value) || 0;
            
            const totalSeconds = (days * 24 * 60 * 60) + (hours * 60 * 60) + (minutes * 60);
            document.getElementById('sub_duration').value = totalSeconds;
        }

        // ربط الأحداث
        document.getElementById('sub_minutes').addEventListener('input', calculateDuration);
        document.getElementById('sub_hours').addEventListener('input', calculateDuration);
        document.getElementById('sub_days').addEventListener('input', calculateDuration);

        // تحديث الحقول عند تغيير المدة بالثواني
        document.getElementById('sub_duration').addEventListener('input', function() {
            const totalSeconds = parseInt(this.value) || 0;
            
            const days = Math.floor(totalSeconds / (24 * 60 * 60));
            const hours = Math.floor((totalSeconds % (24 * 60 * 60)) / (60 * 60));
            const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
            
            document.getElementById('sub_days').value = days;
            document.getElementById('sub_hours').value = hours;
            document.getElementById('sub_minutes').value = minutes;
        });

        // إزالة رسائل التنبيه تلقائياً
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

