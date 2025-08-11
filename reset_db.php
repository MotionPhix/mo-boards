<?php
// Script to completely drop and recreate MySQL database

$host = '127.0.0.1';
$port = 3306;
$username = 'root';
$password = 'devserver';
$database = 'moboards';

try {
    // Connect to MySQL without specifying database
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop database if it exists
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    echo "Database '$database' dropped successfully.\n";

    // Create database
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$database' created successfully.\n";

    // Test connection to the new database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    echo "Connection to database '$database' successful.\n";

    // List tables to confirm it's empty
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "Database is empty and ready for migrations.\n";
    } else {
        echo "Found tables: " . implode(', ', $tables) . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
