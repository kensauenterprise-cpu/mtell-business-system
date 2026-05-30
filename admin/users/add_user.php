<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/role.php';

requireRole('admin');

$error = "";

// ==========================
// HANDLE FORM
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'cashier';
    $branch   = (int)($_POST['branch_id'] ?? 1);

    $allowed_roles = ['admin','manager','cashier','rider'];

    // ==========================
    // VALIDATION
    // ==========================
    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    }
    elseif (!in_array($role, $allowed_roles)) {
        $error = "Invalid role selected.";
    }
    else {

        // ==========================
        // CHECK DUPLICATES
        // ==========================
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            $error = "Username or email already exists.";
        } else {

            // ==========================
            // HASH PASSWORD
            // ==========================
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // ==========================
            // INSERT USER
            // ==========================
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password, role, branch_id)
                VALUES (?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                die("❌ SQL Error: " . $conn->error);
            }

            $stmt->bind_param("ssssi", $username, $email, $hashed, $role, $branch);

            if ($stmt->execute()) {

                // ==========================
                // 🔥 ACTIVITY LOG (OPTIONAL BUT IMPORTANT)
                // ==========================
                if (isset($_SESSION['user_id'])) {
                    $admin_id = $_SESSION['user_id'];
                    $action = "Created user: " . $username;

                    $log = $conn->prepare("
                        INSERT INTO activity_logs (user_id, action, created_at)
                        VALUES (?, ?, NOW())
                    ");
                    $log->bind_param("is", $admin_id, $action);
                    $log->execute();
                }

                header("Location: /infinity/admin/pages/dashboard.php?tab=users&success=1");
                exit;

            } else {
                $error = "❌ Error creating user.";
            }
        }
    }
}
?>

<h2>➕ Add User</h2>

<?php if ($error): ?>
<p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">

<input type="text" name="username" placeholder="Username" required><br><br>

<input type="email" name="email" placeholder="Email"><br><br>

<input type="password" name="password" placeholder="Password" required><br><br>

<select name="role" required>
    <option value="admin">Admin</option>
    <option value="manager">Manager</option>
    <option value="cashier">Cashier</option>
    <option value="rider">Rider</option>
</select><br><br>

<input type="number" name="branch_id" value="1" min="1"><br><br>

<button type="submit">Save User</button>

</form>