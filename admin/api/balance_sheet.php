<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

header('Content-Type: application/json');

// Assets
$assets = $conn->query("
SELECT SUM(debit - credit) as total 
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type = 'Asset'
")->fetch_assoc()['total'] ?? 0;

// Liabilities
$liabilities = $conn->query("
SELECT SUM(credit - debit) as total 
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type = 'Liability'
")->fetch_assoc()['total'] ?? 0;

// Equity
$equity = $conn->query("
SELECT SUM(credit - debit) as total 
FROM journal_entries je
JOIN chart_of_accounts coa ON je.account_id = coa.id
WHERE coa.account_type = 'Equity'
")->fetch_assoc()['total'] ?? 0;

echo json_encode([
    "assets" => $assets,
    "liabilities" => $liabilities,
    "equity" => $equity
]);