<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add product ID to cart
    $_SESSION['cart'][] = $product_id;

    // Redirect to the page the user came from
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}
?>
