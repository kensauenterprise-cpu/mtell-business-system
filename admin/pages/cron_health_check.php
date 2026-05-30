<?php
// ✅ Load utility functions
require_once $_SERVER['DOCUMENT_ROOT'] . '/infinity/config/functions.php';

// ✅ Define endpoints to monitor
$endpoints = [
    'Safaricom API'      => 'https://api.safaricom.co.ke/status',
    'M-Pesa Callback'    => 'https://mtell.kesug.com/infinity/callback.php',
    'Confirmation Page'  => 'https://mtell.kesug.com/infinity/confirmation.php'
];

// ✅ Ping each endpoint and log result
foreach ($endpoints as $label => $url) {
    $code = pingEndpoint($url);
    $status = ($code >= 200 && $code < 300) ? '✅ OK' :
              (($code >= 300 && $code < 500) ? '⚠️ Warning' : '❌ Error');
    write_log('endpoint_health.log', "$label [$url] responded with HTTP $code ($status)");
}

// ✅ Optional: Echo summary if run manually
if (php_sapi_name() !== 'cli') {
    echo "<h3>✅ Health check completed</h3>";
}
