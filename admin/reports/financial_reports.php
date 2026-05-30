<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// 🔐 SESSION (SAFE)
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn instanceof mysqli) {
    die("❌ Database connection failed");
}

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 📌 SUB TAB
// ==========================
$report = $_GET['report'] ?? 'dashboard';

// ==========================
// 📊 DASHBOARD STATS
// ==========================
$total_sales      = 0;
$total_orders     = 0;
$today_sales      = 0;
$total_expenses   = 0;
$total_purchases  = 0;
$net_profit       = 0;

// ==========================
// 🌍 ALL BRANCHES
// ==========================
if ($branch_id === 'all') {

    // TOTAL SALES
    $sql = "
        SELECT SUM(total) as total
        FROM sales
    ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $total_sales = (float)($row['total'] ?? 0);
    }

    // TOTAL ORDERS
    $sql = "
        SELECT COUNT(*) as total
        FROM sales
    ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $total_orders = (int)($row['total'] ?? 0);
    }

    // TODAY SALES
    $sql = "
        SELECT SUM(total) as total
        FROM sales
        WHERE DATE(created_at) = CURDATE()
    ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $today_sales = (float)($row['total'] ?? 0);
    }

    // TOTAL EXPENSES
    $sql = "
        SELECT SUM(amount) as total
        FROM expenses
    ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $total_expenses = (float)($row['total'] ?? 0);
    }

    // TOTAL PURCHASES
    $sql = "
        SELECT SUM(total_amount) as total
        FROM purchases
    ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $total_purchases = (float)($row['total'] ?? 0);
    }

} else {

    $branch_id = (int)$branch_id;

    // TOTAL SALES
    $stmt = $conn->prepare("
        SELECT SUM(total) as total
        FROM sales
        WHERE branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param("i", $branch_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $total_sales = (float)($row['total'] ?? 0);
        }

        $stmt->close();
    }

    // TOTAL ORDERS
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM sales
        WHERE branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param("i", $branch_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $total_orders = (int)($row['total'] ?? 0);
        }

        $stmt->close();
    }

    // TODAY SALES
    $stmt = $conn->prepare("
        SELECT SUM(total) as total
        FROM sales
        WHERE DATE(created_at) = CURDATE()
        AND branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param("i", $branch_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $today_sales = (float)($row['total'] ?? 0);
        }

        $stmt->close();
    }

    // TOTAL EXPENSES
    $stmt = $conn->prepare("
        SELECT SUM(amount) as total
        FROM expenses
        WHERE branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param("i", $branch_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $total_expenses = (float)($row['total'] ?? 0);
        }

        $stmt->close();
    }

    // TOTAL PURCHASES
    $stmt = $conn->prepare("
        SELECT SUM(total_amount) as total
        FROM purchases
        WHERE branch_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param("i", $branch_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $total_purchases = (float)($row['total'] ?? 0);
        }

        $stmt->close();
    }
}

// ==========================
// 📈 NET PROFIT
// ==========================
$net_profit = $total_sales - ($total_expenses + $total_purchases);

// ==========================
// 🛡 SAFE INCLUDE
// ==========================
function loadReport($file) {

    global $conn;
    global $branch_id;
    global $report;

    if (!isset($conn) || !$conn instanceof mysqli) {

        echo "
        <div style='
            background:#fff0f0;
            color:red;
            padding:15px;
            border:1px solid red;
            border-radius:6px;
            margin:10px 0;
        '>
            ❌ Database connection missing
        </div>
        ";

        return;
    }

    if (!file_exists($file)) {

        echo "
        <div style='
            background:#fff0f0;
            color:red;
            padding:15px;
            border:1px solid red;
            border-radius:6px;
            margin:10px 0;
        '>
            ❌ Report not found:
            <br>
            ".htmlspecialchars($file)."
        </div>
        ";

        return;
    }

    try {

        include $file;

    } catch (Throwable $e) {

        echo "
        <div style='
            background:#fff0f0;
            color:red;
            padding:15px;
            border:1px solid red;
            border-radius:6px;
            margin:10px 0;
        '>
            <b>❌ Report Error:</b>
            <br><br>
            ".htmlspecialchars($e->getMessage())."
        </div>
        ";
    }
}
?>

<h2>💰 Financial Reports</h2>

<!-- 🔹 SUB MENU -->
<div class="sub-nav">

    <a href="?tab=financials&report=dashboard">
        🏠 Overview
    </a>

    <a href="?tab=financials&report=sales">
        📊 Sales
    </a>

    <a href="?tab=financials&report=profit_loss">
        📈 Profit & Loss
    </a>

    <a href="?tab=financials&report=balance_sheet">
        📑 Balance Sheet
    </a>

    <a href="?tab=financials&report=cash_flow">
        💸 Cash Flow
    </a>

    <a href="?tab=financials&report=trial_balance">
        📚 Trial Balance
    </a>

    <a href="?tab=financials&report=general_ledger">
        📖 General Ledger
    </a>

    <a href="?tab=financials&report=inventory">
        📦 Inventory
    </a>

    <a href="?tab=financials&report=orders">
        🧾 Orders
    </a>

    <a href="?tab=financials&report=delivery">
        🚚 Deliveries
    </a>

    <a href="?tab=financials&report=purchases">
        🛒 Purchases
    </a>

</div>

<hr>

<?php
switch ($report) {

    case 'dashboard':
        ?>

        <div class="stats">

            <div class="card">
                <h3>💵 Total Sales</h3>
                <p>Ksh <?= number_format($total_sales, 2) ?></p>
            </div>

            <div class="card">
                <h3>📦 Total Orders</h3>
                <p><?= number_format($total_orders) ?></p>
            </div>

            <div class="card">
                <h3>📅 Today Sales</h3>
                <p>Ksh <?= number_format($today_sales, 2) ?></p>
            </div>

            <div class="card">
                <h3>💸 Expenses</h3>
                <p>Ksh <?= number_format($total_expenses, 2) ?></p>
            </div>

            <div class="card">
                <h3>🛒 Purchases</h3>
                <p>Ksh <?= number_format($total_purchases, 2) ?></p>
            </div>

            <div class="card">
                <h3>📈 Net Profit</h3>
                <p>Ksh <?= number_format($net_profit, 2) ?></p>
            </div>

        </div>

        <?php
        break;

    case 'sales':
        loadReport(__DIR__ . '/sales_report.php');
        break;

    case 'profit_loss':
        loadReport(__DIR__ . '/profit_loss.php');
        break;

    case 'balance_sheet':
        loadReport(__DIR__ . '/balance_sheet.php');
        break;

    case 'cash_flow':
        loadReport(__DIR__ . '/cash_flow.php');
        break;

    case 'trial_balance':
        loadReport(__DIR__ . '/trial_balance.php');
        break;

    case 'general_ledger':
        loadReport(__DIR__ . '/general_ledger.php');
        break;

    case 'inventory':
        loadReport(__DIR__ . '/inventory_dashboard.php');
        break;

    case 'orders':
        loadReport(__DIR__ . '/order.php');
        break;

    case 'delivery':
        loadReport(__DIR__ . '/delivery_orders.php');
        break;

    case 'purchases':
        loadReport($_SERVER['DOCUMENT_ROOT'].'/infinity/admin/purchases/purchases.php');
        break;

    default:

        echo "
        <div style='
            background:#fff;
            padding:20px;
            border-radius:8px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
        '>
            Select a report above.
        </div>
        ";
}
?>

<style>

.sub-nav{
    margin-bottom:20px;
}

.sub-nav a{
    display:inline-block;
    margin:5px;
    padding:10px 14px;
    background:#007bff;
    color:white;
    text-decoration:none;
    border-radius:5px;
    transition:0.3s;
}

.sub-nav a:hover{
    background:#0056b3;
}

.stats{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}

.card{
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 0 10px #ccc;
    flex:1;
    min-width:220px;
}

.card h3{
    margin-top:0;
    color:#333;
}

.card p{
    font-size:24px;
    font-weight:bold;
    color:#007bff;
}

</style>