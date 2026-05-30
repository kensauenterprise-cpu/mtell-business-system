// kra_etims.php
include 'db.php';

function sendToKRA($payload) {
    $ch = curl_init("https://etims.kra.go.ke/api/invoices"); // Example endpoint
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer YOUR_API_TOKEN'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    return json_decode($response, true);
}

$invoiceId = $_POST['invoice_id'];
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id=?");
$stmt->execute([$invoiceId]);
$invoice = $stmt->fetch();

$payload = [
  "invoice_number" => $invoice['invoice_number'],
  "amount" => $invoice['total_amount'],
  "customer" => "Customer Name", // fetch from orders table
  "date" => date('Y-m-d')
];

$response = sendToKRA($payload);
$ack = $response['ack_number'];

$pdo->prepare("UPDATE invoices SET kra_ack=? WHERE id=?")->execute([$ack, $invoiceId]);

echo "Invoice pushed to KRA eTIMS successfully!";
