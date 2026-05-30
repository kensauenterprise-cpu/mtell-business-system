<?php 
session_start(); 
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ✅ Protect page
if (!isset($_SESSION['rider_id'])) {
    header("Location: login.php");
    exit;
}

$rider_id = $_SESSION['rider_id'];

// ✅ Fetch assigned orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE rider_id=? AND status='assigned'");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rider Dashboard</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout {
            background: red;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
        }
        .order-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background: green;
            color: #fff;
            text-decoration: none;
            margin-top: 10px;
        }
        .empty {
            margin-top: 20px;
            color: gray;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>Welcome <?= htmlspecialchars($_SESSION['rider_name']) ?></h2>

    <!-- ✅ LOGOUT LINK INTEGRATED -->
    <a href="logout.php" class="logout">Logout</a>
</div>

<h3>📦 My Deliveries</h3>

<?php if ($result->num_rows === 0): ?>
    <p class="empty">No assigned deliveries yet.</p>
<?php endif; ?>

<?php while($row = $result->fetch_assoc()): ?>
    <div class="order-box">
        <p><strong>Order #<?= $row['id'] ?></strong></p>
        <p>Name: <?= htmlspecialchars($row['customer_name']) ?></p>
        <p>Phone: <?= htmlspecialchars($row['phone']) ?></p>
        <p>Address: <?= htmlspecialchars($row['address']) ?></p>

        <!-- ✅ MARK DELIVERED -->
        <a class="btn" href="/infinity/admin/delivery/update_delivery.php?order_id=<?= $row['id'] ?>&status=delivered">
            ✅ Mark Delivered
        </a>
    </div>
<?php endwhile; ?>

</body>
</html>