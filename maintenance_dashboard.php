<?php
session_start();
include('config.php');

if ($_SESSION['user_type'] != 'maintenance') {
    header('Location: login.php');
    exit();
}

// رسائل النجاح أو الخطأ تخزن في الجلسة ليتم عرضها بعد إعادة التوجيه
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

// تحديث حالة الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $request_id = intval($_POST['request_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // تحقق أن الطلب موجود (اختياري)
    $check_sql = "SELECT id FROM support_requests WHERE id = $request_id LIMIT 1";
    $check_res = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_res) > 0) {
        $sql = "UPDATE support_requests SET status = '$status' WHERE id = $request_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['messages'][] = "تم تحديث حالة الطلب بنجاح.";
        } else {
            $_SESSION['messages'][] = "خطأ في تحديث الحالة: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['messages'][] = "الطلب غير موجود.";
    }

    header("Location: maintenance_dashboard.php");
    exit();
}

// إرسال رسالة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $request_id = intval($_POST['request_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sender_type = 'maintenance';

    // تحقق أن الطلب موجود (اختياري)
    $check_sql = "SELECT id FROM support_requests WHERE id = $request_id LIMIT 1";
    $check_res = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_res) > 0) {
        $sql = "INSERT INTO messages (request_id, sender_type, message) VALUES ($request_id, '$sender_type', '$message')";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['messages'][] = "تم إرسال الرسالة بنجاح.";
        } else {
            $_SESSION['messages'][] = "خطأ في إرسال الرسالة: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['messages'][] = "الطلب غير موجود.";
    }

    header("Location: maintenance_dashboard.php?request_id=$request_id");
    exit();
}

// جلب الطلبات الحالية (مع اسم المستخدم)
$sql = "SELECT sr.id, e.name, sr.problem_type, sr.description, sr.status, sr.created_at
        FROM support_requests sr
        JOIN employees e ON sr.user_id = e.employee_id
        WHERE sr.status = 'تم استلام الطلب' OR sr.status = 'جاري المعالجة'
        ORDER BY sr.status, sr.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الفني</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<div id="welcomeModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>مرحبًا بك في لوحة تحكم الفني ✨</h2>
        <p>نتمنى لك تجربة مميزة وإدارة سهلة للمستخدمين</p>
    </div>
</div>

<!-- عرض رسائل النجاح أو الخطأ -->
<?php
if (!empty($_SESSION['messages'])) {
    foreach ($_SESSION['messages'] as $msg) {
        echo "<p style='color: green; font-weight: bold;'>$msg</p>";
    }
    unset($_SESSION['messages']);
}
?>

<h3>الطلبات الحالية</h3>
<table border="1" style="width: 100%;">
    <tr>
        <th>المستخدم</th>
        <th>نوع المشكلة</th>
        <th>الوصف</th>
        <th>الحالة</th>
        <th>إجراءات</th>
        <th>عرض الرسائل</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['problem_type']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
                <form action="maintenance_dashboard.php" method="POST" style="display:inline-block;">
                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                    <select name="status" required>
                        <option value="تم استلام الطلب" <?php if ($row['status'] == 'تم استلام الطلب') echo 'selected'; ?>>تم استلام الطلب</option>
                        <option value="جاري المعالجة" <?php if ($row['status'] == 'جاري المعالجة') echo 'selected'; ?>>جاري المعالجة</option>
                        <option value="تم حل المشكلة" <?php if ($row['status'] == 'تم حل المشكلة') echo 'selected'; ?>>تم حل المشكلة</option>
                    </select>
                    <button type="submit" name="update_status">تحديث</button>
                </form>
            </td>
            <td>
                <a href="maintenance_dashboard.php?request_id=<?php echo $row['id']; ?>">عرض الرسائل</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php
if (isset($_GET['request_id'])):
    $request_id = intval($_GET['request_id']);

    // جلب اسم المستخدم المرتبط بالطلب
    $user_sql = "SELECT e.name 
                 FROM support_requests sr 
                 JOIN employees e ON sr.user_id = e.employee_id 
                 WHERE sr.id = $request_id 
                 LIMIT 1";
    $user_result = mysqli_query($conn, $user_sql);
    $user_name = "غير معروف";
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $user_name = $user_row['name'];
    }

    // جلب الرسائل
    $messages_sql = "SELECT * FROM messages WHERE request_id = $request_id ORDER BY sent_at ASC";
    $messages_result = mysqli_query($conn, $messages_sql);
?>
<br><hr><br>
<h3>الرسائل الخاصة بالطلب من المستخدم: <?php echo htmlspecialchars($user_name); ?></h3>

<?php if (mysqli_num_rows($messages_result) > 0): ?>
    <?php while ($message_row = mysqli_fetch_assoc($messages_result)): ?>
        <p><strong><?php echo $message_row['sender_type'] == 'normal' ? 'المستخدم:' : 'الفني:'; ?></strong>
            <?php echo htmlspecialchars($message_row['message']); ?>
            <br><small><?php echo $message_row['sent_at']; ?></small></p>
    <?php endwhile; ?>
<?php else: ?>
    <p>لا توجد رسائل بعد لهذا الطلب.</p>
<?php endif; ?>

<!-- نموذج إرسال رسالة جديدة -->
<form action="maintenance_dashboard.php" method="POST">
    <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
    <textarea name="message" required placeholder="اكتب ردك هنا..." style="width:100%; height:100px;"></textarea><br><br>
    <button type="submit" name="send_message">إرسال رسالة</button>
</form>

<?php endif; ?>

<br>
<a href="logout.php">تسجيل الخروج</a>

<script src="script.js"></script>
</body>
</html>
