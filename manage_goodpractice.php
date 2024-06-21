<?php
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckPostRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;
    $goodpracticeId = Sanitize($postData['goodpracticeId']);

    // Handling different submit actions
    if ($postData['submit'] === 'erase') {
        // Erase action: temporarily erase the good practice
        if (!isset($postData['programNames'])) {
            $_SESSION['ERASED_GOODPRACTICES'][] = $goodpracticeId;
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Goodpractice temporarily erased');
        } else {
            $programNames = $postData['programNames'];
            foreach ($programNames as $programName) {
                $_SESSION['ERASED_GOODPRACTICES_PROGRAMS']['id'.$goodpracticeId][] = Sanitize($programName);
            }
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Goodpractice temporarily erased for one or more programs');
        }
    } elseif ($postData['submit'] === 'duplicate' && !empty($postData['programNames'])) {
        // Duplicate action: duplicate the good practice for selected programs
        $programNames = $postData['programNames'];
        $goodpracticeId = Sanitize($postData['goodpracticeId']);
        DuplicateGoodpractice($programNames, $goodpracticeId);
        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Goodpractice duplicated for one or more programs');
    } elseif ($postData['submit'] === 'operator-delete') {
        // Operator delete action: non-permanently delete the good practice by an operator
        if (!isset($postData['programNames'])) {
            DeleteOperatorGoodpractice($goodpracticeId);
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Goodpractice non-permanently deleted by an operator');
        } else {
            $programNames = $postData['programNames'];
            DeleteOperatorGoodpractice($goodpracticeId, $programNames);
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Goodpractice non-permanently deleted by an operator for one or more programs');
        }
    } elseif ($_SESSION['LOGGED_USER']['profile'] === 'admin' || $_SESSION['LOGGED_USER']['profile'] === 'superadmin') {
        // Admin or superadmin actions
        if ($postData['submit'] === 'delete') {
            // Admin delete action: permanently delete the good practice
            if (!isset($postData['programNames'])) {
                DeleteGoodpractice($goodpracticeId);
                Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, 'Goodpractice permanently deleted');
            } else {
                $programNames = $postData['programNames'];
                DeleteGoodpractice($goodpracticeId, $programNames);
            }
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, 'Goodpractice permanently deleted for one or more programs');
        } elseif ($postData['submit'] === 'restore') {
            // Admin restore action: restore the previously deleted good practice
            RestoreGoodpractice($goodpracticeId);
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Goodpractice restored');
        }
    }
    header('Location:index.php');
?>