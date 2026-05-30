<?php
// prevent double execution
if (isset($branch_filter)) return;

// ===============================
// 🔐 SESSION SAFE
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ ALWAYS ensure branch exists
if (!isset($_SESSION['branch_id'])) {
    $_SESSION['branch_id'] = 1;
}

$branch_id = (int) $_SESSION['branch_id'];

// ===============================
// 🔗 DB SAFE
// ===============================
if (!isset($conn)) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';
}

// ===============================
// 🏪 FETCH BRANCH
// ===============================
$stmt = $conn->prepare("
    SELECT id, name, type 
    FROM branches 
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("❌ Branch prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $branch_id);
$stmt->execute();

$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $branch_name = "Unknown";
    $branch_type = "unknown";
} else {
    $branch = $result->fetch_assoc();

    $branch_id   = (int)$branch['id'];
    $branch_name = $branch['name'];
    $branch_type = $branch['type'];
}

// ===============================
// HELPERS
// ===============================
function isWholesale() {
    return isset($GLOBALS['branch_type']) && $GLOBALS['branch_type'] === 'wholesale';
}

function isRetail() {
    return isset($GLOBALS['branch_type']) && $GLOBALS['branch_type'] === 'retail';
}
?>