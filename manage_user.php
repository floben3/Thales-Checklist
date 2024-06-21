<?php
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckPostAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);
    $postData = $_POST;
    $userId = Sanitize($postData['userId']);

    if ($postData['submit'] === 'delete-user') {
        // Delete user action: permanently delete the user
        $username = UserWhatIsName($userId);
        UserDelete($userId, $profile); // Delete the user
        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, 'User with username : '.$username.' permanently deleted'); // Log the action
    } elseif ($postData['submit'] === 'reset-password') {
        // Reset password action: reset the user's password
        $password = Sanitize($postData['password']);
        $password2 = Sanitize($postData['password2']);
        $username = UserWhatIsName($userId);

        // Validate the new password
        $passwordValidationResult = PasswordIsValid($username, $password, $password2);
        
        if ($passwordValidationResult === NULL) {
            // If password validation passes, reset the password
            $loggedUserId = Sanitize($_SESSION['LOGGED_USER']['user_id']);
            UserResetPassword($loggedUserId, $userId, $password, $profile);
            $_SESSION['RESET_USER_PASSWORD_MESSAGE'] = 'Mot de passe réinitialisé avec succès !'; // Success message
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Successfully reset password for blocked user with username : '.$username.', account unlocked'); // Log success
        } else {
            // If password validation fails, set error message
            $_SESSION['RESET_USER_PASSWORD_MESSAGE'] = $passwordValidationResult;
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, 'Failed to reset password for blocked user with username : '.$username.', new password issue'); // Log failure
        }
    }

    header('Location:admin.php');
?>