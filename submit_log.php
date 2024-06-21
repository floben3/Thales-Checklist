<?php 
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckPostAdminRights();

    $postData = $_POST;

    // Check which button was clicked
    if ($postData['submit'] === 'reset') {
        // Reset log filters in session
        unset($_SESSION['LOG_FILTERS']['LOG_EVENEMENT_TYPE']);
        unset($_SESSION['LOG_FILTERS']['LOG_PROFILES']);
        unset($_SESSION['LOG_FILTERS']['LOG_SEARCH']);
    } elseif ($postData['submit'] === 'submit') {
        // Set log filters in session
        $_SESSION['LOG_FILTERS']['LOG_EVENEMENT_TYPE'] = $postData['logEvenementTypeSelection'];
        $_SESSION['LOG_FILTERS']['LOG_PROFILES'] = $postData['logUserProfileSelection'];
        $_SESSION['LOG_FILTERS']['LOG_SEARCH'] = Sanitize($postData['logSearch']);
        $_SESSION['LOG_FILTERS']['LOG_DATE_DAY'] = Sanitize($postData['log-date-day']);
        $_SESSION['LOG_FILTERS']['LOG_DATE_MONTH'] = Sanitize($postData['log-date-month']);
        $_SESSION['LOG_FILTERS']['LOG_DATE_YEAR'] = Sanitize($postData['log-date-year']);
    }

    header('Location:log.php');
?>
