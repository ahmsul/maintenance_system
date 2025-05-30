<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // أولاً: نحصل على بيانات المستخدم بالبريد
    $sql_user = "SELECT * FROM employees WHERE email = '$email' LIMIT 1";
    $result_user = mysqli_query($conn, $sql_user);

    if (!$result_user) {
        die("خطأ في استعلام المستخدم: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result_user) == 1) {
        $user = mysqli_fetch_assoc($result_user);
        $employee_id = $user['employee_id'];

        // ثانياً: التحقق من عدد المحاولات الفاشلة خلال آخر دقيقة
        $check_attempts = "SELECT COUNT(*) AS failed_attempts 
                           FROM login_logs 
                           WHERE employee_id = '$employee_id' AND success = 0 
                           AND login_time >= (NOW() - INTERVAL 1 MINUTE)";
        $result_attempts = mysqli_query($conn, $check_attempts);

        if ($result_attempts) {
            $row_attempts = mysqli_fetch_assoc($result_attempts);

            if ($row_attempts['failed_attempts'] >= 3) {
                $error_message = "تم حظر المحاولة مؤقتًا بعد عدة محاولات فاشلة. حاول بعد قليل.";
                echo "<p style='color:red;'>$error_message</p>";
                exit();
            }

            // التحقق من كلمة المرور
            if (password_verify($password, $user['password'])) {
                // تسجيل دخول ناجح
                mysqli_query($conn, "INSERT INTO login_logs (employee_id, success, ip_address) VALUES ('$employee_id', 1, '$ip_address')");

                $_SESSION['user_id'] = $employee_id;
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['type'];

                if ($user['type'] == 'admin') {
                    header('Location: admin_dashboard.php');
                } elseif ($user['type'] == 'maintenance') {
                    header('Location: maintenance_dashboard.php');
                } else {
                    header('Location: user_dashboard.php');
                }
                exit();
            } else {
                // كلمة المرور خاطئة
                mysqli_query($conn, "INSERT INTO login_logs (employee_id, success, ip_address) VALUES ('$employee_id', 0, '$ip_address')");
                $error_message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
            }
        } else {
            die("خطأ في استعلام المحاولات: " . mysqli_error($conn));
        }
    } else {
        // المستخدم غير موجود
        $error_message = "المستخدم غير موجود.";
    }
}
?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (isset($error_message)): ?>
    <p style="color:red;"><?php echo $error_message; ?></p>
<?php endif; ?>

<div class="container">
    <div class="logo"></div>
    <div class="head">
        <h2>تسجيل الدخول</h2>
        <form action="login.php" method="POST">
            <label for="email">البريد الإلكتروني</label><br>
            <input type="email" name="email" id="user" placeholder="Email" required><br><br>

            <label for="password">كلمة المرور</label><br>
            <input type="password" name="password" id="pswd" placeholder="Password" required><br><br>

            <input type="submit" id="sbn" value="log IN">
        </form>
    </div>
</div>

</body>
</html>
