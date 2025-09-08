<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// إذا كان المستخدم مسجل دخول، توجيهه إلى لوحة التحكم المناسبة
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - نظام إدارة الاشتراكات</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand text-gradient" href="#">
                <i class="bi bi-shield-check me-2"></i><?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">الميزات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">حولنا</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">اتصل بنا</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="auth/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>تسجيل الدخول
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/register.php">
                            <i class="bi bi-person-plus me-1"></i>إنشاء حساب
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- القسم الرئيسي -->
    <section id="home" class="py-5" style="margin-top: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">مرحباً بك في <?php echo SITE_NAME; ?></h1>
                    <p class="lead mb-4">
                        نظام إدارة الاشتراكات الآمن والموثوق الذي يوفر لك تحكماً كاملاً في إدارة المستخدمين والاشتراكات 
                        مع إمكانية تحديد مدة الاشتراك بدقة عالية تصل إلى الثانية الواحدة.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="auth/register.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus me-2"></i>ابدأ الآن
                        </a>
                        <a href="auth/login.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل الدخول
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-shield-check display-1 mb-4" style="font-size: 10rem;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم الميزات -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-gradient">الميزات الرئيسية</h2>
                <p class="lead text-muted">اكتشف ما يجعل نظامنا الخيار الأمثل لإدارة الاشتراكات</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-custom">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-clock display-4 text-primary mb-3"></i>
                            <h5>تحكم دقيق في الوقت</h5>
                            <p class="text-muted">
                                إمكانية تحديد مدة الاشتراك بالثواني والدقائق والساعات والأيام 
                                مع عداد تنازلي مباشر للوقت المتبقي.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-custom">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-shield-lock display-4 text-success mb-3"></i>
                            <h5>أمان عالي المستوى</h5>
                            <p class="text-muted">
                                حماية متقدمة للبيانات مع تشفير كلمات المرور وحماية من هجمات CSRF 
                                وتسجيل شامل لجميع الأنشطة.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-custom">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-speedometer2 display-4 text-info mb-3"></i>
                            <h5>واجهة سهلة الاستخدام</h5>
                            <p class="text-muted">
                                تصميم عصري ومتجاوب مع جميع الأجهزة، واجهة بديهية 
                                تجعل إدارة الاشتراكات أمراً سهلاً وممتعاً.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-custom">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-people display-4 text-warning mb-3"></i>
                            <h5>إدارة شاملة للمستخدمين</h5>
                            <p class="text-muted">
                                لوحة تحكم متقدمة للإدارة تتيح إضافة وتعديل وحذف المستخدمين 
                                مع إمكانية إدارة اشتراكاتهم بسهولة.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-custom">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-graph-up display-4 text-danger mb-3"></i>
                            <h5>تقارير وإحصائيات</h5>
                            <p class="text-muted">
                                عرض إحصائيات شاملة عن المستخدمين والاشتراكات النشطة والمنتهية 
                                مع إمكانية تتبع النشاط.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-custom">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-phone display-4 text-secondary mb-3"></i>
                            <h5>متوافق مع جميع الأجهزة</h5>
                            <p class="text-muted">
                                تصميم متجاوب يعمل بشكل مثالي على الهواتف الذكية والأجهزة اللوحية 
                                وأجهزة الكمبيوتر المكتبية.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم حولنا -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold text-gradient mb-4">حول النظام</h2>
                    <p class="lead mb-4">
                        تم تطوير هذا النظام باستخدام أحدث التقنيات والمعايير في تطوير الويب لضمان 
                        الأداء الأمثل والأمان العالي.
                    </p>
                    
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">PHP & MySQL</h6>
                                    <small class="text-muted">تقنيات خادم موثوقة</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">Bootstrap 5</h6>
                                    <small class="text-muted">تصميم عصري ومتجاوب</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">JavaScript</h6>
                                    <small class="text-muted">تفاعل ديناميكي</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">أمان متقدم</h6>
                                    <small class="text-muted">حماية شاملة للبيانات</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 text-center">
                    <div class="stats-card glass-effect">
                        <i class="bi bi-code-slash display-1 mb-4"></i>
                        <h3>نظام متكامل</h3>
                        <p class="mb-0">مطور بعناية فائقة لضمان الجودة والأداء</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم اتصل بنا -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-gradient">اتصل بنا</h2>
                <p class="lead text-muted">نحن هنا لمساعدتك في أي وقت</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-custom h-100">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-envelope display-4 text-primary mb-3"></i>
                                    <h5>البريد الإلكتروني</h5>
                                    <p class="text-muted mb-0">support@example.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-custom h-100">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-telephone display-4 text-success mb-3"></i>
                                    <h5>الهاتف</h5>
                                    <p class="text-muted mb-0">+966-XX-XXX-XXXX</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-custom h-100">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-clock display-4 text-info mb-3"></i>
                                    <h5>ساعات العمل</h5>
                                    <p class="text-muted mb-0">24/7 دعم متواصل</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- التذييل -->
    <footer class="footer bg-dark text-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-gradient"><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">
                        نظام إدارة الاشتراكات الأكثر تطوراً وأماناً في المنطقة. 
                        نوفر لك الأدوات اللازمة لإدارة اشتراكاتك بكفاءة عالية.
                    </p>
                </div>
                
                <div class="col-lg-3">
                    <h6>روابط سريعة</h6>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-muted text-decoration-none">الرئيسية</a></li>
                        <li><a href="#features" class="text-muted text-decoration-none">الميزات</a></li>
                        <li><a href="#about" class="text-muted text-decoration-none">حولنا</a></li>
                        <li><a href="#contact" class="text-muted text-decoration-none">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3">
                    <h6>الحساب</h6>
                    <ul class="list-unstyled">
                        <li><a href="auth/login.php" class="text-muted text-decoration-none">تسجيل الدخول</a></li>
                        <li><a href="auth/register.php" class="text-muted text-decoration-none">إنشاء حساب</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        تم التطوير بـ <i class="bi bi-heart-fill text-danger"></i> باستخدام PHP & Bootstrap
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // تأثير التمرير السلس
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // تأثير شريط التنقل عند التمرير
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-lg');
            } else {
                navbar.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html>

