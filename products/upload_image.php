<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(isset($_POST['upload_image'])){

    $product_id = (int)$_POST['product_id'];

    if(!empty($_FILES['image']['name'])){

        $allowed = ['jpg','jpeg','png','webp'];

        $ext = strtolower(
            pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            )
        );

        if(in_array($ext,$allowed)){

            $uploadDir =
            $_SERVER['DOCUMENT_ROOT'].
            '/infinity/uploads/';

            if(!is_dir($uploadDir)){
                mkdir($uploadDir,0777,true);
            }

            $filename =
            time().'_'.basename($_FILES['image']['name']);

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir.$filename
            );

            $stmt = $conn->prepare("
                UPDATE products
                SET image=?
                WHERE id=?
            ");

            $stmt->bind_param(
                "si",
                $filename,
                $product_id
            );

            $stmt->execute();
            $stmt->close();

            echo "<div style='color:green'>
            Image Uploaded Successfully
            </div>";
        }
    }
}

$products = $conn->query("
    SELECT id,name
    FROM products
    ORDER BY name ASC
");
?>

<h2>🖼 Upload Product Image</h2>

<form
method="POST"
enctype="multipart/form-data"
>

<select
name="product_id"
required
>

<?php while($row = $products->fetch_assoc()): ?>

<option value="<?= $row['id'] ?>">
    <?= htmlspecialchars($row['name']) ?>
</option>

<?php endwhile; ?>

</select>

<input
type="file"
name="image"
required
>

<button
type="submit"
name="upload_image"
>
Upload Image
</button>

</form>