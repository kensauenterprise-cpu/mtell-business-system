<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/role.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("❌ Database connection missing");
}

// ==========================
// 🔐 SESSION ROLE
// ==========================
$role = strtolower(trim($_SESSION['role'] ?? 'guest'));

if ($role === 'guest') {

    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = (int)($_SESSION['branch_id'] ?? 1);

// ==========================
// ➕ ADD CUSTOMER
// ==========================
if (isset($_POST['save_customer'])) {

    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // ✅ NEW
    $customer_type = trim($_POST['customer_type'] ?? 'regular');

    if ($name === '') {
        die("❌ Name is required");
    }

    // Prevent NULL values
    $phone = $phone ?: '';
    $email = $email ?: '';

    $stmt = $conn->prepare("
        INSERT INTO customers (
            name,
            email,
            phone,
            customer_type,
            branch_id
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("❌ Prepare failed: ".$conn->error);
    }

    $stmt->bind_param(
        "ssssi",
        $name,
        $email,
        $phone,
        $customer_type,
        $branch_id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: ?tab=customers");
    exit;
}

// ==========================
// 🗑 DELETE CUSTOMER
// ==========================
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM customers
        WHERE id = ?
        AND branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $id,
            $branch_id
        );

        $stmt->execute();

        $stmt->close();
    }

    header("Location: ?tab=customers");
    exit;
}

// ==========================
// 📦 FETCH CUSTOMERS
// ==========================
$stmt = $conn->prepare("
    SELECT
        id,
        name,
        phone,
        email,
        customer_type
    FROM customers
    WHERE branch_id = ?
    ORDER BY id DESC
");

if (!$stmt) {
    die("❌ Query failed: ".$conn->error);
}

$stmt->bind_param("i", $branch_id);

$stmt->execute();

$customers = $stmt->get_result();
?>

<h2>👥 Customers</h2>

<h3>Add Customer</h3>

<form method="POST">

    <input
        type="text"
        name="name"
        placeholder="Name"
        required
    >

    <input
        type="text"
        name="phone"
        placeholder="Phone"
    >

    <input
        type="email"
        name="email"
        placeholder="Email"
    >

    <br><br>

    <!-- ✅ NEW -->
    <label>Customer Type</label>

    <select name="customer_type">

        <option value="regular">
            Regular Customer
        </option>

        <option value="walk_in">
            Walk-in Customer
        </option>

        <option value="wholesale">
            Wholesale Customer
        </option>

        <option value="credit">
            Credit Customer
        </option>

    </select>

    <br><br>

    <button name="save_customer">
        Save
    </button>

</form>

<hr>

<table
    border="1"
    width="100%"
    cellpadding="8"
    style="background:white;"
>

<tr>

    <th>ID</th>

    <th>Name</th>

    <th>Phone</th>

    <th>Email</th>

    <!-- ✅ NEW -->
    <th>Type</th>

    <th>Action</th>

</tr>

<?php if ($customers && $customers->num_rows > 0): ?>

    <?php while($c = $customers->fetch_assoc()): ?>

    <tr>

        <td>
            #<?= (int)($c['id'] ?? 0) ?>
        </td>

        <td>
            <?= htmlspecialchars($c['name'] ?? '') ?>
        </td>

        <td>
            <?= htmlspecialchars($c['phone'] ?? '') ?>
        </td>

        <td>
            <?= htmlspecialchars($c['email'] ?? '') ?>
        </td>

        <!-- ✅ NEW -->
        <td>
            <?= htmlspecialchars($c['customer_type'] ?? '') ?>
        </td>

        <td>

            <a
                href="?tab=customers&delete=<?= (int)($c['id'] ?? 0) ?>"
                onclick="return confirm('Delete?')"
            >
                🗑
            </a>

        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="6">

    No customers found

</td>

</tr>

<?php endif; ?>

</table>

<?php
$stmt->close();
?>