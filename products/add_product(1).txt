<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];

    $retail_image = '';
$wholesale_image = '';
$online_image = '';

$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/infinity/uploads/products/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Retail Image
if (!empty($_FILES['retail_image']['name'])) {

    $fileName = time().'_retail_'.basename($_FILES['retail_image']['name']);

    move_uploaded_file(
        $_FILES['retail_image']['tmp_name'],
        $uploadDir.$fileName
    );

    $retail_image = '/infinity/uploads/products/'.$fileName;
}

// Wholesale Image
if (!empty($_FILES['wholesale_image']['name'])) {

    $fileName = time().'_wholesale_'.basename($_FILES['wholesale_image']['name']);

    move_uploaded_file(
        $_FILES['wholesale_image']['tmp_name'],
        $uploadDir.$fileName
    );

    $wholesale_image = '/infinity/uploads/products/'.$fileName;
}

// Online Image
if (!empty($_FILES['online_image']['name'])) {

    $fileName = time().'_online_'.basename($_FILES['online_image']['name']);

    move_uploaded_file(
        $_FILES['online_image']['tmp_name'],
        $uploadDir.$fileName
    );

    $online_image = '/infinity/uploads/products/'.$fileName;
}

    $stmt = $conn->prepare("
    INSERT INTO products
    (
        name,
        category,
        description,
        price,
        retail_image,
        wholesale_image,
        online_image
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
   $stmt->bind_param(
    "sssdsss",
    $name,
    $category,
    $description,
    $price,
    $retail_image,
    $wholesale_image,
    $online_image
);
    if ($stmt->execute()) {
        $message = "Product added successfully.";
    }
}
?>

<h2>Add Product</h2>

<?= $message ?>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="name" placeholder="Product Name" required><br><br>

    <input type="text" name="category" placeholder="Category"><br><br>

    <textarea name="description" placeholder="Description"></textarea><br><br>

    <input type="number" step="0.01" name="price" placeholder="Price"><br><br>

   <label>Retail Image</label><br>
<input type="file" name="retail_image"><br><br>

<label>Wholesale Image</label><br>
<input type="file" name="wholesale_image"><br><br>

<label>Online Image</label><br>
<input type="file" name="online_image"><br><br>

    <button type="submit">Save Product</button>

</form>