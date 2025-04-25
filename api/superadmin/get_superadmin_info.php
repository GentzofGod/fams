<?php
session_start();
header("Content-Type: application/json");

// Check if logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'superadmin') {
    echo json_encode(["success" => false, "message" => "Not authorized"]);
    exit;
}

// Return session info
echo json_encode([
    "success" => true,
    "email" => $_SESSION['admin_email'],
    "role" => $_SESSION['admin_role'],
    "phone_number"=>$_SESSION['phone_number'],
    "staff_number"=>$_SESSION['staff_number'],
    "name" => $_SESSION['admin_name'] ?? "Super Admin"

]);
?>
