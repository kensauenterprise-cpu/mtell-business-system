<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = (int)($_GET['id'] ?? 0);

if($id > 0){

    $stmt = $conn->prepare("
        DELETE FROM expenses
        WHERE id=?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: expenses.php");
exit;