<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

switch ($action) {

    case 'add':
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        break;

    case 'remove':
        unset($_SESSION['cart'][$id]);
        break;

    case 'decrease':
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]--;
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
        break;
}

echo json_encode($_SESSION['cart']);