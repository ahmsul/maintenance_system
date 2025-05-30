<?php
session_start();
include('config.php');

// التحقق من أن المستخدم هو أدمن
if ($_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

// إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO employees (name, email, department, type, password) 
            VALUES ('$name', '$email', 'غير محدد', '$type', '$hashed_password')";
    if (mysqli_query($conn, $sql)) {
        echo "تم إضافة المستخدم بنجاح.";
    } else {
        echo "خطأ في إضافة المستخدم: " . mysqli_error($conn);
    }
}

// حذف مستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // لا يمكن حذف الأدمن الحالي
    if ($_SESSION['user_id'] == $user_id) {
        echo "لا يمكن حذف هذا المستخدم.";
    } else {
        $sql = "DELETE FROM employees WHERE employee_id = $user_id";
        if (mysqli_query($conn, $sql)) {
            echo "تم حذف المستخدم بنجاح.";
        } else {
            echo "خطأ في حذف المستخدم: " . mysqli_error($conn);
        }
    }
}

// عرض المستخدمين
$sql = "SELECT * FROM employees";
$result = mysqli_query($conn, $sql);

// عرض طلبات الدعم
$support_sql = "SELECT sr.id, e.name, sr.problem_type, sr.description, sr.status, sr.created_at
                FROM support_requests sr
                JOIN employees e ON sr.user_id = e.employee_id
                ORDER BY sr.status, sr.created_at DESC";
$support_result = mysqli_query($conn, $support_sql);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الأدمن</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>

    <div id="welcomeModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>مرحبًا بك في لوحة التحكم ✨</h2>
            <p>نتمنى لك تجربة مميزة وإدارة سهلة للمستخدمين!</p>
        </div>
    </div>

    <!-- إضافة مستخدم -->
    <h3>إضافة مستخدم جديد</h3>
    <form action="admin_dashboard.php" method="POST">
        <label>الاسم:</label><br>
        <input type="text" name="name" required><br><br>

        <label>البريد الإلكتروني:</label><br>
        <input type="email" name="email" required><br><br>

        <label>كلمة المرور:</label><br>
        <input type="password" name="password" required><br><br>

        <label>نوع المستخدم:</label><br>
        <select name="type">
            <option value="normal">مستخدم عادي</option>
            <option value="maintenance">فني</option>
            <option value="admin">أدمن</option>
        </select><br><br>

        <button type="submit" name="add_user">إضافة مستخدم</button>
    </form>

    <!-- عرض المستخدمين -->
    <h3>المستخدمين</h3>
    <table>
        <tr>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>نوع المستخدم</th>
            
            <th>إجراءات</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['type']; ?></td>
                
                <td>
                    <form action="admin_dashboard.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $row['employee_id']; ?>">
                        <button type="submit" name="delete_user">حذف</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div style="text-align:center; margin-top: 30px;">
        <a href="login_logs.php">عرض سجل الدخول</a> | 
        <a href="logout.php">تسجيل الخروج</a>
    </div>

    <script src="script.js"></script>
</body>
</html>
