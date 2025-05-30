<?php
include('config.php');
$email_filter = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_email'])) {
    $email_filter = mysqli_real_escape_string($conn, $_POST['search_email']);
    $query = "
    SELECT login_logs.*, employees.email 
    FROM login_logs 
    JOIN employees ON login_logs.employee_id = employees.employee_id 
    WHERE employees.email LIKE '%$email_filter%'
    ORDER BY login_logs.login_time DESC
    ";
} else {
    $query = "
    SELECT login_logs.*, employees.email 
    FROM login_logs 
    JOIN employees ON login_logs.employee_id = employees.employee_id 
    ORDER BY login_logs.login_time DESC
    ";
}


$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style2.css">

    <title>سجل الدخول</title>
    <meta charset="UTF-8">
</head>
<body>

<h2>سجل محاولات تسجيل الدخول</h2>

<form method="post">
    <label>ابحث بالبريد الإلكتروني:</label>
    <input type="text" name="search_email" value="<?php echo htmlspecialchars($email_filter); ?>">
    <input type="submit" value="بحث">
</form>

<br>

<table border="1">
    <tr>
        <th>البريد الإلكتروني</th>
        <th>النجاح</th>
        <th>IP</th>
        <th>الوقت</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['success'] ? '✔️' : '❌'; ?></td>
            <td><?php echo $row['ip_address']; ?></td>
            <td><?php echo $row['login_time']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
