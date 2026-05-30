<?php
// ==========================
// 🔐 SECURITY (IT ONLY)
// ==========================
if (!in_array($_SESSION['role'] ?? '', ['it','super_admin'])) {
    die("<p style='color:red'>⛔ Access denied</p>");
}

$logFile = $_SERVER['DOCUMENT_ROOT'].'/infinity/logs/error.log';

// ==========================
// CLEAR LOGS
// ==========================
$message = "";

if (isset($_POST['clear'])) {
    file_put_contents($logFile, "");
    $message = "<p style='color:orange'>🧹 Logs cleared</p>";
}

// ==========================
// READ LOGS (SAFE)
// ==========================
$content = "No logs found.";

if (file_exists($logFile)) {

    $size = filesize($logFile);

    // limit large files (1MB max read)
    if ($size > 1024 * 1024) {
        $content = "⚠️ Log file too large. Showing last part...\n\n";
        $content .= file_get_contents($logFile, false, null, $size - 1024 * 200);
    } else {
        $content = file_get_contents($logFile);
    }
}
?>

<h2>📜 System Logs</h2>

<?= $message ?>

<form method="post" style="margin-bottom:10px;">
<button name="clear" onclick="return confirm('Clear all logs?')">🧹 Clear Logs</button>
</form>

<pre style="
background:black;
color:#00ff88;
padding:15px;
height:400px;
overflow:auto;
font-size:13px;
border-radius:8px;
">
<?= htmlspecialchars($content) ?>
</pre>