<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Dompdf\Dompdf;

$order_id = $_GET['order_id'] ?? 0;

// FETCH DATA
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");

// BUILD HTML
$html = '
<h3>Infinity Store</h3>
<p>Receipt #'.$order_id.'</p>
<hr>';

while($i = $items->fetch_assoc()){
    $html .= $i['product_name'].' x'.$i['quantity'].' - '.$i['total'].'<br>';
}

$html .= '<hr>';
$html .= '<b>Total: KES '.$order['total_amount'].'</b>';

// GENERATE PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("receipt_$order_id.pdf");