<?php
require '../config.php';
header("Content-Type: application/json");

session_start();

// Get department from session or query
$department = $_SESSION['department'] ?? ($_GET['department'] ?? '');

if (empty($department)) {
    echo json_encode([
        "success" => false,
        "message" => "Department not provided"
    ]);
    exit;
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 1000;
$skip = ($page - 1) * $limit;

$collection = $db->students;

// Filter by department
$filter = ['department' => $department];
$options = [
    'skip' => $skip,
    'limit' => $limit
];

$cursor = $collection->find($filter, $options);
$students = [];

foreach ($cursor as $student) {
    $students[] = [
        '_id' => (string) $student->_id,
        'matric_no' => $student->matric_no,
        'fullname' => $student->fullname,
        'department' => $student->department,
        'programme' => $student->programme,
        'level' => $student->level,
        'gender' => $student->gender,
        'phone_number' => $student->phone_number,
        'email' => $student->email,
        'created_at' => isset($student->created_at) ? $student->created_at->toDateTime()->format('Y-m-d H:i') : 'N/A'
    ];
}

$totalStudents = $collection->countDocuments($filter);
$totalPages = ceil($totalStudents / $limit);

echo json_encode([
    "success" => true,
    "students" => $students,
    "totalPages" => $totalPages,
    "currentPage" => $page
]);
