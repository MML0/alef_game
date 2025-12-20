<?php
// Database configuration
$host = 'localhost'; // Database host (e.g., localhost, 127.0.0.1)
$dbname = 'quiz_game'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

try {
    // Create a PDO instance and establish the connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set character encoding to UTF-8
    $pdo->exec("SET NAMES 'utf8'");

    // You can now use $pdo in your PHP scripts for querying the database
} catch (PDOException $e) {
    // If connection fails, display error message and stop execution
    die("Connection failed: " . $e->getMessage());
}
?>
