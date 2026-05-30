<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $password, $id);
    $stmt->execute();

    echo "<script>alert('Password updated'); location='?tab=users';</script>";
}
?>

<h3>🔑 Change Password</h3>

<form method="post">
<input type="password" name="password" placeholder="New Password" required><br><br>
<button>Update Password</button>
</form>