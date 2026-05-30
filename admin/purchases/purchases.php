<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// FETCH PURCHASES
// ==========================
$sql = "

SELECT

    p.id,
    p.total,
    p.status,
    p.created_at,

    s.name AS supplier_name

FROM purchases p

LEFT JOIN suppliers s
ON p.supplier_id = s.id

ORDER BY p.id DESC

";

$result = $conn->query($sql);
?>

<h2>🛒 Purchases</h2>

<a href="?tab=add_purchase"
style="
background:green;
color:white;
padding:10px 15px;
text-decoration:none;
border-radius:5px;
display:inline-block;
margin-bottom:15px;
">
➕ Add Purchase
</a>

<table border="1" cellpadding="10" cellspacing="0" width="100%">

<tr style="background:#f1f1f1;">

    <th>ID</th>
    <th>Supplier</th>
    <th>Total</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>

</tr>

<?php if ($result && $result->num_rows > 0): ?>

    <?php while($row = $result->fetch_assoc()): ?>

    <tr>

        <td><?= $row['id'] ?></td>

        <td>
            <?= htmlspecialchars($row['supplier_name'] ?? 'Unknown Supplier') ?>
        </td>

        <td>
            Ksh <?= number_format($row['total'], 2) ?>
        </td>

        <td>
            <?= htmlspecialchars($row['status']) ?>
        </td>

        <td>
            <?= $row['created_at'] ?>
        </td>

        <td>

            <a href="?tab=edit_purchase&id=<?= $row['id'] ?>">
                ✏ Edit
            </a>

            |

            <a href="?tab=delete_purchase&id=<?= $row['id'] ?>"
               onclick="return confirm('Delete purchase?')">
                🗑 Delete
            </a>

            |

            <a href="?tab=purchase_invoice&id=<?= $row['id'] ?>">
                🧾 Invoice
            </a>

        </td>

    </tr>

    <?php endwhile; ?>

<?php else: ?>

<tr>

    <td colspan="6" style="text-align:center;">
        No purchases found
    </td>

</tr>

<?php endif; ?>

</table>