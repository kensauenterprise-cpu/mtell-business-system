```php
<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM customers WHERE email=?");
    $check->bind_param("s",$email);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){

        $message = "Email already registered.";

    }else{

        $stmt = $conn->prepare("
            INSERT INTO customers
            (name,email,phone,password,branch_id,customer_type)
            VALUES(?,?,?,?,1,'regular')
        ");

        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $phone,
            $password
        );

        if($stmt->execute()){

            $_SESSION['customer_id'] = $stmt->insert_id;
            $_SESSION['customer_name'] = $name;

            header("Location: checkout.php");
            exit;
        }
    }
}
?>

<h2>Create Account</h2>

<form method="POST">

<input type="text" name="name" placeholder="Full Name" required><br><br>

<input type="email" name="email" placeholder="Email" required><br><br>

<input type="text" name="phone" placeholder="Phone Number" required><br><br>

<input type="password" name="password" placeholder="Password" required><br><br>

<button type="submit">Create Account</button>

</form>

<p><?= $message ?></p>
```
