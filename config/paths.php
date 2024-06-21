<?php
    session_start();  // Start or resume the session
    require_once(__DIR__ . '/../functions.php');  // Include functions.php file
    CheckRights();  // Check user rights
    
    // Path to Python3 binary
    $python3BinaryPath = "";

    // If the Python3 binary path is not provided, set a default path
    if ($python3BinaryPath === "") {
        $python3BinaryPath = "/usr/share/bin/python3";
    }
?>