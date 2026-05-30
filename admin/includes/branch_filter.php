<?php
// ==========================
// 🔒 BRANCH FILTER SYSTEM (FINAL)
// ==========================

// Ensure session exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 👤 USER ROLE
// ==========================
$role = $_SESSION['role'] ?? 'guest';

// Default branch
$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 🔁 SUPER ADMIN CONTROL
// ==========================
if ($role === 'super_admin') {

    if (isset($_GET['branch_id'])) {
        $_SESSION['branch_id'] = $_GET['branch_id']; // can be "all"
    }

    $branch_id = $_SESSION['branch_id'];
}

// ==========================
// 🔁 ADMIN CONTROL
// ==========================
elseif ($role === 'admin') {

    if (isset($_GET['branch_id'])) {
        $_SESSION['branch_id'] = (int)$_GET['branch_id'];
    }

    $branch_id = $_SESSION['branch_id'];
}

// ==========================
// 🚫 OTHER ROLES LOCKED
// ==========================
else {
    // cashier, manager → fixed branch
    $branch_id = $_SESSION['branch_id'] ?? 1;
}

// ==========================
// 🧠 APPLY BRANCH FILTER
// ==========================
if (!function_exists('applyBranchFilter')) {
    function applyBranchFilter($sql, &$params = [], &$types = "")
    {
        global $branch_id;

        // SUPER ADMIN → no filter
        if ($branch_id === 'all') {
            return $sql;
        }

        // Add filter safely
        $sql .= " AND branch_id = ?";

        $params[] = (int)$branch_id;
        $types   .= "i";

        return $sql;
    }
}

// ==========================
// 🚀 SAFE QUERY EXECUTOR
// ==========================
if (!function_exists('runQuery')) {
    function runQuery($conn, $sql, $params = [], $types = "")
    {
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        // Bind params if exist
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        return $stmt->get_result();
    }
}