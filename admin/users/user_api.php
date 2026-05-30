<?php
require_once __DIR__ . '/../includes/db.php';

// ==========================
// ADD USER
// ==========================
if ($_POST['action'] === 'add') {

    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // check duplicate
    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['status'=>'error','msg'=>'User exists']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (username,name,email,password,role) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $username,$name,$email,$password,$role);
    $stmt->execute();

    echo json_encode(['status'=>'success']);
    exit;
}

// ==========================
// GET USER
// ==========================
if ($_GET['action'] === 'get') {

    $id = $_GET['id'];

    $res = $conn->query("SELECT * FROM users WHERE id=$id");
    echo json_encode($res->fetch_assoc());
    exit;
}

// ==========================
// UPDATE USER
// ==========================
if ($_POST['action'] === 'update') {

    $id = $_POST['id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET name=?,username=?,email=?,role=? WHERE id=?");
    $stmt->bind_param("ssssi",$name,$username,$email,$role,$id);
    $stmt->execute();

    echo json_encode(['status'=>'updated']);
    exit;
}

// ==========================
// DELETE USER
// ==========================
if ($_POST['action'] === 'delete') {

    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();

    echo json_encode(['status'=>'deleted']);
    exit;
}