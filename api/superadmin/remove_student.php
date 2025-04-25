<?php
require '../config.php';
header("Content-Type: application/json");

use MongoDB\BSON\ObjectId;

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Student ID is required"]);
    exit;
}

try {
    $studentId = new ObjectId($_POST['id']);

    // Optional: Find student before deleting to remove image
    $student = $db->students->findOne(['_id' => $studentId]);
    if ($student && isset($student->passport)) {
        $passportPath = __DIR__ . '/../../' . $student->passport;
        if (file_exists($passportPath)) {
            unlink($passportPath);
        }
    }

    $result = $db->students->deleteOne(['_id' => $studentId]);

    if ($result->getDeletedCount() > 0) {
        echo json_encode(["success" => true, "message" => "Student deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Student not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Invalid Student ID"]);
}
