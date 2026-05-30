<?php
header('Content-Type: application/json');

$logPath = __DIR__ . '/../../logs/error_log.txt';
$search = $_GET['search'] ?? '';
$export = isset($_GET['export']);

if (!file_exists($logPath)) {
    echo json_encode(['logs' => '⚠️ Log file not found.']);
    exit;
}

$lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$filtered = [];

foreach ($lines as $line) {
    if ($search === '' || stripos($line, $search) !== false) {
        $filtered[] = $line;
    }
}

if ($export) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=token_logs.csv');
    $out = fopen('php://output', 'w');
    foreach ($filtered as $line) {
        fputcsv($out, [$line]);
    }
    fclose($out);
    exit;
}

echo json_encode(['logs' => implode("\n", $filtered)]);
