<?php
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['last_login_time'])) {
    echo json_encode(["authenticated" => false]);
    exit;
}

// Check if session is older than 3 days
if (time() - $_SESSION['last_login_time'] > 60) {
    session_destroy();
    echo json_encode(["authenticated" => false]);
    exit;
}

echo json_encode([
    "authenticated" => true,
    "admin" => [
        "email" => $_SESSION['admin_email'],
        "name" => $_SESSION['admin_name'],
        "role" => $_SESSION['admin_role'],
        "phone_number"=> $_SESSION['phone_number'],
        "staff_number"=>$_SESSION['staff_number'],
        "department"=>$_SESSION['department']
    ]
]);
