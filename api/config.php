
<?php
require __DIR__ . '../../vendor/autoload.php';


try {
    $client = new MongoDB\Client("mongodb://localhost:27017"); // Change this URI if necessary
    $db = $client->fams; // This is your database name
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>


