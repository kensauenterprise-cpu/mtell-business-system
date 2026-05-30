<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ✅ Validate order_id
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid Order ID");
}

$order_id = intval($_GET['order_id']);

// ✅ Fetch riders
$riders = $conn->query("SELECT id, name FROM riders");

if (!$riders) {
    die("Error fetching riders: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Rider</title>
    <style>
        body { font-family: Arial; padding:20px; background:#f5f5f5; }
        .box {
            background:#fff;
            padding:20px;
            max-width:400px;
            margin:auto;
            border:1px solid #ccc;
        }
        select, button {
            width:100%;
            padding:10px;
            margin-top:10px;
        }
        button {
            background:blue;
            color:white;
            border:none;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🚚 Assign Rider</h2>
    <p>Order ID: <strong><?= $order_id ?></strong></p>

    <!-- ✅ FORM CALLS assign_rider.php -->
    <form method="POST" action="assign_rider.php?order_id=<?= $order_id ?>">
        
        <label>Select Rider:</label>
        <select name="rider_id" required>
            <option value="">-- Choose Rider --</option>

            <?php while($r = $riders->fetch_assoc()): ?>
                <option value="<?= $r['id'] ?>">
                    <?= htmlspecialchars($r['name']) ?>
                </option>
            <?php endwhile; ?>

        </select>

        <button type="submit">Assign Rider</button>
    </form>

</div>

</body>
</html>