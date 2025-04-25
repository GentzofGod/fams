<?php
require_once '../config.php';  // Assuming this includes the necessary MongoDB connection setup

header('Content-Type: application/json');
session_start();

// Check if the user is logged in
$lecturerId = $_SESSION['admin_id'] ?? null;
if (!$lecturerId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['matric_no'])) {
    // Get the matric number from the request body
    $matricNo = strtoupper(trim($data['matric_no']));
} else {
    echo json_encode(['success' => false, 'message' => 'Matric number is required']);
    exit;
}

// MongoDB connection
$mongo = (new MongoDB\Client)->fams;
$enrolledStudentsCollection = $mongo->enrolled_students;

// Remove the student from the course
$deleteResult = $enrolledStudentsCollection->deleteOne(['matric_no' => $matricNo]);

if ($deleteResult->getDeletedCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Student removed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found or already removed']);
}
?>
