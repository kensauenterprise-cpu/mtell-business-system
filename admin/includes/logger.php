<?php

function logActivity($action, $module, $description = "", $ref_id = null) {

    global $conn;

    if (!isset($_SESSION)) {
        session_start();
    }

    $user_id  = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'guest';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $conn->prepare("
        INSERT INTO activity_logs 
        (user_id, username, action, module, reference_id, description, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssiss",
        $user_id,
        $username,
        $action,
        $module,
        $ref_id,
        $description,
        $ip
    );

    $stmt->execute();
}