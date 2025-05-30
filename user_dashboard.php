<?php
session_start();
include('config.php');

if ($_SESSION['user_type'] != 'normal') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";

// جلب رسالة النجاح من الجلسة وعرضها مرة واحدة
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// رفع طلب جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $problem_type = mysqli_real_escape_string($conn, $_POST['problem_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO support_requests (user_id, problem_type, description) 
            VALUES ('$user_id', '$problem_type', '$description')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "تم رفع الطلب بنجاح.";
        header('Location: user_dashboard.php');
        exit();
    } else {
        $success_message = "خطأ في رفع الطلب: " . mysqli_error($conn);
    }
}

// إضافة الرسائل بين المستخدم والفني
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sender_type = 'normal';  // نوع المرسل (مستخدم عادي)
    $request_id = intval($_POST['request_id']); // رقم الطلب

    $sql = "INSERT INTO messages (request_id, sender_type, message) 
            VALUES ('$request_id', '$sender_type', '$message')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "تم إرسال الرسالة بنجاح.";
        header('Location: user_dashboard.php');
        exit();
    } else {
        $success_message = "خطأ في إرسال الرسالة: " . mysqli_error($conn);
    }
}

// جلب الطلبات السابقة للمستخدم
$requests = mysqli_query($conn, "SELECT * FROM support_requests WHERE user_id = $user_id ORDER BY created_at DESC");

// استرجاع الرسائل لطلب معين (اختياري حسب اختيار المستخدم)
$selected_request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$messages = null;
if ($selected_request_id > 0) {
    $messages = mysqli_query($conn, "SELECT * FROM messages WHERE request_id = $selected_request_id ORDER BY sent_at DESC");
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المستخدم العادي</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<h3>رفع طلب دعم</h3>

<?php if (!empty($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>

<form action="user_dashboard.php" method="POST">
    <label>نوع المشكلة:</label><br>
    <input type="text" name="problem_type" required><br><br>

    <label>وصف المشكلة:</label><br>
    <textarea name="description" required></textarea><br><br>

    <button type="submit" name="submit_request">رفع الطلب</button>
</form>

<br><hr><br>

<h3>طلباتي السابقة</h3>
<table border="1" style="width: 100%;">
    <tr>
        <th>رقم الطلب</th>
        <th>نوع المشكلة</th>
        <th>الوصف</th>
        <th>الحالة</th>
        <th>التاريخ</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($requests)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['problem_type']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<br><hr><br>

<h3>إرسال رسالة لفني الدعم</h3>

<form action="user_dashboard.php" method="POST">
    <label>اختر رقم الطلب:</label><br>
    <select name="request_id" required>
        <option value="">-- اختر طلبًا --</option>
        <?php 
        // إعادة جلب الطلبات للDropdown (أو تخزينها في مصفوفة من قبل)
        $requests_dropdown = mysqli_query($conn, "SELECT id, problem_type FROM support_requests WHERE user_id = $user_id ORDER BY created_at DESC");
        while ($req = mysqli_fetch_assoc($requests_dropdown)) {
            $selected = ($req['id'] == $selected_request_id) ? "selected" : "";
            echo "<option value='{$req['id']}' $selected>رقم {$req['id']} - {$req['problem_type']}</option>";
        }
        ?>
    </select><br><br>

    <textarea name="message" placeholder="اكتب رسالتك هنا" required style="width: 100%; height: 100px;"></textarea><br><br>

    <button type="submit" name="send_message">إرسال الرسالة</button>
</form>

<br><hr><br>

<?php if ($selected_request_id > 0): ?>
    <h3>الرسائل الخاصة بالطلب رقم <?php echo $selected_request_id; ?></h3>
    <div class="messages" style="border:1px solid #ccc; padding:10px; max-height: 300px; overflow-y: auto;">
        <?php if ($messages && mysqli_num_rows($messages) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($messages)) { ?>
                <p><strong><?php echo $row['sender_type'] == 'normal' ? 'المستخدم' : 'الفني'; ?>: </strong><?php echo htmlspecialchars($row['message']); ?> <small><?php echo $row['sent_at']; ?></small></p>
            <?php } ?>
        <?php else: ?>
            <p>لا توجد رسائل بعد لهذا الطلب.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p>اختر رقم طلب من القائمة أعلاه لعرض الرسائل الخاصة به.</p>
<?php endif; ?>

<br>
<a href="logout.php">تسجيل الخروج</a>

</body>
</html>
