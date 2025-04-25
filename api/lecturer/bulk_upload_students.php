<?php
require_once '../config.php';  // Assuming this includes the necessary MongoDB connection setup
require_once '../../vendor/autoload.php';  // Path to PhpSpreadsheet's autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

header('Content-Type: application/json');
session_start();

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$lecturerId = $_SESSION['admin_id'] ?? null;
if (!$lecturerId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = $_POST; // Other form data like course_id will come through $_POST

$courseId = new MongoDB\BSON\ObjectId($data['course_id'] ?? null);  // Make sure course_id is ObjectId

if (!$courseId || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing course or file']);
    exit;
}

// Get the uploaded file
$file = $_FILES['file'];
$filePath = $file['tmp_name'];

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'File does not exist']);
    exit;
}

try {
    // Load the spreadsheet
    $spreadsheet = IOFactory::load($filePath);

    // Assuming the data is in the first sheet
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Skip the header row
    array_shift($rows);

    // MongoDB connection
    $mongo = (new MongoDB\Client)->fams;
    $studentsCollection = $mongo->students;
    $enrolledStudentsCollection = $mongo->enrolled_students;

    $addedStudents = 0;
    $existingStudents = 0;

    foreach ($rows as $row) {
        $matricNo = strtoupper(trim($row[0])); // Assuming the matric number is in the first column

        // Check if the student exists in the database
        $student = $studentsCollection->findOne(['matric_no' => $matricNo]);

        if ($student) {
            // Check if the student is already enrolled in the course
            $alreadyEnrolled = $enrolledStudentsCollection->findOne([
                'course_id' => $courseId,
                'matric_no' => $matricNo
            ]);

            if (!$alreadyEnrolled) {
                // Add student to the enrolled_students collection
                $enrolledStudentsCollection->insertOne([
                    'course_id' => $courseId,
                    'matric_no' => $matricNo,
                    'student_id' => $student['student_id'],
                    'added_by' => new MongoDB\BSON\ObjectId($lecturerId),
                    'added_at' => new MongoDB\BSON\UTCDateTime()
                ]);
                $addedStudents++;
            } else {
                $existingStudents++;
            }
        }
    }

    // Respond with success
    echo json_encode([
        'success' => true,
        'message' => "$addedStudents student(s) added successfully, $existingStudents student(s) already enrolled."
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing the file: ' . $e->getMessage()]);
}
?>
