<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

echo "<h2>👨‍💼 Employees</h2>";

$result = $conn->query("
    SELECT *
    FROM users
    ORDER BY id DESC
");
?>

<div class="card">

<table border="1" width="100%" cellpadding="10">

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
</tr>

<?php if($result && $result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>

    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($row['role'] ?? 'Staff') ?></td>
    </tr>

    <?php endwhile; ?>
<?php endif; ?>

</table>

</div>

<style>
.card{
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 10px #ccc;
}
table{
    border-collapse:collapse;
}
th{
    background:#007bff;
    color:white;
}
</style>