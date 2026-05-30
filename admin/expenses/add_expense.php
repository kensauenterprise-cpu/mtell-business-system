<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

if(isset($_POST['save'])){

    $description = trim($_POST['description']);
    $amount = (float)$_POST['amount'];
    $category_id = (int)$_POST['category_id'];
    $expense_date = $_POST['expense_date'];

    $stmt = $conn->prepare("
        INSERT INTO expenses
        (description, category_id, amount, expense_date)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sids",
        $description,
        $category_id,
        $amount,
        $expense_date
    );

    $stmt->execute();

    header("Location: expenses.php");
    exit;
}

$categories = $conn->query("
    SELECT * FROM expense_categories
    ORDER BY name ASC
");
?>

<h2>Add Expense</h2>

<form method="POST">

    <input type="text"
           name="description"
           placeholder="Description"
           required>

    <input type="number"
           step="0.01"
           name="amount"
           placeholder="Amount"
           required>

    <input type="date"
           name="expense_date"
           value="<?= date('Y-m-d') ?>"
           required>

    <select name="category_id">

        <option value="0">No Category</option>

        <?php while($c = $categories->fetch_assoc()): ?>

            <option value="<?= $c['id'] ?>">
                <?= htmlspecialchars($c['name']) ?>
            </option>

        <?php endwhile; ?>

    </select>

    <button type="submit" name="save">
        Save Expense
    </button>

</form>