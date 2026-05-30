<?php

function requireRole($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        die("⛔ Access denied");
    }
}
?>