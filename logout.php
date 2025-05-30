<?php
session_start();  // بدء الجلسة

// تدمير الجلسة لتسجيل الخروج
session_unset();
session_destroy();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header('Location: login.php');  
exit();
?>
