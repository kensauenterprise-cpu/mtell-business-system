<?php
session_start();
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="stkpush_queries.csv"');

$pdo = new PDO('mysql:host=localhost;dbname=if0_39282158_business', 'your_db_user', 'your_db_pass');

$phone = $_GET['phone'] ?? '';
$from  = $_GET['from'] ?? '';
$to    = $_GET['to'] ?? '';
$code  = $_GET['code'] ?? '';

$sql = "SELECT * FROM stkpush_queries WHERE 1";
$params = [];

if ($phone) {
    $sql .= " AND phone LIKE ?";
    $params[] = "%$phone%";
}
if ($code !== '') {
    $sql .= " AND result_code = ?";
    $params[] = $code;
}
if ($from && $to) {
    $sql .= " AND query_time BETWEEN ? AND ?";
    $params[] = $from . " 00:00:00";
    $params[] = $to . " 23:59:59";
}

$sql .= " ORDER BY query_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$output = fopen('php://output', 'w');
fputcsv($output, ['Phone', 'Checkout ID', 'Result Code', 'Description', 'Timestamp']);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['phone'],
        $row['checkout_id'],
        $row['result_code'],
        $row['result_desc'],
        $row['query_time']
    ]);
}
fclose($output);
?>
