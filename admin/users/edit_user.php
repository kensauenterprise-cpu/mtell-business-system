<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/role.php';

requireRole('admin');

// ==========================
// SESSION
// ==========================
$branch_id = $_SESSION['branch_id'] ?? 1;
$current_user = $_SESSION['username'] ?? 'system';

// ==========================
// GET USER ID
// ==========================
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("❌ Invalid user ID");
}

// ==========================
// FETCH USER (BRANCH SAFE)
// ==========================
$stmt = $conn->prepare("
    SELECT id, name, username, email, role, branch_id 
    FROM users 
    WHERE id = ? AND branch_id = ?
");
$stmt->bind_param("ii", $id, $branch_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("⛔ User not found or access denied");
}

$error = "";

// ==========================
// UPDATE LOGIC
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? '';
    $branch   = (int)($_POST['branch_id'] ?? 1);
    $password = $_POST['password'] ?? '';

    // ======================
    // VALIDATION
    // ======================
    $allowed_roles = ['admin','manager','cashier','viewer'];

    if (!$username || !$name) {
        $error = "⚠️ Name and username are required";
    } elseif (!in_array($role, $allowed_roles)) {
        $error = "⚠️ Invalid role";
    } else {

        // ======================
        // CHECK DUPLICATE USERNAME
        // ======================
        $check = $conn->prepare("
            SELECT id FROM users 
            WHERE (username = ? OR email = ?) AND id != ?
        ");
        $check->bind_param("ssi", $username, $email, $id);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            $error = "⚠️ Username or email already exists";
        } else {

            // ======================
            // UPDATE QUERY
            // ======================
            if (!empty($password)) {

                if (strlen($password) < 4) {
                    $error = "⚠️ Password must be at least 4 characters";
                } else {

                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("
                        UPDATE users 
                        SET name=?, username=?, email=?, password=?, role=?, branch_id=?
                        WHERE id=?
                    ");
                    $stmt->bind_param("ssssiii", $name, $username, $email, $hashed, $role, $branch, $id);
                }

            } else {

                $stmt = $conn->prepare("
                    UPDATE users 
                    SET name=?, username=?, email=?, role=?, branch_id=?
                    WHERE id=?
                ");
                $stmt->bind_param("sssiii", $name, $username, $email, $role, $branch, $id);
            }

            // ======================
            // EXECUTE
            // ======================
            if (empty($error) && $stmt->execute()) {

                // ======================
                // 🔥 AUDIT LOG
                // ======================
                $conn->query("
                    INSERT INTO audit_logs (user, action, details)
                    VALUES (
                        '$current_user',
                        'UPDATE_USER',
                        'Updated user ID $id ($username)'
                    )
                ");

                header("Location: /infinity/admin/pages/dashboard.php?tab=users");
                exit;

            } else {
                $error = $error ?: "❌ Update failed";
            }
        }
    }
}
?>

<h2>✏ Edit User</h2>

<?php if ($error): ?>
<p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">

<input type="text" name="name" 
value="<?= htmlspecialchars($user['name']) ?>" 
placeholder="Full Name" required><br><br>

<input type="text" name="username" 
value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

<input type="email" name="email" 
value="<?= htmlspecialchars($user['email']) ?>"><br><br>

<input type="password" name="password" 
placeholder="Leave blank to keep password"><br><br>

<select name="role">
    <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    <option value="manager" <?= $user['role']=='manager'?'selected':'' ?>>Manager</option>
    <option value="cashier" <?= $user['role']=='cashier'?'selected':'' ?>>Cashier</option>
    <option value="viewer" <?= $user['role']=='viewer'?'selected':'' ?>>Viewer</option>
</select><br><br>

<input type="number" name="branch_id" 
value="<?= (int)$user['branch_id'] ?>"><br><br>

<button type="submit">✅ Update User</button>

</form>