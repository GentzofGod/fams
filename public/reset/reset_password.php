<?php
require '../../api/config.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password are required!"]);
    exit;
}

$collection = $db->admins;
$user = $collection->findOne(["email" => $email]);

if (!$user) {
    echo json_encode(["success" => false, "message" => "No account found!"]);
    exit;
}

// Ensure only HOD and Lecturer can reset passwords
if (!in_array($user->role, ["HOD", "Lecturer"])) {
    echo json_encode(["success" => false, "message" => "Password reset not required for this role!"]);
    exit;
}

// Hash new password and update database
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$collection->updateOne(
    ["email" => $email],
    ['$set' => ["password" => $hashedPassword, "first_login" => false]]
);

echo json_encode(["success" => true, "message" => "Password reset successful!"]);
?>
