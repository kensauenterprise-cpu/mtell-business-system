<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$id = (int)($_GET['id'] ?? 0);

$conn->query("DELETE FROM products WHERE id=$id");

header("Location: products.php");
exit;