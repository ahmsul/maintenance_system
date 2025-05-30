<?php
session_start();
include('config.php');

// تحقق من وجود جلسة
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$request_id = intval($_POST['request_id']);
$sender_type = $_SESSION['user_type'];
$message = mysqli_real_escape_string($conn, $_POST['message']);

// إدخال الرسالة
$sql = "INSERT INTO messages (request_id, sender_type, message) 
        VALUES ('$request_id', '$sender_type', '$message')";

if (mysqli_query($conn, $sql)) {
    header("Location: chat.php?id=$request_id");
    exit();
} else {
    echo "خطأ أثناء الإرسال: " . mysqli_error($conn);
}
