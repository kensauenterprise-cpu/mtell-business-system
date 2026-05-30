<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT * FROM expenses
    WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$expense = $stmt->get_result()->fetch_assoc();

if(!$expense){
    die("Expense not found");
}

if(isset($_POST['update'])){

    $description = trim($_POST['description']);
    $amount = (float)$_POST['amount'];
    $category_id = (int)$_POST['category_id'];
    $expense_date = $_POST['expense_date'];

    $stmt = $conn->prepare("
        UPDATE expenses
        SET
            description=?,
            category_id=?,
            amount=?,
            expense_date=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "sidsi",
        $description,
        $category_id,
        $amount,
        $expense_date,
        $id
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

<h2>Edit Expense</h2>

<form method="POST">

    <input type="text"
           name="description"
           value="<?= htmlspecialchars($expense['description']) ?>"
           required>

    <input type="number"
           step="0.01"
           name="amount"
           value="<?= $expense['amount'] ?>"
           required>

    <input type="date"
           name="expense_date"
           value="<?= $expense['expense_date'] ?>"
           required>

    <select name="category_id">

        <option value="0">No Category</option>

        <?php while($c = $categories->fetch_assoc()): ?>

            <option value="<?= $c['id'] ?>"
                <?= $expense['category_id'] == $c['id'] ? 'selected' : '' ?>>

                <?= htmlspecialchars($c['name']) ?>

            </option>

        <?php endwhile; ?>

    </select>

    <button type="submit" name="update">
        Update Expense
    </button>

</form>