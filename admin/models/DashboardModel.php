<?php
// DashboardModel.php — central place for dashboard queries

function getCallbackSummary($mysqli) {
    $sql = "SELECT 
                COUNT(*) AS total_pushes,
                SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) AS successful_callbacks,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) AS failed_callbacks,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) AS pending_callbacks,
                ROUND(
                    (SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) * 100.0) / COUNT(*),
                    2
                ) AS success_rate
            FROM orders
            WHERE DATE(trans_date) = CURDATE()";
    return $mysqli->query($sql)->fetch_assoc();
}

function getFailureBreakdown($mysqli) {
    $sql = "SELECT error_desc, COUNT(*) AS fail_count
            FROM orders
            WHERE status = 'FAILED'
            GROUP BY error_desc
            ORDER BY fail_count DESC";
    return $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getTrendData($mysqli) {
    $sql = "SELECT 
                DATE(trans_date) AS day,
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) AS successful,
                ROUND(
                    (SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) * 100.0) / COUNT(*),
                    2
                ) AS success_rate
            FROM orders
            WHERE trans_date >= CURDATE() - INTERVAL 7 DAY
            GROUP BY day
            ORDER BY day ASC";
    return $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getRecentTransactions($mysqli) {
    $sql = "SELECT 
                order_id,
                checkout_request_id,
                status,
                receipt,
                amount,
                phone,
                trans_date,
                error_desc
            FROM orders
            ORDER BY trans_date DESC
            LIMIT 20";
    return $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>
