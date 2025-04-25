<?php
require_once '../config.php';

header('Content-Type: application/json');

$collection = $db->admins;

// Check if a Super Admin already exists
$existingSuperAdmin = $collection->findOne(['role' => 'superadmin']);
if ($existingSuperAdmin) {
    echo json_encode(["success" => false, "message" => "Super Admin already exists!"]);
    exit;
}

// Get input data
$data = json_decode(file_get_contents("php://input"), true);

// Check for required fields
if (
    !$data ||
    !isset($data['name'], $data['email'], $data['staff_number'], $data['designation'], $data['phone_number'], $data['password'])
) {
    echo json_encode(["success" => false, "message" => "All fields are required!"]);
    exit;
}

// Strong password validation (server-side fallback)
$password = $data['password'];
if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[\W]/', $password)
) {
    echo json_encode(["success" => false, "message" => "Password does not meet the strength requirements."]);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert Super Admin into MongoDB
$result = $collection->insertOne([
    "name" => $data['name'],
    "email" => $data['email'],
    "staff_number" => $data['staff_number'],
    "designation" => $data['designation'],
    "phone_number" => $data['phone_number'],
    "password" => $hashedPassword,
    "role" => "superadmin", // automatically assigned
    "first_login" => false,
    "created_at" => new MongoDB\BSON\UTCDateTime()
]);

if ($result->getInsertedCount() > 0) {
    echo json_encode(["success" => true, "message" => "Super Admin registered successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to register Super Admin."]);
}
?>
