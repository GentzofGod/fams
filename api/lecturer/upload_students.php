<?php
// Include necessary files
require_once '../config.php';
require_once '../../vendor/autoload.php';

// Set response header
header('Content-Type: application/json');

// Get course ID and uploaded file
$courseId = $_POST['course_id'];
$file = $_FILES['file'];

// Validate the input
if (empty($courseId) || !$file) {
    echo json_encode(['success' => false, 'message' => 'Missing course ID or file']);
    exit;
}

// Connect to MongoDB
$db = (new MongoDB\Client)->your_database_name;
$coursesCollection = $db->courses;
$studentsCollection = $db->students;

// Process the uploaded Excel file
try {
    // Load the file using PhpSpreadsheet
    $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Loop through the rows and add students
    $rows = $sheet->toArray();
    $addedStudents = 0;
    
    foreach ($rows as $row) {
        $matricNumber = $row[0]; // Assuming matric number is in the first column
        
        // Check if the student exists
        $student = $studentsCollection->findOne(['matric_number' => $matricNumber]);
        
        if ($student) {
            // Add the student to the course if not already added
            $coursesCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectID($courseId)],
                ['$addToSet' => ['students' => $student['_id']]]
            );
            $addedStudents++;
        }
    }
    
    echo json_encode(['success' => true, 'message' => "$addedStudents students uploaded successfully"]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing file: ' . $e->getMessage()]);
}
?>
