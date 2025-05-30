<?php
error_reporting(E_ALL);  // تفعيل عرض جميع الأخطاء
ini_set('display_errors', 1);  // عرض الأخطاء في المتصفح

// الاتصال بقاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'm_system'); // تأكد من تغيير 'your_database' إلى اسم قاعدة بياناتك الفعلي.

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}
?>
