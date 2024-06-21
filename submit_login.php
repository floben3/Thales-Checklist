<?php
    session_start();
    $_SESSION['LOGIN_ATTEMPT'] = TRUE;
    require_once(__DIR__ . '/functions.php');
    CheckConnectionRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;

    // Check if username and password are set
    if (isset($postData['username']) && isset($postData['password'])) {
        $username = Sanitize($postData['username']);
        $password = Sanitize($postData['password']);

        // Fetch all users from the database
        $users = UsersSelect();
        $userIsBlocked = 0;
        $u = 0;

        // Loop through each user
        foreach ($users as $user) {
            if ($username === $user['username']) {
                $u = 1;
                $hash = $user['password'];
                // Verify password
                if (password_verify($password, $hash)) {
                    // Check if user is blocked
                    $userIsBlocked = UserIsBlocked(Sanitize($user['attempts']));
                    if (!$userIsBlocked) {
                        // Set session variables for logged user
                        $_SESSION['LOGGED_USER'] = [
                            'user_id' => Sanitize($user['user_id']),
                            'username' => Sanitize($user['username']),
                            'firstname' => Sanitize($user['firstname']),
                            'lastname' => Sanitize($user['lastname']),
                            'profile' => Sanitize($user['profile']),
                        ];
                        // Reset user attempts on successful login
                        UserAttempts($user['user_id'], 'reset');
                        // Log the successful connection attempt
                        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Successful connection attempt');
                        header('Location:index.php');
                        break;
                    } else {
                        // If user is blocked, set login message and log the attempt
                        $_SESSION['LOGIN_MESSAGE'] = 'Utilisateur bloqué.';
                        Logger(NULL, NULL, 2, 'Connection attempt to a blocked account with username: '.$username);
                        break;
                    }
                } else {
                    // Increment user attempts on failed login for non-superadmin profiles
                    if ($user['profile'] !== 'superadmin') {
                        UserAttempts($user['user_id'], 'increment');
                        // Log different messages based on the number of attempts
                        if (Sanitize($user['attempts']+1 === 3)) {
                            Logger(NULL, NULL, 2, 'Failed connection attempt with username: '.$username.', account blocked');
                        } elseif (UserIsBlocked(Sanitize($user['attempts']+1))) {
                            Logger(NULL, NULL, 2, 'Failed connection attempt to a blocked account with username: '.$username);
                        } else {
                            Logger(NULL, NULL, 1, 'Failed connection attempt with username: '.$username);
                        }
                        break;
                    }
                }
            }
        }
        // If user is not logged in and not blocked, set login message
        if (!isset($_SESSION['LOGGED_USER']) && !$userIsBlocked) {
            $_SESSION['LOGIN_MESSAGE'] = "Echec de l'authentification.";
        }

        // Log the attempt if username was not found
        if ($u === 0) {
            Logger(NULL, NULL, 2, 'Failed connection attempt with username: '.$username.', no account with this username');
        }

        header('Location:login.php');
    }
?>