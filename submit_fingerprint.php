<?php
require 'api/config.php'; // MongoDB connection
header('Content-Type: application/json');

// Decode JSON data from ESP32
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['fingerprint_id']) || !isset($data['sensor_id'])) {
    echo json_encode(['error' => 'fingerprint_id and sensor_id are required']);
    exit;
}

$fingerprint_id = $data['fingerprint_id'];
$sensor_id = $data['sensor_id'];
$timestamp = date('Y-m-d H:i:s');

$collection = $client->selectCollection('fams', 'fingerprint_logs');

// Save to database
$insertResult = $collection->insertOne([
    'sensor_id' => $sensor_id,
    'fingerprint_id' => $fingerprint_id,
    'timestamp' => $timestamp
]);

echo json_encode([
    'message' => 'Fingerprint recorded successfully',
    'inserted_id' => (string) $insertResult->getInsertedId()
]);
