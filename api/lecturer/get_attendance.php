<?php
require '../config.php'; // MongoDB connection
header('Content-Type: application/json');

// Get query parameters
$courseCode = $_GET['course_code'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($courseCode) || empty($date)) {
    echo json_encode(['error' => 'course_code and date are required']);
    exit;
}

$logsCollection = $client->selectCollection('fams', 'fingerprint_logs');
$studentsCollection = $client->selectCollection('fams', 'students');

// Match logs for this course and date
$logsCursor = $logsCollection->find([
    'course_code' => $courseCode,
    'timestamp' => new MongoDB\BSON\Regex("^$date") // Date stored as string in format "YYYY-MM-DD ..."
]);

$attendanceRecords = [];

foreach ($logsCursor as $log) {
    $studentId = $log['student_id'];
    $student = $studentsCollection->findOne(['student_id' => $studentId]);

    $attendanceRecords[] = [
        'student_id' => $studentId,
        'full_name' => $student ? $student['surname'] . ' ' . $student['firstname'] : 'Unknown Student',
        'matric_no' =>$student['matric_no'] ?? 'N/A',
        'department' => $student['department'] ?? 'N/A',
        'level' => $student['level'] ?? 'N/A',
        'timestamp' => $log['timestamp']
    ];
}

echo json_encode(['records' => $attendanceRecords]);
?>
