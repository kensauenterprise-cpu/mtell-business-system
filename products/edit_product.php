<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$id = (int)($_GET['id'] ?? 0);

$product = $conn->query(
    "SELECT * FROM products WHERE id=$id"
)->fetch_assoc();

if (!$product) {
    die("Product not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $image_url = $product['image_url'];

    if (!empty($_FILES['image']['name'])) {

        $uploadDir = $_SERVER['DOCUMENT_ROOT'].'/infinity/uploads/products/';

        $fileName = time().'_'.$_FILES['image']['name'];

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir.$fileName
        );

        $image_url = '/infinity/uploads/products/'.$fileName;
    }

    $stmt = $conn->prepare("
        UPDATE products
        SET
            name=?,
            category=?,
            description=?,
            price=?,
            image_url=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "sssdsi",
        $name,
        $category,
        $description,
        $price,
        $image_url,
        $id
    );

    $stmt->execute();

    header("Location: products.php");
    exit;
}
?>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="name"
value="<?= htmlspecialchars($product['name']) ?>">

<br><br>

<input type="text" name="category"
value="<?= htmlspecialchars($product['category']) ?>">

<br><br>

<textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>

<br><br>

<input type="number" step="0.01"
name="price"
value="<?= $product['price'] ?>">

<br><br>

<input type="file" name="image">

<br><br>

<button type="submit">Update Product</button>

</form>