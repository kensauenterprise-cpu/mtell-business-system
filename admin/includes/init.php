<?php

// ==========================
// ?? SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ?? ERROR REPORTING
// ==========================
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ==========================
// ?? TIMEZONE
// ==========================
date_default_timezone_set('Africa/Nairobi');

// ==========================
// ??? LOAD DATABASE
// ==========================
$dbFile = $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if (!file_exists($dbFile)) {

    die("
    <div style='background:#ffe0e0;padding:15px;color:#900'>
        ? db.php not found
        <br><br>
        ".$dbFile."
    </div>
    ");
}

require_once $dbFile;

// ==========================
// ?? VALIDATE CONNECTION
// ==========================
if (
    !isset($conn) ||
    !($conn instanceof mysqli) ||
    $conn->connect_error
) {

    die("
    <div style='background:#ffe0e0;padding:15px;color:#900'>
        ? Database connection missing
    </div>
    ");
}

// ==========================
// ?? LOAD AUTH
// ==========================
$auth = $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/auth.php';

if (file_exists($auth)) {
    require_once $auth;
}

// ==========================
// ?? LOAD LOGGER
// ==========================
$logger = $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/logger.php';

if (file_exists($logger)) {
    require_once $logger;
}

// ==========================
// ?? GLOBALS
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

$user_id = $_SESSION['user_id'] ?? null;

$username = $_SESSION['username'] ?? 'Guest';

$role = $_SESSION['role'] ?? 'guest';

// ==========================
// ?? HELPERS
// ==========================
if (!function_exists('e')) {

    function e($string) {

        return htmlspecialchars(
            $string,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('dd')) {

    function dd($data) {

        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die();
    }
}

// ==========================
// ?? SAFE QUERY
// ==========================
if (!function_exists('safeQuery')) {

    function safeQuery($conn, $query) {

        $result = $conn->query($query);

        if (!$result) {

            error_log(
                "SQL ERROR: ".$conn->error
            );

            die("
            <div style='background:#ffe0e0;padding:15px;color:#900'>
                ? SQL Error
            </div>
            ");
        }

        return $result;
    }
}

?>