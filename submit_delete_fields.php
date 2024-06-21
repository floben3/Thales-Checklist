<?php 
    session_start();

    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;
    
    $fieldType = Sanitize($postData['fieldType']);
    $value = Sanitize($postData['fieldToDelete']);
    $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);

    DeleteField($fieldType, $value, $profile);

    $message = urlencode("Tentative de suppression prise en compte.");
    header("Location:delete_fields.php?e=$message");
?>
