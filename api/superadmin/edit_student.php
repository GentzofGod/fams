<?php
require '../config.php';
header("Content-Type: application/json");

$id = $_POST['id'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$matric_no = $_POST['matric_no'] ?? '';
$department = $_POST['department'] ?? '';
$level = $_POST['level'] ?? '';

if (!$id || !$fullname || !$matric_no || !$department || !$level) {
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
    "level" => $level
];

// Handle optional new passport
if (isset($_FILES['passport']) && $_FILES['passport']['error'] === 0) {
    $uploadDir = __DIR__ . '/../../uploads/passports/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['passport']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $ext;
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['passport']['tmp_name'], $targetFile)) {
        $passportPath = 'uploads/passports/' . $fileName;
        $updateFields['passport'] = $passportPath;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload passport"]);
        exit;
    }
}

$collection = $db->students;
$collection->updateOne(
    ["_id" => $studentId],
    ['$set' => $updateFields]
);

echo json_encode(["success" => true, "message" => "Student updated successfully"]);
