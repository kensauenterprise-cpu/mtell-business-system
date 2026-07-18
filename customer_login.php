<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$message = '';

if($_SERVER['REQUEST_METHOD']=='POST'){

    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT
            id,
            name,
            email,
            phone,
            password
        FROM customers
        WHERE email = ?
           OR phone = ?
    ");

    $stmt->bind_param(
        "ss",
        $login,
        $login
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){

        if(password_verify(
            $password,
            $row['password']
        )){

            $_SESSION['customer_id'] =
                $row['id'];

            $_SESSION['customer_name'] =
                $row['name'];

            header("Location: checkout.php");
            exit;
        }
    }

    $message = "Invalid login details.";
}
?>

<h2>Customer Login</h2>

<form method="POST" autocomplete="off">

<input
type="text"
name="login"
placeholder="Email or Phone Number"
required
>
<br><br>

<input
type="password"
name="password"
placeholder="Password"
required
>
<br><br>

<button type="submit">
Login
</button>

</form>

<p style="color:red;">
<?= htmlspecialchars($message) ?>
</p>

<p>
<a href="signup.php">
Create Account
</a>
</p>