<?php
// Optional: Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Set default title if not provided
$pageTitle = isset($pageTitle) ? $pageTitle : "Mtell Kenya";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="stylesheet" href="/infinity/admin/assets/css/style.css">
  <link rel="stylesheet" href="/infinity/admin/assets/admin.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
    nav a {
      margin-right: 15px;
      text-decoration: none;
      padding: 8px 12px;
      background: #007bff;
      color: white;
      border-radius: 4px;
    }
    nav a.active { background: #0056b3; }
    .dashboard-link a {
      display: inline-block;
      margin: 10px;
      padding: 10px 15px;
      background: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }
    .tab-content {
      margin-top: 20px;
      background: white;
      padding: 20px;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    .card {
      border-radius: 6px;
      box-shadow: 0 0 4px rgba(0,0,0,0.1);
      margin: 20px auto;
      max-width: 500px;
    }
    .card-header {
      background: #f8f9fa;
      font-weight: bold;
      padding: 10px;
    }
    .card-body { padding: 15px; }

    .shop-button {
      background: #28a745;
      color: white;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      animation: blinkShopNow 1.2s infinite;
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
      transition: background-color 0.3s ease;
    }

    @keyframes blinkShopNow {
      0%, 50%, 100% { opacity: 1; }
      25%, 75% { opacity: 0.3; }
    }

    .shop-button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

<header style="display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
  <div>
    <a href="/infinity/deals.php" class="shop-button">SHOP NOW 🛍️</a>
  </div>
  <div style="flex-grow: 1; text-align: center;">
    <img src="/infinity/assets/uploads/FB_IMG_1757061942498.jpg" alt="Mtell Kenya Logo" style="height: 100px; max-height: 120px;">
  </div>
  <div>
    <a href="/infinity/cart.php" class="shop-button">🛒 Cart</a>
  </div>
</header>
