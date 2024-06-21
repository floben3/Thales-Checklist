<?php
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckPostAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;

    // Check if all required fields are set in POST data
    if (isset($postData['username']) && isset($postData['firstname']) && isset($postData['lastname']) && isset($postData['password']) && isset($postData['password2'])) {
        // Sanitize input data
        $username = Sanitize($postData['username']);
        $firstname = Sanitize($postData['firstname']);
        $lastname = Sanitize($postData['lastname']);
        $password = Sanitize($postData['password']);
        $password2 = Sanitize($postData['password2']);
        
        // If logged user is superadmin, check and sanitize profile
        if ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' && isset($postData['profile'])) {
            $profile = Sanitize($postData['profile']);
        }

        // Validate password
        $passwordValidationResult = PasswordIsValid($username, $password, $password2);

        // Check if username, firstname, and lastname are not empty
        if (!empty($username) && !empty($firstname) && !empty($lastname)) {
            // If password is valid
            if ($passwordValidationResult === NULL) {
                // Check if username contains restricted keywords
                if (str_contains(strtolower($username), 'operator') || str_contains(strtolower($username), 'admin') || str_contains(strtolower($username), 'unauthenticated')) {
                    $_SESSION['CREATE_USER_MESSAGE'] = 'Erreur !\n\nNom d utilisateur indisponible : ' . $username . '.';
                    Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, 'Failed to create a user with username: ' . $username . ', username invalid');
                } 
                // Attempt to append user
                elseif (UserAppend($username, $firstname, $lastname, $password, $profile)) {
                    $_SESSION['CREATE_USER_MESSAGE'] = 'Utilisateur créé avec succès !\n\nVous pouvez en ajouter un nouveau.';
                    Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Successfully created a user with username: ' . $username);
                } else {
                    $_SESSION['CREATE_USER_MESSAGE'] = 'Erreur !\n\nNom d utilisateur indisponible : '.$username.'.';
                    Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Failed to create a user with username: ' . $username . ', username unavailable');
                }
            } else {
                // Set session message for password validation error
                $_SESSION['CREATE_USER_MESSAGE'] = $passwordValidationResult;
                Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Failed to create a user with username: ' . $username . ', password issue');
            }
        } else {
            // Set session message for empty input fields
            $_SESSION['CREATE_USER_MESSAGE'] = 'Erreur !\n\nAucune information ne doit être laissée vide.';
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Failed to create a user with username: ' . $username . ', empty information');
        } 
    }

    header('Location:create_user.php');
?>