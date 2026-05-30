<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

header('Content-Type: application/json');

$alerts = [];

// Check expenses > revenue (last 7 days)
$rev = $conn->query("
SELECT SUM(credit - debit) as total
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type='Revenue'
AND entry_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetch_assoc()['total'] ?? 0;

$exp = $conn->query("
SELECT SUM(debit - credit) as total
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type='Expense'
AND entry_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetch_assoc()['total'] ?? 0;

if ($exp > $rev) {
    $alerts[] = "⚠️ Expenses exceeded revenue in last 7 days";
}

// Low sales alert
$sales = $conn->query("
SELECT COUNT(*) as total FROM orders
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
")->fetch_assoc()['total'] ?? 0;

if ($sales < 5) {
    $alerts[] = "⚠️ Low sales today";
}

echo json_encode($alerts);