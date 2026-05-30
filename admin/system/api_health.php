<?php

require_once __DIR__ . '/../includes/db.php';

$db_status = ($conn && !$conn->connect_error)
    ? "Online"
    : "Offline";

?>

<table border="1" cellpadding="10">
<tr>
    <th>Service</th>
    <th>Status</th>
</tr>

<tr>
    <td>Database Connection</td>
    <td><?= $db_status ?></td>
</tr>

<tr>
    <td>Server Status</td>
    <td>Online</td>
</tr>

<tr>
    <td>PHP Version</td>
    <td><?= phpversion() ?></td>
</tr>

<tr>
    <td>Session Status</td>
    <td><?= session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive' ?></td>
</tr>

</table>