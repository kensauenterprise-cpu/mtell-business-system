<div class="card">
  <h4>🔐 Access Token Status</h4>
  <ul style="list-style:none; padding-left:0;">
    <?php
    $tokenPath = __DIR__ . '/../../config/access_token.json';

    if (!file_exists($tokenPath)) {
        echo "<li><span style='color:red;'>❌ Token file missing</span></li>";
    } else {
        $data      = json_decode(file_get_contents($tokenPath), true);
        $expiresAt = $data['expires_at'] ?? 0;
        $token     = $data['access_token'] ?? '';

        $remaining = $expiresAt - time();   
        $age       = 3600 - $remaining;

        if ($remaining <= 0) {
            echo "<li><strong>Status:</strong> <span style='color:red;'>❌ Expired</span></li>";
        } else {
            echo "<li><strong>Status:</strong> <span style='color:green;'>✅ Fresh</span></li>";
            echo "<li><strong>⏱ Age:</strong> " . floor($age / 60) . " min</li>";
            echo "<li><strong>🕒 Expires in:</strong> " . floor($remaining / 60) . " min</li>";
        }

        echo "<li><strong>🔑 Token Preview:</strong> <code>" . substr($token, 0, 10) . "...</code></li>";
    }
    ?>
  </ul>
</div>
