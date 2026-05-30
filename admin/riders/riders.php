<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// =========================
// FETCH RIDERS
// =========================
$result = $conn->query("SELECT * FROM riders ORDER BY id DESC");
?>

<h2>👤 Riders Management</h2>

<div style="margin-bottom:15px;">
    <a href="?tab=add_rider" style="padding:10px 15px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px;">
        ➕ Add New Rider
    </a>
</div>

<table border="1" width="100%" cellpadding="10" style="border-collapse:collapse;">
<tr style="background:#f4f4f4;">
    <th>ID</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Status</th>
    <th>Created</th>
    <th>Actions</th>
</tr>

<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td>
            <?php if($row['status'] == 'available'): ?>
                <span style="color:green;">Available</span>
            <?php else: ?>
                <span style="color:red;">Busy</span>
            <?php endif; ?>
        </td>
        <td><?= $row['created_at'] ?></td>
        <td>
            <a href="?tab=assign_rider&rider_id=<?= $row['id'] ?>" style="color:blue;">Assign</a> |
            <a href="?tab=select_rider&rider_id=<?= $row['id'] ?>" style="color:purple;">View</a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="6" style="text-align:center;">No riders found</td>
</tr>
<?php endif; ?>

</table>