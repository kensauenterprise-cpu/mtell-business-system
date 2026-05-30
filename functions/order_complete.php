<?php

function postJournalEntry($conn, $description, $accountCode, $debit, $credit) {
    $sql = "INSERT INTO journal_entries (entry_date, description, account_id, debit, credit)
            VALUES (NOW(), ?, (SELECT id FROM chart_of_accounts WHERE account_code=?), ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidd", $description, $accountCode, $debit, $credit);
    $stmt->execute();
    $stmt->close();
}

function order_complete($orderId, $paymentAmount)
{
    // Ensure DB connection exists
    if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof mysqli)) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/config/functions.php';
    }

    $conn = $GLOBALS['conn'];

    // ---- Calculate totals for Sales and COGS ----
    $orderQuery = "
        SELECT oi.quantity, p.price, p.buying_price
        FROM order_items oi
        INNER JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalSales = 0;
    $totalCOGS  = 0;

    while ($item = $result->fetch_assoc()) {
        $totalSales += $item['quantity'] * $item['price'];
        $totalCOGS  += $item['quantity'] * $item['buying_price'];
    }
    $stmt->close();

    // ---- Post journal entries ----
    $desc = "Sale Order #$orderId";

    postJournalEntry($conn, $desc, "1100", $totalSales, 0.00);
    postJournalEntry($conn, $desc, "4000", 0.00, $totalSales);
    postJournalEntry($conn, $desc, "5000", $totalCOGS, 0.00);
    postJournalEntry($conn, $desc, "1200", 0.00, $totalCOGS);

    // ---- Payment posting ----
    if ($paymentAmount) {
        $descPay = "Payment for Order #$orderId";
        postJournalEntry($conn, $descPay, "1000", $paymentAmount, 0.00);
        postJournalEntry($conn, $descPay, "1100", 0.00, $paymentAmount);
    }

    return true;
}
