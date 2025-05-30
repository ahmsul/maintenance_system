<?php
// setup.php

$host = "localhost";  
$user = "root";  
$pass = "";

// الاتصال بالسيرفر
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("فشل الاتصال بالسيرفر: " . $conn->connect_error);
}

// إنشاء قاعدة البيانات
$conn->query("CREATE DATABASE IF NOT EXISTS m_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db("m_system");

// جدول الموظفين
$conn->query("
CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    type ENUM('normal', 'maintenance', 'admin') NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// جدول طلبات الدعم
$conn->query("
CREATE TABLE IF NOT EXISTS support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    problem_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('تم استلام الطلب', 'تم حل المشكلة', 'جاري المعالجة') DEFAULT 'تم استلام الطلب',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// جدول الرسائل مع الربط مع employees
$conn->query("
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    sender_id INT,
    sender_type ENUM('normal', 'maintenance') NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES support_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES employees(employee_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// جدول تسجيل الدخول
$conn->query("
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    success BOOLEAN,
    ip_address VARCHAR(100),
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// إضافة حساب الأدمن
$pwd = password_hash('1234', PASSWORD_DEFAULT);
$sql = "
INSERT IGNORE INTO employees (name, email, department, type, password) 
VALUES ('Admin', 'admin@example.com', 'Admin', 'admin', '$pwd')
";
if (mysqli_query($conn, $sql)) {
    echo "تم إضافة حساب الأدمن بنجاح إذا لم يكن موجودًا.<br>";
} else {
    echo "حدث خطأ أثناء إضافة حساب الأدمن: " . mysqli_error($conn) . "<br>";
}

echo "تم إنشاء القاعدة والجداول وربطها بنجاح.";
$conn->close();
?>
