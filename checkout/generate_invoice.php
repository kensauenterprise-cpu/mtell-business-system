<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/vendor/autoload.php';

use Dompdf\Dompdf;

// Disable errors in output
ini_set('display_errors', 0);
error_reporting(0);

// =========================
// VALIDATE ORDER ID
// =========================
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid Order ID");
}

$order_id = intval($_GET['order_id']);

// =========================
// FETCH ORDER
// =========================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// =========================
// FETCH ITEMS
// =========================
$items_stmt = $conn->prepare("
    SELECT p.name, oi.quantity, oi.price 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();

// =========================
// BUILD HTML
// =========================
$html = "
<h2 style='text-align:center;'>INVOICE</h2>

<p><strong>Order ID:</strong> {$order['id']}</p>
<p><strong>Name:</strong> {$order['customer_name']}</p>
<p><strong>Phone:</strong> {$order['phone']}</p>
<p><strong>Address:</strong> {$order['address']}</p>

<table border='1' width='100%' cellspacing='0' cellpadding='8'>
<tr>
<th>Item</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
</tr>
";

$total = 0;

while($item = $items->fetch_assoc()){
    $line_total = $item['quantity'] * $item['price'];
    $total += $line_total;

    $html .= "<tr>
        <td>{$item['name']}</td>
        <td>{$item['quantity']}</td>
        <td>KES ".number_format($item['price'],2)."</td>
        <td>KES ".number_format($line_total,2)."</td>
    </tr>";
}

$html .= "</table>
<h3>Total: KES ".number_format($total,2)."</h3>
<p>Thank you for your order.</p>
";

// =========================
// GENERATE PDF
// =========================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// DOWNLOAD PDF
$dompdf->stream("invoice_$order_id.pdf", ["Attachment" => true]);
exit;