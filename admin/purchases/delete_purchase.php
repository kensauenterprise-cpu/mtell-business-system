
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = (int)($_GET['id'] ?? 0);

if($id > 0){

    $stmt = $conn->prepare("
        DELETE FROM purchases
        WHERE id=?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();
}

header("Location: purchases.php");
exit;