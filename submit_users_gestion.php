<?php 
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckPostAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;

    // If the form submission is for ordering users
    if ($postData['submit'] === 'users-order') {
        // Sanitize and set the order type and direction in session
        $_SESSION['USERS_SELECTION_ORDER'] = array(Sanitize($postData['users-order']['type']), Sanitize($postData['users-order']['direction']));
    } 
    // If the form submission is for updating password settings
    elseif ($postData['submit'] === 'password-update') {
        $n = Sanitize($postData['n']);
        $p = Sanitize($postData['p']);
        $q = Sanitize($postData['q']);
        $r = Sanitize($postData['r']);
        // Update password configuration settings
        PasswordUpdate($n, $p, $q, $r);
        // Log the successful password configuration change
        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Successfully changed password configuration settings');
    } 
    // If the form submission is to create a new user
    elseif ($postData['submit'] === 'create-user') {
        // Redirect to the create user page
        header('Location:create_user.php');
        exit();
    } 
    // If the form submission is to go to the logs
    elseif ($postData['submit'] === 'to-logs') {
        // Redirect to the log page
        header('Location:log.php');
        exit();
    }
    
    header('Location:admin.php');
?>
