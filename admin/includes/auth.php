<?php
// ==========================
// 🔐 AUTH GUARD (LOGIN REQUIRED)
// ==========================

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 🚫 NOT LOGGED IN
// ==========================
if (!isset($_SESSION['user_id'])) {

    // Save requested page (optional - for redirect after login)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';

    // Handle AJAX requests differently
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized. Please login."
        ]);
        exit;
    }

    // Normal redirect
    header("Location: /infinity/admin/pages/login.php");
    exit;
}

// ==========================
// ✅ OPTIONAL: SESSION FIXATION PROTECTION
// ==========================
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// ==========================
// ⏱ OPTIONAL: SESSION TIMEOUT (30 mins)
// ==========================
$timeout = 1800; // 30 minutes

if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > $timeout)) {

    session_unset();
    session_destroy();

    header("Location: /infinity/admin/pages/login.php?timeout=1");
    exit;
}

$_SESSION['last_activity'] = time();