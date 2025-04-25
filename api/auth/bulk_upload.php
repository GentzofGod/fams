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
$collection = $db->admins;

$insertedCount = 0;
$errors = [];

// **Handle CSV Files**
if ($extension === 'csv') {
    $csv = Reader::createFromPath($file, 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv as $row) {
        if (!isset($row['name'], $row['email'], $row['role'], $row['department'], $row['password'])) {
            $errors[] = "Invalid CSV format.";
            continue;
        }

        // **Check if user exists**
        $existingUser = $collection->findOne(["email" => $row['email']]);
        if ($existingUser) {
            $errors[] = "User with email " . $row['email'] . " already exists.";
            continue;
        }

        // **Ensure only one HOD per department**
        if ($row['role'] === 'HOD') {
            $existingHOD = $collection->findOne(["role" => "HOD", "department" => $row['department']]);
            if ($existingHOD) {
                $errors[] = "Only one HOD can be registered for department " . $row['department'];
                continue;
            }
        }

        // **Insert into MongoDB**
        $result = $collection->insertOne([
            "name" => $row['name'],
            "email" => $row['email'],
            "role" => $row['role'],
            "department" => $row['department'],
            "password" => password_hash($row['password'], PASSWORD_BCRYPT),
            "first_login" => true, // Ensure first_login is true for all new admins
            "created_at" => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($result->getInsertedCount() > 0) {
            $insertedCount++;
        } else {
            $errors[] = "Failed to insert " . $row['email'];
        }
    }
}

// **Handle Excel Files**
elseif ($extension === 'xlsx' || $extension === 'xls') {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    foreach ($rows as $index => $row) {
        if ($index == 1) continue; // Skip header row

        $name = trim($row['A']);
        $email = trim($row['B']);
        $role = trim($row['C']);
        $department = trim($row['D']);
        $password = trim($row['E']);
        $staff_number = trim($row['F']);
        $phone_number = trim($row['G']);
        $designation = trim($row['H']);
        
        if (!$name || !$email || !$role || !$department || !$password || !$staff_number || !$phone_number || !$designation) {
            $errors[] = "Missing fields in row $index.";
            continue;
        }
        

        // **Check if user exists**
        $existingUser = $collection->findOne(["email" => $email]);
        if ($existingUser) {
            $errors[] = "User with email $email already exists.";
            continue;
        }

        // **Ensure only one HOD per department**
        if ($role === 'HOD') {
            $existingHOD = $collection->findOne(["role" => "HOD", "department" => $department]);
            if ($existingHOD) {
                $errors[] = "Only one HOD can be registered for department $department.";
                continue;
            }
        }

        $existingStaff = $collection->findOne(["staff_number" => $staff_number]);
if ($existingStaff) {
    $errors[] = "Staff number $staff_number already exists.";
    continue;
}

        // **Insert into MongoDB**
        $result = $collection->insertOne([
            "name" => $name,
            "email" => $email,
            "role" => $role,
            "department" => $department,
            "password" => password_hash($password, PASSWORD_BCRYPT),
            "staff_number" => $staff_number,
            "phone_number" => $phone_number,
            "designation" => $designation,
            "first_login" => true,
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
    "message" => "$insertedCount users added successfully.",
    "errors" => $errors
];

echo json_encode($response);
?>
