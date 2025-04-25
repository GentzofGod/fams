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

if (isset($_GET['course_id'])) {
    // Get the course ID from the query string
    $courseId = new MongoDB\BSON\ObjectId($_GET['course_id']);
} else {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

// MongoDB connection
$mongo = (new MongoDB\Client)->fams;
$enrolledStudentsCollection = $mongo->enrolled_students;
$studentsCollection = $mongo->students;

// Fetch the enrolled students for the specified course
$enrolledStudents = $enrolledStudentsCollection->find(['course_id' => $courseId]);

// Prepare the list of students
$students = [];
foreach ($enrolledStudents as $enrollment) {
    $student = $studentsCollection->findOne(['matric_no' => $enrollment['matric_no']]);
    if ($student) {
        $students[] = [
            'matric_no' => $student['matric_no'],
            'fullname' => $student['fullname'],
            'department' => $student['department'],
            'level' => $student['level']
        ];
    }
}

echo json_encode(['success' => true, 'students' => $students]);
?>
