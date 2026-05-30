<?php
include $_SERVER['DOCUMENT_ROOT'] . '/business/admin/includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store token
        $stmt = $conn->prepare("UPDATE admins SET reset_token = ?, token_expires = ? WHERE username = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        // Prepare reset link
        $resetLink = "http://localhost/business/admin/reset-password.php?token=$token";

        // TODO: Send $resetLink via email (for now just show it)
        $message = "Reset link: <a href='$resetLink'>$resetLink</a>";
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="post">
        <label for="email">Enter your email address:</label>
        <input type="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <p><?= $message ?></p>
</body>
</html>
