<?php
// infinity/admin/pages/excel_embed.php

session_start();

// ✅ Optional: Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Excel Viewer</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Adjust if needed -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📄 Embedded Excel Sheet</h2>
        <iframe width="100%" height="500" frameborder="0" scrolling="no"
            src="https://1drv.ms/x/c/dfc619609ba7b206/IQRlfju8X6zCT6GjqU5rWAiiAYNQW-YDYCVRmAjhItZCowU?em=2&wdAllowInteractivity=False&wdHideGridlines=True&wdHideHeaders=True&wdDownloadButton=True&wdInConfigurator=True&waccluster=GZA1&edaebp=">
        </iframe>

        <!-- ✅ Back to Dashboard Button -->
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
</body>
</html>
