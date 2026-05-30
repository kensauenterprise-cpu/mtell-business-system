<?php
// token_utils.php

// Include access token config (contains $access_token and $expires_at)
include_once __DIR__ . '/../../config/access_token.php';

function getAccessTokenStatus() {
    // If access_token.php defines $expires_at, use it directly
    if (isset($expires_at)) {
        $now = time();
        $expires = strtotime($expires_at);

        if ($expires < $now) {
            return "<span style='color:red;'>Expired</span>";
        }

        $remaining = $expires - $now;
        return "<span style='color:green;'>Valid – expires in " . gmdate("H:i:s", $remaining) . "</span>";
    }

    // Fallback: check token_status.json if $expires_at is not set
    $tokenFile = __DIR__ . '/../logs/token_status.json';

    if (!file_exists($tokenFile)) {
        return "<span style='color:red;'>Token file missing</span>";
    }

    $data = json_decode(file_get_contents($tokenFile), true);

    if (!$data || !isset($data['access_token']) || !isset($data['expires_at'])) {
        return "<span style='color:orange;'>Invalid token data</span>";
    }

    $now = time();
    $expires = strtotime($data['expires_at']);

    if ($expires < $now) {
        return "<span style='color:red;'>Expired</span>";
    }

    $remaining = $expires - $now;
    return "<span style='color:green;'>Valid – expires in " . gmdate("H:i:s", $remaining) . "</span>";
}
