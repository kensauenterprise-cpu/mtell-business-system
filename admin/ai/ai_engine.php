<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ==========================
// 🔁 GET BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📊 FETCH PRODUCTS
// ==========================
if ($branch_id === 'all') {
    $products = $conn->query("SELECT * FROM products");
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE branch_id=?");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $products = $stmt->get_result();
}

// ==========================
// 🧠 LOOP PRODUCTS
// ==========================
while ($p = $products->fetch_assoc()) {

    $product_id = $p['id'];
    $stock = $p['stock'];

    // ==========================
    // 📈 SALES LAST 7 DAYS
    // ==========================
    if ($branch_id === 'all') {

        $sales = $conn->query("
            SELECT SUM(quantity) as total
            FROM order_items
            WHERE product_id = $product_id
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")->fetch_assoc()['total'] ?? 0;

    } else {

        $stmt = $conn->prepare("
            SELECT SUM(oi.quantity) as total
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE oi.product_id=? AND o.branch_id=?
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->bind_param("ii", $product_id, $branch_id);
        $stmt->execute();
        $sales = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    }

    // ==========================
    // 🧮 DAILY AVERAGE
    // ==========================
    $daily = $sales / 7;

    // ==========================
    // ⚠️ DAYS TO STOCKOUT
    // ==========================
    $days_left = $daily > 0 ? ($stock / $daily) : 999;

    // ==========================
    // 🔄 RECOMMENDED RESTOCK
    // ==========================
    $restock = 0;

    if ($days_left < 5) {
        $restock = ceil($daily * 14); // 2 weeks buffer
    }

    // ==========================
    // 💾 SAVE TO DB
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO ai_predictions
        (product_id, branch_id, predicted_daily_sales, days_to_stockout, recommended_restock)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("isdii", $product_id, $branch_id, $daily, $days_left, $restock);
    $stmt->execute();
}

echo "✅ AI Predictions Updated";