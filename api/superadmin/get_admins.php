<?php

require '../config.php';

header("Content-Type: application/json");

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Set page limit
$skip = ($page - 1) * $limit;

$collection = $db->admins;
$filter = ["role" => ['$ne' => 'superadmin']];

$totalAdmins = $collection->countDocuments($filter);
$totalPages = ceil($totalAdmins / $limit);

$options = [
    'skip' => $skip,
    'limit' => $limit
];

$cursor = $collection->find($filter, $options);

$admins = [];
foreach ($cursor as $admin) {
    $admins[] = [
        '_id' => (string)$admin->_id,
        'name' => $admin->designation . ' ' . $admin->name,
        'designation' => $admin->designation ?? '',
        'email' => $admin->email,
        'role' => $admin->role,
        'department' => $admin->department ?? 'N/A',
        'staff_number' => $admin->staff_number ?? 'N/A',
        'phone_number' => $admin->phone_number ?? '',
        'created_at' => isset($admin->created_at) ? $admin->created_at->toDateTime()->format('Y-m-d H:i') : 'N/A'
    ];
}


echo json_encode([
    "success" => true,
    "admins" => $admins,
    "totalPages" => $totalPages
]);
?>

