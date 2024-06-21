<?php
    session_start();

    // Check if user is not logged in or attempting unauthorized access
    if (!isset($_SESSION['LOGGED_USER']) && !isset($_SESSION['LOGIN_ATTEMPT']) && !isset($_SESSION['LOGOUT_ATTEMPT']) && !isset($_SESSION['ON_LOGIN_PAGE'])) {
        CheckNotConnectedLogger();
        unset($_SESSION['ON_LOGIN_PAGE']);
    }

    /**
     * Retrieves the current filename.
     * 
     * @return string The filename
     */
    function GetCurrentFile(): string
    {
        $path = $_SERVER['PHP_SELF'];
        $file = basename($path);
        return $file;
    }

    /**
     * Checks if the user has admin rights. Redirects to logout if not.
     */
    function CheckAdminRights(): void
    {
        if (!isset($_SESSION['LOGGED_USER']) || ($_SESSION['LOGGED_USER']['profile'] !== 'admin' && $_SESSION['LOGGED_USER']['profile'] !== 'superadmin')) {
            CheckConnectedLogger();
        }
    }

    /**
     * Checks if the user is logged in. Redirects to logout if not.
     */
    function CheckRights(): void
    {
        if (!isset($_SESSION['LOGGED_USER'])) {
            CheckNotConnectedLogger();
        }
    }

    /**
     * Checks if the user has admin rights for a POST request. Redirects to logout if not.
     */
    function CheckPostAdminRights(): void
    {
        if (!isset($_SESSION['LOGGED_USER']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['LOGGED_USER']['profile'] !== 'admin' && $_SESSION['LOGGED_USER']['profile'] !== 'superadmin')) {
            CheckConnectedLogger();
        }
    }

    /**
     * Checks if the user has rights for a POST request. Redirects to logout if not.
     */
    function CheckPostRights(): void
    {
        if (!isset($_SESSION['LOGGED_USER']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            CheckConnectedLogger();
        }    
    }

    /**
     * Checks if the user is not logged in and has no login attempts. Redirects to logout if not.
     */
    function CheckRightsAndConnectionAttempt(): void
    {
        if (!isset($_SESSION['LOGGED_USER']) && !isset($_SESSION['LOGIN_ATTEMPT'])) {
            CheckNotConnectedLogger();
        }
    }

    /**
     * Checks if the user is attempting unauthorized access (logged in or wrong request method). Redirects to logout if not.
     */
    function CheckConnectionRights(): void
    {
        if (isset($_SESSION['LOGGED_USER']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            CheckConnectedLogger();
        }
    }

    /**
     * Logs unauthorized access attempt and redirects to logout.
     */
    function CheckConnectedLogger(): void
    {
        $file = GetCurrentFile();
        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, 'Unauthorized access attempt to ' . $file);
        header('Location:logout.php');
        exit();
    }

    /**
     * Logs unauthorized access attempt and redirects to logout.
     */
    function CheckNotConnectedLogger(): void
    {
        $file = GetCurrentFile();
        Logger(NULL, NULL, 2, 'Unauthorized access attempt to ' . $file);
        header('Location:logout.php');
        exit();
    }

    /**
     * Checks if user is blocked based on login attempts.
     * 
     * @param int $attempts Number of login attempts
     * @return bool Whether the user is blocked
     */
    function UserIsBlocked(int $attempts): bool {
        return $userIsBlocked = ($attempts >= 3);
    }

        /**
     * Validates a password based on specified criteria.
     * 
     * @param string $username The username to compare against the password
     * @param string $password The password to validate
     * @param string $password2 The confirmation password
     * @return string|null Error message if password validation fails, otherwise NULL
     */
    function PasswordIsValid(string $username, string $password, string $password2): ?string
    {
        $username = Sanitize($username);
        $password = Sanitize($password);
        $password2 = Sanitize($password2);

        if ($password === $password2) {
            if (!str_contains($password, ' ')) {
                $accents = array(
                    'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                    'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã',
                    'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ',
                    'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ',
                    'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę',
                    'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī',
                    'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ',
                    'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ',
                    'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 
                    'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 
                    'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ',
                    'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'
                );
                if (!StrContainsAnySubstring($password, $accents)) {
                    if (!str_contains(strtolower($password), strtolower($username))) {
                        $parameters = PasswordSelect();
                        $countDigits = preg_match_all('/[0-9]/', $password);
                        $countLowercase = preg_match_all('/[a-z]/', $password);
                        $countUppercase = preg_match_all('/[A-Z]/', $password);
                        $countSpecial = preg_match_all('/[!"#$%&\'()*+,\-./;<=>?@\\\^_`{|}~]/', $password);
                        if ($countDigits >= $parameters['n'] && $countLowercase >= $parameters['p'] && $countUppercase >= $parameters['q'] && $countSpecial >= $parameters['r']) {
                            return NULL;
                        } else {
                            $errorMessage = 'Erreur !\n\nLe mot de passe ne respecte pas les paramètres de configuration.';
                        }
                    } else {
                        $errorMessage = 'Erreur !\n\nLe mot de passe ne doit pas contenir le nom d utilisateur.';
                    }
                } else {
                    $errorMessage = 'Erreur !\n\nLe mot de passe ne doit pas contenir d accent.';
                }
            } else {
                $errorMessage = 'Erreur !\n\nLe mot de passe ne doit pas contenir d espace.';
            }
        } else {
            $errorMessage = 'Erreur !\n\nLes deux mots de passe sont différents.';
        }   
        return $errorMessage;
    }

    /**
     * Sanitizes input by trimming whitespace and converting special characters to HTML entities.
     * 
     * @param string $input The input string to sanitize
     * @return string The sanitized input string
     */
    function Sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Desanitizes input by converting HTML entities back to their corresponding characters.
     * 
     * @param string $input The input string to desanitize
     * @return string The desanitized input string
     */
    function Desanitize($input) {
        return htmlspecialchars_decode($input, ENT_QUOTES);
    }

    /**
     * Checks if a string contains any of the specified substrings.
     * 
     * @param string $haystack The string to search within
     * @param array $needles An array of substrings to search for
     * @return bool TRUE if any substring is found in the haystack, otherwise FALSE
     */
    function StrContainsAnySubstring(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Checks if a string contains any substring that is approximately similar to the specified needles.
     * 
     * This function uses a similarity threshold of 80% to determine if a substring is similar to any needle.
     * 
     * @param string $haystack The string to search within
     * @param array $needles An array of needles (strings) to compare against
     * @return bool TRUE if a similar substring is found, otherwise FALSE
     */
    function StrContainsAnySubstringApprox(string $haystack, array $needles): bool
    {
        $haystack = str_replace(['[', ']'], '', $haystack);
        $haystack = strtolower($haystack);
        $haystackExploded = explode(' ', Sanitize($haystack));
        foreach ($haystackExploded as $oneHaystack) {
            foreach ($needles as $needle) {
                similar_text($oneHaystack, strtolower($needle), $percentage);
                if ($percentage >= 80) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Replaces the last occurrence of a substring in a string with another substring.
     * 
     * @param string $patternToSearch The substring to search for
     * @param string $replacement The substring to replace with
     * @param string $string The original string to perform replacement on
     * @return string The string with the last occurrence replaced
     */
    function ReplaceLastOccurrence($patternToSearch, $replacement, $string): string
    {   
        $position = strrpos($string, $patternToSearch);
        if ($position !== false) {
            $string = substr_replace($string, $replacement, $position, strlen($patternToSearch));
        }
        return $string;
    }

    /**
     * Downloads a checklist using parameters to customize generation.
     * 
     * @param array|null $whereIs Array specifying conditions for checklist generation
     * @param array|null $orderBy Array specifying sorting criteria for checklist
     * @param array|null $erasedGoodpractices Array of erased good practices
     * @param array|null $erasedPrograms Array of erased programs
     * @param string|null $username Username for whom checklist is generated
     * @param string $profile User's profile (sanitized)
     * @param string $mode Output mode: 'pdf' or 'csv'
     * @return array Array containing success message and filename if successful, otherwise error message and filename
     */
    function DownloadChecklist(array $whereIs = NULL, array $orderBy = NULL, array $erasedGoodpractices = NULL, array $erasedPrograms = NULL, string $username = NULL, string $profile, string $mode): array
    {
        $whereIs = $whereIs ? json_encode($whereIs) : '';
        $whereIs = str_replace('"program_name":null,', '', $whereIs);
        $whereIs = str_replace('"phase_name":null,', '', $whereIs);
        $whereIs = str_replace(',"onekeyword":[""]', '', $whereIs);
        $whereIs = str_replace('"onekeyword":[""]', '', $whereIs);
        $orderBy = $orderBy ? json_encode($orderBy) : '';
        $erasedGoodpractices = $erasedGoodpractices ? implode(',', $erasedGoodpractices) : '';
        $erasedPrograms = $erasedPrograms ? json_encode($erasedPrograms) : '';
        $profile = Sanitize($profile);
        $username = $username ? Sanitize($username) : '';

        $currentDirectoryPath = getcwd();
        $checklistFilesDirectory = $currentDirectoryPath . "/checklist";
        require_once(__DIR__ . '/config/paths.php');
        $pythonChecklistGeneratorProgramPath = $currentDirectoryPath . "/python/checklist_generator.py";

        $command = "cd " . $checklistFilesDirectory . " && " . $python3BinaryPath . " " . $pythonChecklistGeneratorProgramPath . " ";
        $command .= "--where " . escapeshellarg($whereIs) . " ";
        $command .= "--order " . escapeshellarg($orderBy) . " ";
        $command .= "--erased_goodpractices " . escapeshellarg($erasedGoodpractices) . " ";
        $command .= "--erased_programs " . escapeshellarg($erasedPrograms) . " ";        
        if ($username) {
            $command .= " --username " . escapeshellarg($username) . " ";
        }
        $command .= "--profile " . escapeshellarg($profile) . " ";
        $outputFile = "checklist_" . ($username ? $username . '_' : '') . date('d-m-Y') . ($mode === 'pdf' ? '.pdf' : '.csv');
        $command .= "--output_format " . escapeshellarg($mode) . " ";
        $command .= "--output_file " . escapeshellarg($outputFile);
        
        exec($command, $output, $exit_code);
        $filename = $output[0];

        if (intval($exit_code) === 0) {                
            Logger($username, $profile, 0, "Successfully generated {$mode} checklist with filename : $filename");
            return array($exit_code, 'Succès !\n\nLa checklist : ' . $filename . ', au format ' . strtoupper($mode) . ' a bien été générée.', $filename);
        } else {
            Logger($username, $profile, 2, "Failed to generate {$mode} checklist");
            return array($exit_code, 'Erreur !\n\nLe programme python n a pas réussi à générer la checklist au format ' . strtoupper($mode) . '.');
        }
    }

    /**
     * Retrieves the user's IP address.
     * 
     * @return string The user's IP address or 'UNKNOWN' if not found
     */
    function getUserIP() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipArray as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return 'UNKNOWN';
    }

    /**
     * Logs events with user details, event type, and description.
     * 
     * @param string|null $username Username (sanitized)
     * @param string|null $profile User's profile (sanitized)
     * @param int $eventType Type of event (0 for information, 1 for warning, 2 for alarm)
     * @param string $description Description of the logged event (sanitized)
     */
    function Logger(string $username = NULL, string $profile = NULL, int $eventType, string $description): void
    {
        $ip = Sanitize(getUserIP());
        $log = '['.$ip.'] ';

        if ($username !== NULL && $profile !== NULL) {
            $log .= '['.Sanitize($username).'] ';
            switch (Sanitize($profile)) {
                case 'operator' :
                    $log .= '[Operator] ';
                    break;
                case 'admin' :
                    $log .= '[Admin] ';
                    break;
                case 'superadmin' :
                    $log .= '[Superadmin] ';
                    break;
            }
        } else {
            $log .= '[Unauthenticated] [Unauthenticated] ';
        }

        switch ($eventType) {
            case 0:
                $log .= '[Information] ';
                break;
            case 1:
                $log .= '[Warning] ';
                break;
            case 2:
                $log .= '[Alarm] ';
                break;
        }

        $log .= '['.Sanitize($description).']';
        ini_set("error_log", "./log/log.txt");
        error_log($log);
    }

    /**
     * Filters logs based on user privileges and search criteria.
     * 
     * @param string $userUsername Username of the logged-in user (sanitized)
     * @param string $userProfile Profile of the logged-in user (sanitized)
     * @param array $log Array of log lines to filter
     * @param int|null $day Day filter (optional)
     * @param string|null $month Month filter (optional)
     * @param int|null $year Year filter (optional)
     * @param array|null $evenementType Event types to filter (optional)
     * @param array|null $profiles Profiles to filter (optional)
     * @param string|null $logSearch Search string for log content (optional)
     * @return array|null Filtered array of log lines or NULL if no filters applied
     */
    function LogFilter(string $userUsername, string $userProfile, array $log, int $day = NULL, string $month = NULL, int $year = NULL, array $evenementType = NULL, array $profiles = NULL, string $logSearch = NULL): ?array
    {
        $userProfile = Sanitize($userProfile);

        if ($userProfile === 'admin' || $userProfile === 'superadmin') {
            if ($evenementType !== NULL) {
                foreach ($evenementType as $evenement) {
                    if ($evenement === 'Information' || $evenement === 'Warning' || $evenement === 'Alarm') {
                        $logFilters['evenement'][] = '['.$evenement.']';
                    }
                }
            }

            if ($profiles !== NULL) {
                foreach ($profiles as $profile) {
                    if ($profile === 'Operator' || $profile === 'Admin' || $profile === 'Superadmin') {
                        $logFilters['profile'][] = '['.$profile.']';
                    }
                }
            }

            if ($logSearch !== NULL && !empty($logSearch)) {
                $logFilters['search'] = explode(', ', Sanitize($logSearch));
            }

            if (isset($logFilters) && !empty($logFilters)) {
                $userUsername = Sanitize($userUsername);
                $filterNumber = count($logFilters);
                foreach ($log as $logLine) {
                    if (LogIsInTime($logLine, $day, $month, $year)) {
                        $passedFilterNumber = 0;
                        $logLine = Sanitize($logLine);
                        if ($userProfile === 'admin' && ((!str_contains($logLine, '[Admin]') || str_contains($logLine, $userUsername)) && !str_contains($logLine, '[Superadmin]'))) {
                            foreach ($logFilters as $logFilter) {
                                if (StrContainsAnySubstringApprox($logLine, $logFilter)) { 
                                    $passedFilterNumber += 1;
                                }
                            }
                            if ($passedFilterNumber === $filterNumber) {
                                $logFiltered[] = $logLine;
                            }
                        } elseif ($userProfile === 'superadmin') {
                            foreach ($logFilters as $logFilter) {
                                if (StrContainsAnySubstringApprox($logLine, $logFilter)) { 
                                    $passedFilterNumber += 1;
                                }
                            }
                            if ($passedFilterNumber === $filterNumber) {
                                $logFiltered[] = $logLine;
                            }
                        }
                    }
                }
                return $logFiltered;
            } elseif ($day !== NULL || $month !== NULL || $year !== NULL) {
                foreach ($log as $logLine) {
                    $logLine = Sanitize($logLine);
                    if (LogIsInTime($logLine, $day, $month, $year)) {
                        if ($userProfile === 'admin' && ((!str_contains($logLine, '[Admin]') || str_contains($logLine, $userUsername)) && !str_contains($logLine, '[Superadmin]'))) {
                            $logFiltered[] = $logLine; 
                        } elseif ($userProfile === 'superadmin') {
                            $logFiltered[] = $logLine;
                        }
                    }
                }
                return $logFiltered;
            } else {
                return $log;
            }
        }
    }

    /**
     * Checks if a log line falls within the specified time frame.
     * 
     * @param string $logLine Log line to check
     * @param int|null $day Day to match (optional)
     * @param string|null $month Month to match (optional)
     * @param int|null $year Year to match (optional)
     * @return bool TRUE if log line is within specified time frame, otherwise FALSE
     */
    function LogIsInTime(string $logLine, int $day = NULL, string $month = NULL, int $year = NULL): bool
    {   
        $logLine = Sanitize($logLine);
        $day = Sanitize($day);
        $month = Sanitize($month);
        $year = Sanitize($year);

        $position = strpos($logLine, ' ');
        $logTimeLine = substr($logLine, 1, $position);

        if (!($day === NULL || $day < 1 || $day > 31)) {
            if ($day < 10) {
                $day = '0'.$day;
            }
            if (!(substr($logTimeLine, 0, 2) === strval($day))) {
                return FALSE;
            }
        }

        if (!($month === NULL || $month === '' || str_contains($logTimeLine, $month))) {
            return FALSE;
        }

        if (!($year === NULL || $year < 2024 || str_contains($logTimeLine, strval($year)))) {
            return FALSE;
        }

        return TRUE;
    }
?>