<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_POST['add_category'])){

    $name = trim($_POST['name']);

    if($name != ''){

        $stmt = $conn->prepare("
            INSERT INTO categories(name)
            VALUES(?)
        ");

        $stmt->bind_param("s",$name);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ?tab=categories");
    exit;
}

$categories = $conn->query("
    SELECT *
    FROM categories
    ORDER BY name ASC
");
?>

<h2>📂 Categories</h2>

<form method="POST">

    <input
        type="text"
        name="name"
        placeholder="Category Name"
        required
    >

    <button
        type="submit"
        name="add_category"
    >
        Add Category
    </button>

</form>

<br>

<table border="1" width="100%">

<tr>
    <th>ID</th>
    <th>Name</th>
</tr>

<?php while($row = $categories->fetch_assoc()): ?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
</tr>

<?php endwhile; ?>

</table>