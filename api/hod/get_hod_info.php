<?php
session_start();
header("Content-Type: application/json");

require_once '../config.php';

// Check if session is valid
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'HOD') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit;
}

$adminId = $_SESSION['admin_id'];
$collection = $db->admins;

$admin = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($adminId)]);

if (!$admin) {
    echo json_encode([
        "success" => false,
        "message" => "HOD not found."
    ]);
    exit;
}

// Return the HOD's info
echo json_encode([
    "success" => true,
    "name" => ($admin->designation ?? '') . ' ' . ($admin->name ?? ''),
    "email" => $admin->email ?? '',
    "phone_number" => $admin->phone_number ?? '',
    "staff_number" => $admin->staff_number ?? '',
    "department"=>$admin->department,
    "role" => $admin->role ?? ''
]);
?>
