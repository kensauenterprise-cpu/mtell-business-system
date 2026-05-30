<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM riders WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $rider = $result->fetch_assoc();

        $_SESSION['rider_id'] = $rider['id'];
        $_SESSION['rider_name'] = $rider['name'];

        header("Location: rider_orders.php");
        exit;
    } else {
        $error = "Invalid login";
    }
}
?>

<h2>Rider Login</h2>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <button type="submit">Login</button>
</form>

<p style="color:red;"><?= $error ?></p>