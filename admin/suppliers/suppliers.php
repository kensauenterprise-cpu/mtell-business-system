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
    die("❌ Database connection failed");
}

// ==========================
// 🔐 SESSION ROLE
// ==========================
$role = $_SESSION['role'] ?? 'guest';

if ($role === 'guest') {
    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = (int)($_SESSION['branch_id'] ?? 1);

// ==========================
// ➕ ADD SUPPLIER
// ==========================
if (isset($_POST['save_supplier'])) {

    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        die("❌ Supplier name required");
    }

    $stmt = $conn->prepare("
        INSERT INTO suppliers (
            name,
            phone,
            branch_id
        )
        VALUES (?, ?, ?)
    ");

    if (!$stmt) {
        die("❌ Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssi",
        $name,
        $phone,
        $branch_id
    );

    $stmt->execute();
    $stmt->close();

    header("Location: ?tab=suppliers");
    exit;
}

// ==========================
// 🗑 DELETE SUPPLIER
// ==========================
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM suppliers
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

    header("Location: ?tab=suppliers");
    exit;
}

// ==========================
// 📦 FETCH SUPPLIERS
// ==========================
$stmt = $conn->prepare("
    SELECT *
    FROM suppliers
    WHERE branch_id = ?
    ORDER BY id DESC
");

if (!$stmt) {
    die("❌ Query failed: " . $conn->error);
}

$stmt->bind_param("i", $branch_id);

$stmt->execute();

$suppliers = $stmt->get_result();
?>

<h2>🏭 Suppliers</h2>

<h3>Add Supplier</h3>

<form method="POST">

    <input
        type="text"
        name="name"
        placeholder="Supplier Name"
        required
    >

    <input
        type="text"
        name="phone"
        placeholder="Phone"
    >

    <button name="save_supplier">
        Save
    </button>

</form>

<hr>

<table
    border="1"
    cellpadding="8"
    width="100%"
    style="background:white;"
>

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Action</th>
</tr>

<?php if ($suppliers && $suppliers->num_rows > 0): ?>

    <?php while($s = $suppliers->fetch_assoc()): ?>

    <tr>

        <td>
            #<?= (int)$s['id'] ?>
        </td>

        <td>
            <?= htmlspecialchars($s['name']) ?>
        </td>

        <td>
            <?= htmlspecialchars($s['phone']) ?>
        </td>

        <td>

            <a
                href="?tab=suppliers&delete=<?= (int)$s['id'] ?>"
                onclick="return confirm('Delete?')"
            >
                🗑
            </a>

        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="4">
        No suppliers found
    </td>
</tr>

<?php endif; ?>

</table>

<?php
$stmt->close();
?>