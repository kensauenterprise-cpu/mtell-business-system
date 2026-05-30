<?php

function classifyLog($details) {
    if (stripos($details, '"ResponseCode":0') !== false || stripos($details, '"ResultCode":0') !== false) {
        return '✅ Success';
    } elseif (
        stripos($details, 'error') !== false ||
        stripos($details, 'failed') !== false ||
        stripos($details, '"ResultCode":1') !== false
    ) {
        return '❌ Error';
    }
    return '🔄 Pending';
}

function parseLogFile($path, $label, $search = '', $filter = 'all') {
    $entries = [];

    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($search !== '' && stripos($line, $search) === false) continue;

            $pos = strpos($line, ' - ');
            $timestamp = $pos !== false ? substr($line, 0, $pos) : '';
            $details   = $pos !== false ? substr($line, $pos + 3) : $line;
            $status    = classifyLog($details);

            if ($filter === 'success' && $status !== '✅ Success') continue;
            if ($filter === 'error' && $status !== '❌ Error') continue;

            $entries[] = [$label, $timestamp, $status, $details];
        }
    }

    return $entries;
}

function matchStkPushWithCallback($pushEntries, $callbackPath) {
    $matched = [];

    // Build a lookup table from callback log
    $callbackMap = [];
    if (file_exists($callbackPath)) {
        $lines = file($callbackPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match('/"CheckoutRequestID":"([^"]+)".*?"ResultCode":(\d+).*?"ResultDesc":"([^"]+)".*?"MpesaReceiptNumber":"([^"]+)?"/', $line, $matches)) {
                $checkoutID = $matches[1];
                $resultCode = $matches[2];
                $resultDesc = $matches[3];
                $receipt = $matches[4] ?? 'N/A';

                $status = $resultCode == '0' ? '✅ Success' : '❌ Error';
                $callbackMap[$checkoutID] = [
                    'status' => $status,
                    'receipt' => $receipt,
                    'desc' => $resultDesc
                ];
            }
        }
    }

    // Update STK Push entries with matched status and extra info
    foreach ($pushEntries as [$label, $timestamp, $status, $details]) {
        if (preg_match('/"CheckoutRequestID":"([^"]+)"/', $details, $match)) {
            $checkoutID = $match[1];
            if (isset($callbackMap[$checkoutID])) {
                $status = $callbackMap[$checkoutID]['status'];
                $details .= " | Receipt: {$callbackMap[$checkoutID]['receipt']} | ResultDesc: {$callbackMap[$checkoutID]['desc']}";
            }
        }
        $matched[] = [$label, $timestamp, $status, $details];
    }

    return $matched;
}
