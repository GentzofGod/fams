<?php
require '../config.php';
header("Content-Type: application/json");

$id = $_POST['id'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$matric_no = $_POST['matric_no'] ?? '';
$department = $_POST['department'] ?? '';
$level = $_POST['level'] ?? '';
$gender = $_POST['gender'] ?? '';
$programme = $_POST['programme'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$email = $_POST['email'] ?? '';

if (!$id || !$fullname || !$matric_no || !$department || !$level || !$gender) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

try {
    $studentId = new MongoDB\BSON\ObjectId($id);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Invalid student ID"]);
    exit;
}

$updateFields = [
    "fullname" => $fullname,
    "matric_no" => $matric_no,
    "department" => $department,
    "level" => $level,
    "gender" => $gender,
    "programme" => $programme,
    "phone_number" => $phone_number,
    "email" => $email
];

$collection = $db->students;
$collection->updateOne(
    ["_id" => $studentId],
    ['$set' => $updateFields]
);

echo json_encode(["success" => true, "message" => "Student updated successfully"]);
