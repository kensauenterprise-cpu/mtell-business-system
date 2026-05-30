<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if ($_POST) {
    $name  = $_POST['name'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO riders (name, phone) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $phone);
    $stmt->execute();

    echo "Rider added!";
}
?>

<form method="POST">
    <input type="text" name="name" placeholder="Rider Name" required>
    <input type="text" name="phone" placeholder="Phone" required>
    <button>Add Rider</button>
</form>