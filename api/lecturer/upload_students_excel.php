<?php
require '../../vendor/autoload.php'; // Adjust if needed
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Skip the header row
        unset($data[0]);

        // Connect to MongoDB
        require '../config.php'; // adjust path
        $studentsCollection = $db->students;
        $courseStudentsCollection = $db->course_students; // this links students to courses

        $successCount = 0;
        $errors = [];

        foreach ($data as $row) {
            $matricNumber = trim($row[0]);

            if (empty($matricNumber)) continue;

            $student = $studentsCollection->findOne(['matric_no' => $matricNumber]);

            if ($student) {
                // Check if already added to course (optional: based on lecturer's course ID)
                $alreadyExists = $courseStudentsCollection->findOne([
                    'matric_no' => $matricNumber,
                    'course_id' => $_POST['course_id'] // include course_id from the form if needed
                ]);

                if (!$alreadyExists) {
                    $courseStudentsCollection->insertOne([
                        'course_id' => $_POST['course_id'], // You should pass this from the form
                        'matric_no' => $student['matric_no'],
                        'name' => $student['surname'] . ' ' . $student['first_name'],
                        'department' => $student['department'],
                        'level' => $student['level'],
                        'added_by' => $_SESSION['lecturer_id'],
                        'timestamp' => new MongoDB\BSON\UTCDateTime()
                    ]);
                    $successCount++;
                }
            } else {
                $errors[] = "Matric number not found: $matricNumber";
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => "$successCount students added.",
            'errors' => $errors
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
