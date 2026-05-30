<?php

// ==========================
// SESSION
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ERROR REPORTING
// ==========================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================
// LOAD DATABASE
// ==========================
require_once __DIR__ . '/../includes/db.php';

// ==========================
// PAGE SETTINGS
// ==========================
$title = "Full Application Diagnostic";
$date = date("Y-m-d H:i:s");

// ==========================
// DATABASE STATUS
// ==========================
$db_status = (
    isset($conn) &&
    $conn instanceof mysqli &&
    !$conn->connect_error
)
? "Online"
: "Offline";

// ==========================
// SERVER STATUS
// ==========================
$server_status = "Online";

// ==========================
// PHP VERSION
// ==========================
$php_version = phpversion();

// ==========================
// SESSION STATUS
// ==========================
$session_status = (
    session_status() === PHP_SESSION_ACTIVE
)
? "Active"
: "Inactive";

// ==========================
// MODULE FILES
// ==========================
$modules = [
    'dashboard.php',
    'products.php',
    'sales.php',
    'reports.php',
    'branch_comparison.php',
    'customers.php',
    'suppliers.php',
    'inventory.php',
    'settings.php'
];

// ==========================
// DATABASE TABLES
// ==========================
$tables = [
    'products',
    'orders',
    'branches',
    'customers',
    'suppliers',
    'users',
    'inventory',
    'sales'
];

?>

<!DOCTYPE html>
<html>
<head>

<title><?= htmlspecialchars($title) ?></title>

<style>

body{
    font-family:Arial,sans-serif;
    background:#f1f5f9;
    padding:20px;
}

.card{
    background:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

table th,
table td{
    border:1px solid #ddd;
    padding:10px;
}

table th{
    background:#0f172a;
    color:white;
}

.online{
    color:green;
    font-weight:bold;
}

.offline{
    color:red;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="card">

<h2>📡 Full Application Diagnostic</h2>

<p><b>Generated:</b> <?= htmlspecialchars($date) ?></p>

<table>

<tr>
    <th>Service</th>
    <th>Status</th>
</tr>

<tr>
    <td>Database Connection</td>
    <td class="<?= $db_status === 'Online' ? 'online' : 'offline' ?>">
        <?= htmlspecialchars($db_status) ?>
    </td>
</tr>

<tr>
    <td>Server Status</td>
    <td class="online">
        <?= htmlspecialchars($server_status) ?>
    </td>
</tr>

<tr>
    <td>PHP Version</td>
    <td>
        <?= htmlspecialchars($php_version) ?>
    </td>
</tr>

<tr>
    <td>Session Status</td>
    <td class="<?= $session_status === 'Active' ? 'online' : 'offline' ?>">
        <?= htmlspecialchars($session_status) ?>
    </td>
</tr>

</table>

</div>

<div class="card">

<h2>📂 Module File Check</h2>

<table>

<tr>
    <th>Module</th>
    <th>Status</th>
</tr>

<?php foreach ($modules as $module): ?>

<?php
$path = __DIR__ . '/' . $module;
$exists = file_exists($path);
?>

<tr>
    <td><?= htmlspecialchars($module) ?></td>
    <td class="<?= $exists ? 'online' : 'offline' ?>">
        <?= $exists ? 'Available' : 'Missing' ?>
    </td>
</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h2>🗄️ Database Table Check</h2>

<table>

<tr>
    <th>Table</th>
    <th>Status</th>
</tr>

<?php foreach ($tables as $table): ?>

<?php

$exists = false;

if ($db_status === 'Online') {

    $res = $conn->query("SHOW TABLES LIKE '$table'");

    if ($res && $res->num_rows > 0) {
        $exists = true;
    }
}

?>

<tr>
    <td><?= htmlspecialchars($table) ?></td>
    <td class="<?= $exists ? 'online' : 'offline' ?>">
        <?= $exists ? 'Exists' : 'Missing' ?>
    </td>
</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h2>⚙️ PHP Extensions</h2>

<table>

<tr>
    <th>Extension</th>
    <th>Status</th>
</tr>

<?php

$extensions = [
    'mysqli',
    'pdo',
    'json',
    'mbstring',
    'openssl',
    'curl'
];

foreach ($extensions as $ext):

?>

<tr>
    <td><?= htmlspecialchars($ext) ?></td>
    <td class="<?= extension_loaded($ext) ? 'online' : 'offline' ?>">
        <?= extension_loaded($ext) ? 'Enabled' : 'Disabled' ?>
    </td>
</tr>

<?php endforeach; ?>

</table>

</div>

</body>
</html>