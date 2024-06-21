<?php
    session_start();
    require_once(__DIR__ . '/../functions.php');
    CheckRightsAndConnectionAttempt(); // Check user rights and attempt to establish connection

    // MySQL server configuration
    $mysqlServer = "";
    $mysqlUser = "";
    $mysqlPassword = "";
    $mysqlBase = "";

    try {
        // Establish a connection to MySQL database using PDO
        $db = new PDO("mysql:host={$mysqlServer};dbname={$mysqlBase}", $mysqlUser, $mysqlPassword);
        
        // Set the character set to UTF-8 for proper encoding
        $db->exec('SET NAMES utf8');
    }
    catch (Exception $e) {
        // If connection fails, terminate with an error message
        die("Error: Unable to connect to the database");
    }
?>