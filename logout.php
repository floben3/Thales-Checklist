<?php
    session_start();

    // Marking logout attempt
    $_SESSION['LOGOUT_ATTEMPT'] = TRUE;

    require_once(__DIR__ . '/functions.php');

    if (isset($_SESSION['LOGGED_USER'])) { 
        $lm = 1; // Set a flag for logout message
        // Log the logout event with user details
        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Successfully deconnected');
    }

    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session data

    session_start();

    if ($lm === 1) {
        $_SESSION['LOGIN_MESSAGE'] = 'Vous êtes déconnecté(e)'; // Set logout message
    }
    unset($lm);

    header('Location:index.php');
?>