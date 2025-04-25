<?php

// ✅ These must be placed BEFORE session_start
ini_set('session.gc_maxlifetime', 259200); // 3 days
session_set_cookie_params([
    'lifetime' => 60, // 
    'path' => '/',
    'httponly' => true,
    'secure' => false, // Change to true if using HTTPS
    'samesite' => 'Lax'
]);

session_start();

require_once '../config.php';
header("Content-Type: application/json");

$collection = $db->admins;
$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password are required!"]);
    exit;
}

$admin = $collection->findOne(["email" => $email]);

if (!$admin || !password_verify($password, $admin->password)) {
    echo json_encode(["success" => false, "message" => "Invalid credentials!"]);
    exit;
}

// Superadmin bypasses first_login check
if ($admin->role !== "superadmin" && (!isset($admin->first_login) || $admin->first_login === true)) {
    echo json_encode([
        "success" => true,
        "message" => "First login detected, please change your password.",
        "redirect" => "../public/reset/reset.html"
    ]);
    exit;
}

// ✅ Store login session
$_SESSION['admin_id'] = (string) $admin->_id;
$_SESSION['admin_email'] = $admin->email;
$_SESSION['admin_role'] = $admin->role;
$_SESSION['admin_name'] = $admin->name ?? '';
$_SESSION['phone_number'] =$admin->phone_number;
$_SESSION['staff_number'] = $admin->staff_number;
$_SESSION['department'] = $admin->department ?? '';
$_SESSION['last_login_time'] = time();

echo json_encode([
    "success" => true,
    "message" => "Login successful!",
    "role" => $admin->role
]);
?>
