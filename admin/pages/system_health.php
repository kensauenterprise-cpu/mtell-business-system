<?php
// ===============================
// 🌍 Environment Setup
// ===============================
date_default_timezone_set('Africa/Nairobi');
require_once __DIR__ . '/../../config/functions.php';
include_once(__DIR__ . '/../../config/daraja_auth.php');

// 📄 Log timezone drift if detected
function logTimezoneDrift($currentTZ, $expectedTZ = 'Africa/Nairobi') {
    if ($currentTZ !== $expectedTZ) {
        $logLine = date('Y-m-d H:i:s') . ", Drift detected: $currentTZ → $expectedTZ\n";
        file_put_contents(__DIR__ . '/../../logs/timezone_drift.log', $logLine, FILE_APPEND);
    }
}

// ⏱️ Lightweight NTP time fetcher (UDP port 123)
function getNtpTime() {
    $server = 'time.google.com';
    $socket = @fsockopen("udp://$server", 123, $errNo, $errStr, 1);
    if (!$socket) return false;

    $packet = chr(0x1B) . str_repeat(chr(0), 47);
    fwrite($socket, $packet);
    $response = fread($socket, 48);
    fclose($socket);

    if (strlen($response) < 48) return false;

    $timestamp = unpack("N12", $response)[9];
    $timestamp -= 2208988800; // Convert NTP to Unix epoch
    return $timestamp;
}

// 🩺 Main system health checker
function checkSystemHealth() {
    $results = [];

    // 1. Timezone check
    $timezone = date_default_timezone_get();
    logTimezoneDrift($timezone);
    $results['Timezone'] = ($timezone === 'Africa/Nairobi')
        ? '✅ Africa/Nairobi'
        : "⚠️ Drifted: $timezone";

    // 2. Clock sync check
    $localTime = time();
    $ntpTime = getNtpTime();
    if ($ntpTime) {
        $offset = abs($localTime - $ntpTime);
        $results['Clock Sync'] = ($offset < 10)
            ? '✅ Synced (±' . $offset . 's)'
            : '⚠️ Offset: ' . $offset . 's';
    } else {
        $results['Clock Sync'] = '⚠️ NTP check unavailable';
    }

    // 3. Access token check via Daraja
    $tokenData = getAccessToken();
    if (is_array($tokenData) && isset($tokenData['status'])) {
        if ($tokenData['status'] === 'ok' || $tokenData['status'] === 'regenerated') {
            $remaining = $tokenData['expires_at'] - time();
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60;
            $results['Access Token'] = "✅ {$tokenData['message']} — expires in {$minutes}m {$seconds}s";
        } else {
            $results['Access Token'] = "❌ {$tokenData['message']}";
        }
    } else {
        $results['Access Token'] = "❌ Token check failed — unexpected response";
    }

    // 4. Endpoint health check
    $endpoints = [
        "Token URL"    => "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials",
        "STK Push URL" => "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest",
        "Query URL"    => "https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query"
    ];

    foreach ($endpoints as $label => $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $success = curl_exec($ch) !== false;
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = $success && $httpCode == 200 ? "✅ Healthy" : "❌ Unreachable ($httpCode)";
        $results[$label] = $status;

        // ✅ Log to endpoint_health.log
        write_log("endpoint_health.log", "$label → $status");
    }

    return $results;
}

// 🔍 Display results
$health = checkSystemHealth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Health Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        .health-check { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .health-check h2 { margin-top: 0; }
        .health-check ul { list-style: none; padding-left: 0; }
        .health-check li { margin-bottom: 10px; }
        .dashboard-link { display:inline-block; margin-top:20px; padding:10px 15px; background:#007bff; color:white; text-decoration:none; border-radius:5px; }
    </style>
</head>
<body>
    <div class="health-check">
        <h2>🩺 Server Health Check</h2>
        <ul>
            <?php foreach ($health as $key => $status): ?>
                <li><strong><?= htmlspecialchars($key) ?>:</strong> <?= htmlspecialchars($status) ?></li>
            <?php endforeach; ?>
        </ul>
        <a href="/infinity/admin/pages/dashboard.php" class="dashboard-link">🏠 Back to Dashboard</a>
    </div>
</body>
</html>
