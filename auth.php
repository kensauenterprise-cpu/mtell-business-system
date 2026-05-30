<?php
session_start();

// ===============================
// 🔐 CHECK LOGIN
// ===============================
if (!isset($_SESSION['user_id'])) {
    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ===============================
// ⏳ SESSION TIMEOUT (30 mins)
// ===============================
$timeout = 1800; // 30 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: /infinity/admin/pages/login.php?timeout=1");
    exit;
}

$_SESSION['last_activity'] = time();
?>