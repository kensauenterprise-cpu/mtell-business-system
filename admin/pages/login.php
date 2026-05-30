<?php
// ===============================
// 🔐 SESSION
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// 🔗 DB (CORRECT PATH)
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/db.php';

// ===============================
// ✅ DB CHECK
// ===============================
if (!isset($conn) || !$conn) {
    die("Database connection missing");
}

// ===============================
// 🔑 INIT ERROR
// ===============================
$error = "";

// ===============================
// 🔑 LOGIN
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ===============================
    // ✅ NORMALIZE INPUT
    // ===============================
    $user = strtolower(trim($_POST['username'] ?? ''));
    $pass = trim($_POST['password'] ?? '');

    if (empty($user) || empty($pass)) {

        $error = "Please enter username and password.";

    } else {

        // ===============================
        // 🔍 FIND USER
        // ===============================
        $stmt = $conn->prepare("
            SELECT
                id,
                username,
                email,
                password,
                role,
                branch_id
            FROM users
            WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))
               OR LOWER(TRIM(username)) = LOWER(TRIM(?))
            LIMIT 1
        ");

        // ===============================
        // ❌ QUERY CHECK
        // ===============================
        if (!$stmt) {
            die("❌ Query error: " . $conn->error);
        }

        // ===============================
        // 🔗 BIND + EXECUTE
        // ===============================
        $stmt->bind_param("ss", $user, $user);

        if (!$stmt->execute()) {
            die("❌ Execute error: " . $stmt->error);
        }

        // ===============================
        // 📦 RESULT
        // ===============================
        $result = $stmt->get_result();

        // ===============================
        // ✅ USER FOUND
        // ===============================
        if ($result && $result->num_rows > 0) {

            $row = $result->fetch_assoc();

            // ===============================
            // 🔐 VERIFY PASSWORD
            // ===============================
            if (
                !empty($row['password']) &&
                password_verify($pass, $row['password'])
            ) {

                // ===============================
                // 🔒 SECURE SESSION
                // ===============================
                session_regenerate_id(true);

                // ===============================
                // ✅ SAVE SESSION
                // ===============================
                $_SESSION['user_id'] = (int)$row['id'];

                $_SESSION['username'] = !empty($row['username'])
                    ? trim($row['username'])
                    : '';

                $_SESSION['email'] = !empty($row['email'])
                    ? trim($row['email'])
                    : '';

                $_SESSION['role'] = !empty($row['role'])
                    ? strtolower(trim($row['role']))
                    : 'guest';

                // ===============================
                // ✅ BRANCH FIX
                // ===============================
                $_SESSION['branch_id'] = !empty($row['branch_id'])
                    ? (int)$row['branch_id']
                    : 1;

                // ===============================
                // 🚀 REDIRECT
                // ===============================
                header("Location: /infinity/admin/pages/dashboard.php");
                exit;

            } else {

                $error = "Invalid password.";
            }

        } else {

            $error = "User not found.";
        }

        // ===============================
        // 🔚 CLOSE
        // ===============================
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Login</title>

<meta charset="utf-8">

<style>
body{
    font-family:Arial;
    background:#f1f5f9;
}

.login-box{
    max-width:400px;
    margin:60px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:6px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#2563eb;
    color:white;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#1d4ed8;
}

.error{
    color:red;
    margin-bottom:15px;
}
</style>

</head>

<body>

<div class="login-box">

<h2>🔐 Login</h2>

<?php if (!empty($error)): ?>
<p class="error">
    <?= htmlspecialchars($error) ?>
</p>
<?php endif; ?>

<form method="post" autocomplete="off">

<!-- prevent autofill -->
<input type="text"
name="fakeusernameremembered"
style="display:none">

<input type="password"
name="fakepasswordremembered"
style="display:none">

<input
    type="text"
    name="username"
    placeholder="Username or Email"
    required
    autocomplete="off"
>

<input
    type="password"
    name="password"
    placeholder="Password"
    required
    autocomplete="new-password"
>

<button type="submit">
    Login
</button>

</form>

</div>

</body>
</html>