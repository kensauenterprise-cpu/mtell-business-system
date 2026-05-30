<?php
session_start();

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/infinity/admin/includes/db.php';

// Fetch categories for the form
$categoryStmt = $conn->query("SELECT id, name FROM categories");
$categories = $categoryStmt->fetch_all(MYSQLI_ASSOC);

// Handle file upload
$targetDir = $_SERVER['DOCUMENT_ROOT'] . '/infinity/uploads/';
$imageName = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $created_at = date('Y-m-d H:i:s');

    // Validate image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExt, $allowed)) {
            $imageName = time() . '_' . preg_replace("/[^a-zA-Z0-9]/", "_", $fileName);
            move_uploaded_file($fileTmp, $targetDir . $imageName);
        } else {
            $error = "Invalid image format.";
        }
    } else {
        $error = "Image upload failed.";
    }

    // Insert product
    if (empty($error)) {
        // 🧠 Get category name from category_id
        $catNameStmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $catNameStmt->bind_param("i", $category_id);
        $catNameStmt->execute();
        $catNameResult = $catNameStmt->get_result();
        $catRow = $catNameResult->fetch_assoc();
        $category_name = $catRow['name'] ?? '';
        $catNameStmt->close();

        // ✅ Insert with category name
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, created_at, category_id, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsssis", $name, $price, $description, $imageName, $created_at, $category_id, $category_name);

        if ($stmt->execute()) {
           header("Location: /infinity/admin/pages/dashboard.php");
            exit;
        } else {
            $error = "Failed to insert product: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Product</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <h2 style="text-align:center;">Add New Product</h2>

  <?php if (!empty($error)): ?>
    <div class="error" style="text-align:center; color:red;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" style="max-width:500px;margin:40px auto;">
    <label>Category:</label><br>
    <select name="category_id" required>
      <option value="">-- Select Category --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Product Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Price (Ksh):</label><br>
    <input type="number" name="price" step="0.01" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="4" required></textarea><br><br>

    <label>Upload Image:</label><br>
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required><br><br>

    <button type="submit">Add Product</button>
  </form>
</body>
</html>
