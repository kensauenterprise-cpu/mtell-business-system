<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$res = $conn->query("
    SELECT * FROM activity_logs 
    ORDER BY id DESC 
    LIMIT 100
");
?>

<h2>📜 Activity Logs</h2>

<table border="1" width="100%" cellpadding="8">
<tr>
    <th>User</th>
    <th>Action</th>
    <th>Module</th>
    <th>Description</th>
    <th>IP</th>
    <th>Date</th>
</tr>

<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['username']) ?></td>
    <td><?= $row['action'] ?></td>
    <td><?= $row['module'] ?></td>
    <td><?= htmlspecialchars($row['description']) ?></td>
    <td><?= $row['ip_address'] ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>