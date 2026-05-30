<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ✅ Validate product_id
if (!isset($_POST['product_id'])) {
    $_SESSION['message'] = "Invalid product.";
    header("Location: /index.php");
    exit();
}

$product_id = intval($_POST['product_id']);

// ✅ Fetch product (NO stock check)
$stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Product not found
if ($result->num_rows == 0) {
    $_SESSION['message'] = "Product not found.";
    header("Location: /index.php");
    exit();
}

$product = $result->fetch_assoc();

// ✅ Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$pid = $product['id'];

// ✅ Add to cart (NO stock restriction)
if (isset($_SESSION['cart'][$pid])) {
    $_SESSION['cart'][$pid]['quantity']++;
} else {
    $_SESSION['cart'][$pid] = [
        "name" => $product['name'],
        "price" => $product['price'],
        "image" => $product['image'],
        "quantity" => 1
    ];
}

// ✅ Success message
$_SESSION['message'] = $product['name'] . " added to cart";

// ✅ Redirect ALWAYS works
header("Location: /infinity/cart/cart.php");
exit();
?>