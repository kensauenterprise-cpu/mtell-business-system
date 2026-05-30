<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

header('Content-Type: application/json');

$year = $_GET['year'] ?? date('Y');

$data = [];
$labels = [];
$sales = [];
$expenses = [];
$profit = [];

for ($m = 1; $m <= 12; $m++) {

    $month = str_pad($m, 2, '0', STR_PAD_LEFT);

    // SALES (Revenue)
    $rev = $conn->query("
        SELECT SUM(credit - debit) as total
        FROM journal_entries je
        JOIN chart_of_accounts coa ON je.account_id = coa.id
        WHERE coa.account_type='Revenue'
        AND DATE_FORMAT(entry_date, '%Y-%m') = '$year-$month'
    ")->fetch_assoc()['total'] ?? 0;

    // EXPENSES
    $exp = $conn->query("
        SELECT SUM(debit - credit) as total
        FROM journal_entries je
        JOIN chart_of_accounts coa ON je.account_id = coa.id
        WHERE coa.account_type='Expense'
        AND DATE_FORMAT(entry_date, '%Y-%m') = '$year-$month'
    ")->fetch_assoc()['total'] ?? 0;

    $labels[] = date("M", mktime(0,0,0,$m,1));
    $sales[] = (float)$rev;
    $expenses[] = (float)$exp;
    $profit[] = (float)($rev - $exp);
}

echo json_encode([
    "labels" => $labels,
    "sales" => $sales,
    "expenses" => $expenses,
    "profit" => $profit
]);