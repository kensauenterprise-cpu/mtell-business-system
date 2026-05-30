<?php

// =========================
// LOAD SYSTEM
// =========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// =========================
// 🔐 SESSION SAFE
// =========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================
// ✅ DB CHECK
// =========================
if (!isset($conn) || !$conn) {
    die("Database connection missing");
}

// =========================
// ✅ AUTH CHECK
// =========================
if (!isset($_SESSION['user_id'])) {

    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// =========================
// 🌍 BRANCH FILTER
// =========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// =========================
// UPDATE DELIVERY STATUS
// =========================
if (isset($_POST['update_status'])) {

    $order_id = (int)($_POST['order_id'] ?? 0);

    $new_status = trim($_POST['delivery_status'] ?? 'pending');

    $allowed = ['pending', 'dispatched', 'delivered'];

    if (!in_array($new_status, $allowed, true)) {
        $new_status = 'pending';
    }

    // =========================
    // UPDATE QUERY
    // =========================
    if ($branch_id === 'all') {

        $stmt = $conn->prepare("
            UPDATE orders
            SET delivery_status = ?
            WHERE id = ?
        ");

        if ($stmt) {

            $stmt->bind_param("si", $new_status, $order_id);
            $stmt->execute();
            $stmt->close();
        }

    } else {

        $stmt = $conn->prepare("
            UPDATE orders
            SET delivery_status = ?
            WHERE id = ?
            AND branch_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sii",
                $new_status,
                $order_id,
                $branch_id
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    // =========================
    // REDIRECT SAFE
    // =========================
    header("Location: ?tab=financials&report=delivery");
    exit;
}

// =========================
// FETCH DELIVERY ORDERS
// =========================
$orders = [];

if ($branch_id === 'all') {

    $sql = "
    SELECT
        id,
        customer_name,
        phone,
        address,
        total_amount,
        payment_method,
        status,
        delivery_status,
        source,
        created_at,
        branch_id
    FROM orders
    ORDER BY created_at DESC
    ";

    $result = $conn->query($sql);

} else {

    $branch_id = (int)$branch_id;

    $sql = "
    SELECT
        id,
        customer_name,
        phone,
        address,
        total_amount,
        payment_method,
        status,
        delivery_status,
        source,
        created_at
    FROM orders
    WHERE branch_id = ?
    ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("i", $branch_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $stmt->close();
    }
}

// =========================
// STORE RESULTS
// =========================
if (isset($result) && $result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Delivery Orders</title>

<style>

body{
    font-family:Arial;
    background:#f4f4f4;
}

.container{
    width:95%;
    margin:auto;
    margin-top:20px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    padding:10px;
    border:1px solid #ddd;
    text-align:center;
}

th{
    background:#333;
    color:white;
}

.badge{
    padding:5px 10px;
    border-radius:5px;
    color:white;
    display:inline-block;
}

.pending{
    background:orange;
}

.dispatched{
    background:purple;
}

.delivered{
    background:green;
}

.paid{
    color:green;
    font-weight:bold;
}

.unpaid{
    color:red;
    font-weight:bold;
}

select{
    padding:5px;
}

button{
    padding:6px 10px;
    border:none;
    background:#007bff;
    color:white;
    border-radius:4px;
    cursor:pointer;
}

button:hover{
    background:#0056b3;
}

</style>

</head>

<body>

<div class="container">

<h2>
🚚 Delivery Orders
(Branch <?= htmlspecialchars((string)$branch_id) ?>)
</h2>

<table>

<tr>

    <th>ID</th>
    <th>Customer</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Amount</th>
    <th>Payment</th>
    <th>Source</th>
    <th>Delivery</th>

    <?php if ($branch_id === 'all'): ?>

    <th>Branch</th>

    <?php endif; ?>

    <th>Update</th>

</tr>

<?php if (!empty($orders)): ?>

    <?php foreach ($orders as $order): ?>

    <tr>

        <td>
            <?= (int)$order['id']; ?>
        </td>

        <td>
            <?= htmlspecialchars($order['customer_name'] ?? '-'); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['phone'] ?? '-'); ?>
        </td>

        <td>
            <?= htmlspecialchars($order['address'] ?? '-'); ?>
        </td>

        <td>
            KES <?= number_format((float)($order['total_amount'] ?? 0), 2); ?>
        </td>

        <!-- PAYMENT STATUS -->
        <td>

            <?php

            if (($order['status'] ?? '') === 'paid') {

                echo "<span class='paid'>PAID</span>";

            } else {

                echo "
                <span class='unpaid'>
                    ".
                    htmlspecialchars($order['status'] ?? 'unpaid')
                    ."
                </span>
                ";
            }

            ?>

        </td>

        <!-- SOURCE -->
        <td>
            <?= htmlspecialchars(strtoupper($order['source'] ?? 'N/A')); ?>
        </td>

        <!-- DELIVERY STATUS -->
        <td>

            <span class="badge <?= htmlspecialchars($order['delivery_status'] ?? 'pending'); ?>">

                <?= htmlspecialchars(strtoupper($order['delivery_status'] ?? 'PENDING')); ?>

            </span>

        </td>

        <?php if ($branch_id === 'all'): ?>

        <td>
            <?= htmlspecialchars((string)($order['branch_id'] ?? '-')); ?>
        </td>

        <?php endif; ?>

        <!-- UPDATE -->
        <td>

            <form method="POST">

                <input
                    type="hidden"
                    name="order_id"
                    value="<?= (int)$order['id']; ?>"
                >

                <select name="delivery_status">

                    <option
                        value="pending"
                        <?= (($order['delivery_status'] ?? '') === 'pending') ? 'selected' : ''; ?>
                    >
                        Pending
                    </option>

                    <option
                        value="dispatched"
                        <?= (($order['delivery_status'] ?? '') === 'dispatched') ? 'selected' : ''; ?>
                    >
                        Dispatched
                    </option>

                    <option
                        value="delivered"
                        <?= (($order['delivery_status'] ?? '') === 'delivered') ? 'selected' : ''; ?>
                    >
                        Delivered
                    </option>

                </select>

                <button type="submit" name="update_status">
                    Update
                </button>

            </form>

        </td>

    </tr>

    <?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="<?= ($branch_id === 'all') ? 10 : 9 ?>">

    No orders found

</td>

</tr>

<?php endif; ?>

</table>

</div>

</body>

</html>