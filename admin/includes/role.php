<?php
// ==========================
// 🛡 ROLE-BASED ACCESS CONTROL
// ==========================

function requireRole($roles)
{
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // ==========================
    // 🚫 NO ROLE SET
    // ==========================
    if (!isset($_SESSION['role'])) {
        header("Location: /infinity/admin/pages/login.php");
        exit;
    }

    $userRole = $_SESSION['role'];

    // ==========================
    // ✅ NORMALIZE ROLES
    // ==========================
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    // ==========================
    // 🔍 CHECK PERMISSION
    // ==========================
    if (!in_array($userRole, $roles)) {

        // AJAX handling
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Access denied."
            ]);
            exit;
        }

        // Normal request → show error page
        http_response_code(403);
        echo "
        <h2 style='color:red;text-align:center;margin-top:50px;'>
            ⛔ Access Denied
        </h2>
        <p style='text-align:center;'>
            You do not have permission to access this page.
        </p>
        ";
        exit;
    }
}