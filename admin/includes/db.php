<?php
// ==========================
// ⚠️ ERROR LOGGING
// ==========================
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/infinity/logs/error_log.txt');
error_reporting(E_ALL);

// ==========================
// 🗄️ DATABASE CREDENTIALS
// ==========================
$host     = 'sql303.infinityfree.com';
$username = 'if0_39282158';
$password = '0suY3MfJZVqPJZ7';
$database = 'if0_39282158_business'; // ✅ EXACT NAME

// ==========================
// 🔌 CONNECT
// ==========================
$conn = new mysqli($host, $username, $password, $database);

// ==========================
// ❌ HANDLE CONNECTION ERROR
// ==========================
if ($conn->connect_error) {
    error_log("DB ERROR: " . $conn->connect_error);

    die("
    <div style='background:#ffe0e0;padding:15px;color:#900;font-family:Arial'>
        ❌ Database connection failed<br>
        <small>{$conn->connect_error}</small>
    </div>
    ");
}

// ==========================
// 🔤 SET CHARSET
// ==========================
$conn->set_charset("utf8mb4");

// ❌ IMPORTANT: DO NOT RETURN ANYTHING