<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// تسجيل الخروج
logoutUser();

// توجيه المستخدم إلى صفحة تسجيل الدخول
redirect('../auth/login.php?success=logged_out');
?>

