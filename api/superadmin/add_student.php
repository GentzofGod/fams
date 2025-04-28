<?php
require '../config.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$requiredFields = [
    'student_id', 'matric_no', 'surname', 'firstname', 'gender',
    'programme', 'department', 'level', 'phone_number', 'email'
];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(["success" => false, "message" => "$field is required"]);
        exit;
    }
}

// Validate fields
$validDepartments = [
    "Computer Engineering", "Agric and Biosystem", "Electrical Engineering",
    "Mechanical Engineering", "Food Engineering", "Material and Metalurgical",
    "Water Recourses", "Civil Engineering", "Chemical Engineering", "Biomedical Engineering"
];
$validLevels = ["100", "200", "300", "400", "500"];
$validGenders = ["M", "F"];

if (!in_array($_POST['department'], $validDepartments)) {
    echo json_encode(["success" => false, "message" => "Invalid department selected"]);
    exit;
}

if (!in_array($_POST['level'], $validLevels)) {
    echo json_encode(["success" => false, "message" => "Invalid level selected"]);
    exit;
}

if (!in_array($_POST['gender'], $validGenders)) {
    echo json_encode(["success" => false, "message" => "Invalid gender selected"]);
    exit;
}

// Check if student already exists
$collection = $db->students;
$existing = $collection->findOne(['matric_no' => $_POST['matric_no']]);
if ($existing) {
    echo json_encode(["success" => false, "message" => "Student with this matric number already exists"]);
    exit;
}

// Insert into MongoDB
$collection->insertOne([
    'student_id' => $_POST['student_id'],
    'matric_no' => $_POST['matric_no'],
    'surname' => $_POST['surname'],
    'firstname' => $_POST['firstname'],
    'fullname' => $_POST['surname'] . ' ' . $_POST['firstname'],
    'gender' => $_POST['gender'],
    'programme' => $_POST['programme'],
    'department' => $_POST['department'],
    'level' => $_POST['level'],
    'phone_number' => $_POST['phone_number'],
    'email' => $_POST['email'],
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(["success" => true, "message" => "Student added successfully"]);
