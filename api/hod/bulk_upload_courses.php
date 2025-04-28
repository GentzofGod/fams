<?php
require '../config.php';
require '../../vendor/autoload.php'; // If using Excel parsing

use PhpOffice\PhpSpreadsheet\IOFactory; // For Excel files
use League\Csv\Reader; // For CSV files

header("Content-Type: application/json");

if (!isset($_FILES['file'])) {
    echo json_encode(["success" => false, "message" => "No file uploaded"]);
    exit;
}

$file = $_FILES['file']['tmp_name'];
$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$collection = $db->courses;

$insertedCount = 0;
$alreadyExist = 0;
$errors = [];

// **Handle Excel Files**
if ($extension === 'xlsx' || $extension === 'xls') {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    foreach ($rows as $index => $row) {
        if ($index == 1) continue; // Skip header row

        $course_code = trim($row['A']);
        $course_title = trim($row['B']);
        $credit_load = trim($row['C']);
        
        if (!$course_code || !$course_title || !$credit_load ) {
            $errors[] = "Missing fields in row $index.";
            continue;
        }
        

        // **Check if user exists**
        $existingUser = $collection->findOne(["course_code" => $course_code]);
        if ($existingUser) {
            $errors[] = "Course $course_code already exists.";
            $alreadyExist++;
            continue;
        }


        // **Insert into MongoDB**
        $result = $collection->insertOne([
            "course_code" => $course_code,
            "course_title" => $course_title,
            "credit_load" => $credit_load,
            "created_at" => new MongoDB\BSON\UTCDateTime()
        ]);
        

        if ($result->getInsertedCount() > 0) {
            $insertedCount++;
        } else {
            $errors[] = "Failed to insert $email";
        }
    }
}

// **Return response with inserted count and errors**
$response = [
    "success" => $insertedCount > 0,
    "message" => "$insertedCount courses added successfully. $alreadyExist Courses already exists",
    "errors" => $errors
];

echo json_encode($response);
?>
