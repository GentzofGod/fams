<?php

require '../config.php';

header("Content-Type: application/json");

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10000;
$skip = ($page - 1) * $limit;

$collection = $db->students;
$options = [
    'skip' => $skip,
    'limit' => $limit
];

$cursor = $collection->find([], $options);
$students = [];

foreach ($cursor as $student) {
    $students[] = [
        '_id' => (string) $student->_id,
        'matric_no' => $student->matric_no,
        'fullname' => $student->fullname,
        'department' => $student->department,
        'programme' => $student ->programme,
        'level' => $student->level,
        'gender' => $student->gender,
        'phone_number' => $student->phone_number,
        'email' => $student->email,
        'created_at' => isset($student->created_at) ? $student->created_at->toDateTime()->format('Y-m-d H:i') : 'N/A'
    ];
}

$totalStudents = $collection->countDocuments();
$totalPages = ceil($totalStudents / $limit);

echo json_encode([
    "success" => true,
    "students" => $students,
    "totalPages" => $totalPages,
    "currentPage" => $page
]);

?>
