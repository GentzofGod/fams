<?php
require '../config.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and new password required."]);
    exit;
}

$collection = $db->admins;
$user = $collection->findOne(["email" => $email]);

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found."]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$collection->updateOne(
    ["email" => $email],
    ['$set' => ["password" => $hashedPassword, "first_login" => false]]
);

echo json_encode(["success" => true, "message" => "Password updated successfully."]);
?>
