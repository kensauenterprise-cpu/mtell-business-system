<?php
// ==========================
// 🔐 LOAD SYSTEM (AUTH + DB)
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ==========================
// 🔐 SECURITY (ADMIN / IT ONLY)
// ==========================
if (!in_array($_SESSION['role'] ?? '', ['super_admin','admin','it'])) {
    die("<p style='color:red'>⛔ Access denied</p>");
}

// ==========================
// 📊 QUERY
// ==========================
$sql = "SELECT * FROM settings";
$result = $conn->query($sql);

// ✅ FIX: better error handling
if (!$result) {
    error_log("Settings query failed: " . $conn->error);
    die("<p style='color:red'>❌ Failed to load settings</p>");
}
?>

<h2>⚙️ System Settings</h2>

<table border="1" cellpadding="10" style="border-collapse:collapse;">
<tr>
    <th>ID</th>
    <th>Key</th>
    <th>Value</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= (int)$row['id'] ?></td>
    <td><?= htmlspecialchars($row['setting_key'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['setting_value'] ?? '') ?></td>
</tr>
<?php endwhile; ?>

</table>