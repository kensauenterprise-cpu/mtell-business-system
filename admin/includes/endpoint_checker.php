<?php
function checkEndpoint($url, $label = '') {
    $cacheFile = __DIR__ . '/../../logs/endpoint_cache.json';
    $cacheTTL = 300; // 5 minutes

    // Load cache if available
    $cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];

    // Use cached result if still fresh
    if (isset($cache[$url]) && (time() - $cache[$url]['timestamp']) < $cacheTTL) {
        return $cache[$url]['status'];
    }

    // ✅ Log retry attempt
    $retryLogPath = __DIR__ . '/../../logs/retry_log.txt';
    error_log("Retry check for [$label]: $url", 3, $retryLogPath);

    // Ping endpoint
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = ($httpCode >= 200 && $httpCode < 400) ? '✅ Reachable' : '❌ Unreachable';

    // Log failures
    if ($status === '❌ Unreachable') {
        $errorLogPath = __DIR__ . '/../../logs/error_log.txt';
        error_log("[$label] Endpoint unreachable: $url", 3, $errorLogPath);
    }

    // Update cache
    $cache[$url] = [
        'status' => $status,
        'timestamp' => time()
    ];
    file_put_contents($cacheFile, json_encode($cache));

    return $status;
}
