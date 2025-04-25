<?php
require '../config.php';
require '../../vendor/autoload.php'; // Ensure PhpSpreadsheet is available for Excel parsing

use PhpOffice\PhpSpreadsheet\IOFactory;
use MongoDB\BSON\UTCDateTime;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_FILES["studentFile"])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$file = $_FILES["studentFile"]["tmp_name"];
$fileExt = pathinfo($_FILES["studentFile"]["name"], PATHINFO_EXTENSION);
$collection = $db->students;
$inserted = 0;

// Define valid departments, levels, and genders
$validDepartments = [
    "Computer Engineering", "Agric and Biosystem", "Electrical Engineering",
    "Mechanical Engineering", "Food Engineering", "Material and Metalurgical",
    "Water Recourses", "Civil Engineering", "Chemical Engineering", "Biomedical Engineering"
];
$validLevels = ["100", "200", "300", "400", "500"];
$validGenders = ["Male", "Female", "M", "F"];  // Valid genders

// Function to split full name into first name and surname
function splitName($fullname) {
    // Explode the full name by space
    $nameParts = explode(" ", $fullname);
    
    // Check if the name has more than one part
    if (count($nameParts) > 1) {
        // First name is the first part, surname is the last part
        $firstname = array_shift($nameParts);
        $surname = array_pop($nameParts);
        $middlename = implode(" ", $nameParts); // All middle parts combined as middle name

        // Return the parts
        return [
            'firstname' => $firstname,
            'surname' => $surname,
            'middlename' => $middlename
        ];
    }
    return [
        'firstname' => $fullname,
        'surname' => '',
        'middlename' => ''
    ];
}

function insertStudent($data, $collection, &$inserted) {
    global $validDepartments, $validLevels, $validGenders;

    // Ensure required fields are available
    if (!isset($data["matric_no"], $data["fullname"], $data["department"],$data["programme"], $data["level"], $data["gender"], $data["email"], $data["phone_number"])) {
        return;
    }

    // Validate gender
    if (!in_array($data["gender"], $validGenders)) {
        return; // Skip the student if the gender is invalid
    }

    // Check for valid department and level
    if (!in_array($data["department"], $validDepartments) || !in_array($data["level"], $validLevels)) {
        return;
    }

    // Check for existing student with same matric_no, email, phone_number, or student_id
    $existing = $collection->findOne([
        '$or' => [
            ['matric_no' => (string) $data["matric_no"]],
            ['email' => (string) $data["email"]],
            ['phone_number' => (string) $data["phone_number"]],
            ['student_id' => (string) $data["student_id"]]
        ]
    ]);

    if ($existing) {
        return; // Skip the student if any of the unique fields already exist
    }

    // Split the fullname into first name, surname, and middle name
    $nameParts = splitName($data["fullname"]);

    // Create student data
    $student_id = isset($data["student_id"]) ? (string) $data["student_id"] : uniqid();
    $level = (string) $data["level"];

    // Insert student into MongoDB collection
    $collection->insertOne([
        "student_id" => $student_id,
        "matric_no" => (string) $data["matric_no"],
        "fullname" => $data["fullname"], // Store full name as it is
        "firstname" => $nameParts['firstname'],
        "surname" => $nameParts['surname'],
        "middlename" => $nameParts['middlename'], // Optional, based on your use
        "department" => $data["department"],
        "programme"=>$data["programme"],
        "level" => $level,
        "gender" => $data["gender"],
        "email" => $data["email"],
        "phone_number" => $data["phone_number"],
        "created_at" => new UTCDateTime()
    ]);

    $inserted++;
}

if ($fileExt === "csv") {
    $handle = fopen($file, "r");
    if (!$handle) {
        echo json_encode(["success" => false, "message" => "Unable to read file"]);
        exit;
    }

    $headers = fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {
        $studentData = array_combine($headers, $row);
        insertStudent($studentData, $collection, $inserted);
    }

    fclose($handle);
} elseif ($fileExt === "xlsx") {
    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $headers = array_map('strtolower', $rows[1]);

        for ($i = 2; $i <= count($rows); $i++) {
            $studentData = array_combine($headers, $rows[$i]);
            insertStudent($studentData, $collection, $inserted);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error reading Excel file: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid file format. Use CSV or Excel."]);
    exit;
}

echo json_encode(["success" => true, "message" => "$inserted students uploaded successfully."]);
?>
