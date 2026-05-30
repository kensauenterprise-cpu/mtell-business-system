<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/role.php';

requireRole('admin'); // 🔒 Only admin can delete users

// ==========================
// VALIDATE ID
// ==========================
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die("❌ Invalid user ID");
}

// ==========================
// PREVENT SELF DELETE
// ==========================
if ($id == $_SESSION['user_id']) {
    die("⛔ You cannot delete your own account");
}

// ==========================
// FETCH USER
// ==========================
$stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("❌ User not found");
}

// ==========================
// PROTECT MAIN ADMIN
// ==========================
if ($user['id'] == 1 || $user['role'] === 'admin') {
    die("⛔ Cannot delete main/admin user");
}

// ==========================
// DELETE USER
// ==========================
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    // ==========================
    // ✅ OPTIONAL: AUDIT LOG
    // ==========================
    $admin_id = $_SESSION['user_id'];
    $action = "Deleted user: " . $user['username'];

    $log = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, created_at)
        VALUES (?, ?, NOW())
    ");
    $log->bind_param("is", $admin_id, $action);
    $log->execute();

    // ==========================
    // RESPONSE
    // ==========================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(["success" => true, "msg" => "User deleted"]);
    } else {
        header("Location: /infinity/admin/pages/dashboard.php?tab=users");
    }

} else {
    echo "❌ Delete failed";
}
?>