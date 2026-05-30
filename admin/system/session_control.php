<h2>🔐 Session Control</h2>

<?php
session_start();

if (!isset($_SESSION['active_users'])) {
    $_SESSION['active_users'] = [];
}

// track current user
$_SESSION['active_users'][$_SESSION['user_id']] = time();

// remove session
if (isset($_GET['kill'])) {
    unset($_SESSION['active_users'][$_GET['kill']]);
    echo "<p style='color:red'>Session killed</p>";
}
?>

<table border="1" cellpadding="8">
<tr><th>User ID</th><th>Last Active</th><th>Action</th></tr>

<?php foreach ($_SESSION['active_users'] as $id => $time): ?>
<tr>
<td><?= $id ?></td>
<td><?= date("H:i:s", $time) ?></td>
<td><a href="?tab=session_control&kill=<?= $id ?>">❌ Kill</a></td>
</tr>
<?php endforeach; ?>
</table>