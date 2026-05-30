<?php
// ==========================
// 🔐 INIT
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

header('Content-Type: application/json');

// ==========================
// 📊 FETCH TRIAL BALANCE
// ==========================
$sql = "
SELECT 
    coa.account_code,
    coa.account_name,
    coa.account_type,
    COALESCE(SUM(j.debit),0) as total_debit,
    COALESCE(SUM(j.credit),0) as total_credit
FROM chart_of_accounts coa
LEFT JOIN journal_entries j 
    ON coa.id = j.account_id
GROUP BY coa.id
ORDER BY coa.account_code ASC
";

$result = $conn->query($sql);

$data = [];
$totalDebit = 0;
$totalCredit = 0;

// ==========================
// 🧮 CALCULATE BALANCES
// ==========================
while($row = $result->fetch_assoc()){

    $balance = $row['total_debit'] - $row['total_credit'];

    if($balance >= 0){
        $debit = $balance;
        $credit = 0;
    } else {
        $debit = 0;
        $credit = abs($balance);
    }

    $totalDebit += $debit;
    $totalCredit += $credit;

    $data[] = [
        'code'   => $row['account_code'],
        'name'   => $row['account_name'],
        'type'   => $row['account_type'],
        'debit'  => round($debit,2),
        'credit' => round($credit,2)
    ];
}

// ==========================
// 📤 RESPONSE
// ==========================
echo json_encode([
    'accounts' => $data,
    'total_debit' => round($totalDebit,2),
    'total_credit' => round($totalCredit,2),
    'balanced' => round($totalDebit,2) == round($totalCredit,2)
]);