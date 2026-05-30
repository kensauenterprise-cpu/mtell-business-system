<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$order_id = $_GET['order_id'];

$riders = $conn->query("SELECT * FROM riders WHERE status='available'");

if ($_POST) {
    $rider_id = $_POST['rider_id'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET rider_id=?, delivery_status='Assigned' 
        WHERE id=?
    ");
    $stmt->bind_param("ii", $rider_id, $order_id);
    $stmt->execute();

    echo "Rider Assigned!";
}
?>

<form method="POST">
    <select name="rider_id">
        <?php while($r = $riders->fetch_assoc()): ?>
            <option value="<?= $r['id'] ?>">
                <?= $r['name'] ?> (<?= $r['phone'] ?>)
            </option>
        <?php endwhile; ?>
    </select>
    <button>Assign Rider</button>
</form>