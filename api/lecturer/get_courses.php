
<?php
session_start();
header("Content-Type: application/json");
require_once '../config.php';

// Check if session is valid and user is a lecturer
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Lecturer') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit;
}

$lecturerId = $_SESSION['admin_id'];  
$collection = $db->admins;

// Convert the lecturerId to ObjectId for comparison
$lecturerIdObj = new MongoDB\BSON\ObjectId($lecturerId);

$lecturer = $collection->findOne(['_id' => $lecturerIdObj]);

if (!$lecturer) {
    echo json_encode([
        "success" => false,
        "message" => "Lecturer not found."
    ]);
    exit;
}

// Fetch assigned courses using the correct field key ("Lecturer_id" with capital L)
$assignedCourses = $db->assigned_courses->find(["Lecturer_id" => $lecturerIdObj])->toArray();
$courses = [];

foreach ($assignedCourses as $ac) {
    $course = $db->courses->findOne(["_id" => $ac['course_id']]);
    if ($course) {
        $courses[] = [
            "_id"=>(string) $course["_id"],
            "course_code" => $course["course_code"],
            "course_title" => $course["course_title"],
            "credit_load" => $course["credit_load"]
        ];
    }
}

echo json_encode([
    "success" => true,
    "courses" => $courses
]);
