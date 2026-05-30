<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

header('Content-Type: application/json');

// Revenue
$revenue = $conn->query("
SELECT SUM(credit - debit) as total 
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type = 'Revenue'
")->fetch_assoc()['total'] ?? 0;

// Expenses
$expenses = $conn->query("
SELECT SUM(debit - credit) as total 
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type = 'Expense'
")->fetch_assoc()['total'] ?? 0;

$profit = $revenue - $expenses;

echo json_encode([
    "revenue" => $revenue,
    "expenses" => $expenses,
    "net_profit" => $profit
]);