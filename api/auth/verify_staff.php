<?php
require '../config.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$staff_number = $data['staff_number'] ?? '';

if (!$email || !$staff_number) {
    echo json_encode(["success" => false, "message" => "Email and staff number required."]);
    exit;
}

$collection = $db->admins;
$user = $collection->findOne([
    "email" => $email,
    "staff_number" => $staff_number
]);

if ($user) {
    echo json_encode(["success" => true, "message" => "Verified"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid email or staff number."]);
}
?>
