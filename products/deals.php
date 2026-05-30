<?php
session_start();

// 🔍 Enable error reporting (disable on live server)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ InfinityFree DB credentials
$servername = "sql303.infinityfree.com";
$username   = "if0_39282158";
$password   = "0suY3MfJZVqPJZ7";  // 🔐 Replace with your real password
$database   = "if0_39282158_business";

// 📦 Connect to database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🛍️ Fetch deal products
$dealQuery = $conn->query("SELECT id, name, image, price, description FROM products WHERE is_deal = 1 ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Deals - Mtell Kenya | Online Shopping</title>
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="stylesheet" href="assets/storefront.css">
  <style>
    body { font-family: sans-serif; background: #f9f9f9; }
    .product-grid { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
    .product-card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 16px;
      width: 250px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .product-card img {
      width: 100%;
      height: auto;
      border-radius: 6px;
    }
    .price {
      font-weight: bold;
      color: #28a745;
    }
    .add-cart-form button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 10px;
    }
    h1, p { text-align: center; }
  </style>
</head>
<body>

  <h1>🔥 Hot Deals & Discounts</h1>
  <p>Grab these limited-time offers before they’re gone!</p>

  <div class="product-grid">
    <?php
    if ($dealQuery && $dealQuery->num_rows > 0) {
      while ($product = $dealQuery->fetch_assoc()) {
        echo "
          <div class='product-card'>
            <img src='/infinity/assets/uploads/" . htmlspecialchars($product['image']) . "' alt='" . htmlspecialchars($product['name']) . "'>
            <h4>" . htmlspecialchars($product['name']) . "</h4>
            <p>" . htmlspecialchars($product['description']) . "</p>
            <p class='price'>Ksh " . htmlspecialchars($product['price']) . "</p>

            <!-- ✅ Corrected Add-to-Cart Form -->
            <form method='POST' action='/infinity/add-to-cart.php' class='add-cart-form'>
              <input type='hidden' name='product_id' value='" . htmlspecialchars($product['id']) . "'>
              <button type='submit'>Add to Cart</button>
            </form>
          </div>
        ";
      }
    } else {
      echo "<p>No deals available at the moment. Check back soon!</p>";
    }
    ?>
  </div>

</body>
</html>
