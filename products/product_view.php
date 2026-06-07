<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$id = (int)($_GET['id'] ?? 0);

$product = $conn->query(
    "SELECT * FROM products WHERE id=$id"
)->fetch_assoc();

if (!$product) {
    die("Product not found");
}
?>

<h2><?= htmlspecialchars($product['name']) ?></h2>

<?php if (!empty($product['image_url'])): ?>

<img
src="<?= $product['image_url'] ?>"
width="250">

<?php endif; ?>

<p><strong>Category:</strong>
<?= htmlspecialchars($product['category']) ?>
</p>

<p><strong>Description:</strong>
<?= nl2br(htmlspecialchars($product['description'])) ?>
</p>

<p><strong>Price:</strong>
Ksh <?= number_format($product['price'],2) ?>
</p>