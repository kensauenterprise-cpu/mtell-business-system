<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ USE MAIN DB
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ==========================
// ✅ DB CHECK
// ==========================
if (!isset($conn) || !$conn) {
    die("Database connection missing");
}

// ==========================
// 🏢 BRANCH
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// QUERY
// ==========================
$sql = "
SELECT 
    a.type AS account_type,
    a.account_code,
    a.account_name,
    SUM(COALESCE(je.debit,0)) AS total_debit,
    SUM(COALESCE(je.credit,0)) AS total_credit
FROM journal_entries je
INNER JOIN chart_of_accounts a 
    ON je.account_id = a.id
WHERE je.branch_id = ?
GROUP BY 
    a.type,
    a.account_code,
    a.account_name
ORDER BY 
    a.type,
    a.account_code
";

// ==========================
// PREPARE
// ==========================
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare Failed: " . $conn->error);
}

$stmt->bind_param("i", $branch_id);

if (!$stmt->execute()) {
    die("SQL Execute Failed: " . $stmt->error);
}

$result = $stmt->get_result();

if (!$result) {
    die("Failed to fetch balance sheet data");
}

// ==========================
// ORGANIZE
// ==========================
$assets = [];
$liabilities = [];
$equity = [];

$totalAssets = 0;
$totalLiabilities = 0;
$totalEquity = 0;

while ($row = $result->fetch_assoc()) {

    $total_debit = (float)($row['total_debit'] ?? 0);
    $total_credit = (float)($row['total_credit'] ?? 0);

    // ==========================
    // ASSETS
    // ==========================
    if (strtolower($row['account_type']) === 'asset') {

        $balance = $total_debit - $total_credit;

        $row['balance'] = $balance;

        $assets[] = $row;

        $totalAssets += $balance;
    }

    // ==========================
    // LIABILITIES
    // ==========================
    if (strtolower($row['account_type']) === 'liability') {

        $balance = $total_credit - $total_debit;

        $row['balance'] = $balance;

        $liabilities[] = $row;

        $totalLiabilities += $balance;
    }

    // ==========================
    // EQUITY
    // ==========================
    if (strtolower($row['account_type']) === 'equity') {

        $balance = $total_credit - $total_debit;

        $row['balance'] = $balance;

        $equity[] = $row;

        $totalEquity += $balance;
    }
}

$stmt->close();

// ==========================
// UI
// ==========================
function renderSection($title, $rows) {

    echo "
    <div style='margin-bottom:30px;'>
    ";

    echo "
    <h3 style='
        background:#0f172a;
        color:white;
        padding:10px;
        border-radius:6px;
    '>
        $title
    </h3>
    ";

    echo "
    <table 
        border='1'
        cellpadding='8'
        cellspacing='0'
        width='100%'
        style='
            border-collapse:collapse;
            background:white;
        '
    >
    ";

    echo "
    <tr style='background:#f1f5f9;'>
        <th>Code</th>
        <th>Name</th>
        <th>Balance (KES)</th>
    </tr>
    ";

    if (empty($rows)) {

        echo "
        <tr>
            <td colspan='3' style='text-align:center;color:#64748b;'>
                No records found
            </td>
        </tr>
        ";

    } else {

        foreach ($rows as $r) {

            echo "<tr>";

            echo "
            <td>
                ".htmlspecialchars($r['account_code'])."
            </td>
            ";

            echo "
            <td>
                ".htmlspecialchars($r['account_name'])."
            </td>
            ";

            echo "
            <td style='text-align:right;'>
                ".number_format((float)$r['balance'], 2)."
            </td>
            ";

            echo "</tr>";
        }
    }

    echo "</table>";

    echo "</div>";
}

// ==========================
// PAGE TITLE
// ==========================
echo "
<h2 style='margin-bottom:20px;'>
    📊 Balance Sheet
</h2>
";

// ==========================
// ASSETS
// ==========================
renderSection("Assets", $assets);

echo "
<p style='
    font-size:18px;
    font-weight:bold;
    color:#16a34a;
'>
    Total Assets:
    KES ".number_format($totalAssets, 2)."
</p>
";

// ==========================
// LIABILITIES
// ==========================
renderSection("Liabilities", $liabilities);

echo "
<p style='
    font-size:18px;
    font-weight:bold;
    color:#dc2626;
'>
    Total Liabilities:
    KES ".number_format($totalLiabilities, 2)."
</p>
";

// ==========================
// EQUITY
// ==========================
renderSection("Equity", $equity);

echo "
<p style='
    font-size:18px;
    font-weight:bold;
    color:#2563eb;
'>
    Total Equity:
    KES ".number_format($totalEquity, 2)."
</p>
";

// ==========================
// TOTAL LIABILITIES + EQUITY
// ==========================
$totalLiabilityEquity = $totalLiabilities + $totalEquity;

echo "
<p style='
    font-size:20px;
    font-weight:bold;
    color:#7c3aed;
'>
    Total Liabilities + Equity:
    KES ".number_format($totalLiabilityEquity, 2)."
</p>
";

// ==========================
// CHECK
// ==========================
echo "
<hr style='margin:30px 0;'>
";

if (
    round($totalAssets, 2)
    ===
    round($totalLiabilityEquity, 2)
) {

    echo "
    <div style='
        background:#dcfce7;
        color:#166534;
        padding:15px;
        border-radius:6px;
        font-weight:bold;
    '>
        ✅ Balance Sheet is Balanced
    </div>
    ";

} else {

    echo "
    <div style='
        background:#fee2e2;
        color:#991b1b;
        padding:15px;
        border-radius:6px;
        font-weight:bold;
    '>
        ❌ Balance Sheet is NOT Balanced
    </div>
    ";

    echo "
    <p>
        Difference:
        <b>KES ".
        number_format(
            abs(
                $totalAssets -
                $totalLiabilityEquity
            ),
            2
        ).
        "</b>
    </p>
    ";
}

?>