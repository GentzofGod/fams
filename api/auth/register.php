<?php
require '../config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);


$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

// Check if email exists
$existing = $usersCollection->findOne(['email' => $email]);
if ($existing) {
    echo json_encode(['status' => false, 'message' => 'Email already exists']);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$usersCollection->insertOne([
    'email' => $email,
    'password' => $hashedPassword,
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(['status' => true, 'message' => 'User registered successfully']);
