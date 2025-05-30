<?php
session_start();
include('config.php');

// تأكد من أن المستخدم العادي يزور الصفحة
if ($_SESSION['user_type'] != 'normal' && $_SESSION['user_type'] != 'maintenance') {
    header('Location: login.php');
    exit();
}

// إرسال الرسالة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sender_type = $_SESSION['user_type'];
    $user_id = $_SESSION['user_id'];

    // تحديد نوع المرسل (عادي أو فني) بناءً على نوع المستخدم
    $sql = "INSERT INTO messages (request_id, sender_type, message) 
            VALUES ('$user_id', '$sender_type', '$message')";
    if (mysqli_query($conn, $sql)) {
        echo "تم إرسال الرسالة بنجاح.";
    } else {
        echo "خطأ في إرسال الرسالة: " . mysqli_error($conn);
    }
}

// استرجاع الرسائل
$sql = "SELECT * FROM messages WHERE request_id = '{$_SESSION['user_id']}' ORDER BY sent_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <link rel="stylesheet" href="style2.css">
    <meta charset="UTF-8">
    <title>الدردشة مع الفني</title>
</head>
<body>
    <h3>الدردشة مع الفني</h3>

    <form action="chat.php" method="POST">
        <textarea name="message" placeholder="اكتب رسالتك هنا" required></textarea><br>
        <button type="submit" name="send_message">إرسال</button>
    </form>

    <!-- عرض الرسائل في الشات -->
    <div class="messages">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <p><strong><?php echo $row['sender_type'] == 'normal' ? 'المستخدم' : 'الفني'; ?>: </strong><?php echo $row['message']; ?> <small><?php echo $row['sent_at']; ?></small></p>
        <?php } ?>
    </div>

    <a href="user_dashboard.php">العودة إلى لوحة تحكم المستخدم</a>
</body>
</html>
