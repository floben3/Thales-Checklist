<?php    
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckRightsAndConnectionAttempt();

    /**
     * Retrieves password parameters from the PASSWORD table.
     * 
     * @return array Password parameters
     */
    function PasswordSelect(): array
    {
        global $db;

        $sql = "SELECT * FROM PASSWORD";
        $stmt = $db->prepare($sql);
        $stmt->execute() or die(print_r($stmt->errorInfo()));
        $passwordParameters = $stmt->fetchall();
        $stmt->closeCursor();
        
        foreach ($passwordParameters as $parameters) {
            return $parameters;
        }
    }

    /**
     * Updates password parameters in the PASSWORD table.
     * 
     * @param int $n Parameter n
     * @param int $p Parameter p
     * @param int $q Parameter q
     * @param int $r Parameter r
     * @return void
     */
    function PasswordUpdate(int $n, int $p, int $q, int $r): void
    {
        global $db;

        $sql = "UPDATE PASSWORD SET n = :n, p = :p, q = :q, r = :r";
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':n', $n, PDO::PARAM_INT);
        $stmt->bindParam(':p', $p, PDO::PARAM_INT);
        $stmt->bindParam(':q', $q, PDO::PARAM_INT);
        $stmt->bindParam(':r', $r, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * Retrieves users from the USERS table based on optional parameters.
     * 
     * @param array|null $orderBy Array with order by parameters [field, direction]
     * @param string|null $profile Profile type ('admin' or 'superadmin')
     * @return array Users data
     */
    function UsersSelect(array $orderBy = NULL, string $profile = NULL): array
    {
        global $db;

        if ($profile === 'admin') {
            $sql = "SELECT * FROM USERS WHERE profile != 'superadmin' AND profile != 'admin'";
        } elseif ($profile === 'superadmin') {
            $sql = "SELECT * FROM USERS WHERE profile != 'superadmin'";
        } else {
            $sql = "SELECT * FROM USERS";
        }

        if ($orderBy !== NULL) {
            $order = $orderBy[0];
            $direction = $orderBy[1];
            if (($order === 'username' || $order === 'firstname' || $order === 'lastname' || $order === 'profile' || $order === 'attempts') && ($direction === 'asc' || $direction === 'desc')) {
                $sql .= " ORDER BY {$order} {$direction}";
            }
        }
    
        $stmt = $db->prepare($sql);
        $stmt->execute() or die(print_r($stmt->errorInfo()));
    
        $users = $stmt->fetchAll();
        $stmt->closeCursor();
    
        return $users;
    }    

    /**
     * Updates user attempts in the USERS table.
     * 
     * @param int $userId User ID
     * @param string $option Option ('reset' or 'increment')
     * @return void
     */
    function UserAttempts(int $userId, string $option): void 
    {
        global $db;

        $sql = "UPDATE USERS ";
        if ($option === 'reset') {
            $sql .= "SET attempts = 0 ";
        } elseif ($option === 'increment') {
            $sql .= "SET attempts = attempts + 1 ";
        }   
        $sql .= "WHERE user_id = :userId";
        
        $stmt = $db->prepare($sql);
        $markers = array('userId' => $userId);
        $stmt->execute($markers) or die(print_r($stmt->errorInfo()));
        $stmt->closeCursor();
    }

    /**
     * Checks if a user with given username exists in the USERS table.
     * 
     * @param string $username Username to check
     * @return bool True if user exists, false otherwise
     */
    function UserIsInBDD(string $username): bool
    {
        $users = UsersSelect();
        foreach ($users as $user) {
            if ($username === $user['username']) {
                return TRUE;
            } 
        }
        return FALSE;
    }

    /**
     * Adds a new user to the USERS table.
     * 
     * @param string $username Username
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param string $password Password
     * @param string|null $profile Profile type ('operator' or 'admin')
     * @return bool True if user was successfully added, false if user already exists
     */
    function UserAppend(string $username, string $firstname, string $lastname, string $password, string $profile = NULL): bool
    {
        global $db;
        
        if (!UserIsInBDD($username)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO USERS (username, firstname, lastname, profile, password, attempts) VALUES (:username, :firstname, :lastname, :profile, :password, 0)";
            $stmt = $db->prepare($sql);
            $markers = array('username' => Sanitize($username), 'firstname' => Sanitize($firstname), 'lastname' => Sanitize($lastname), 'password' => Sanitize($hash));
            if ($profile === NULL || $profile === 'operator') {
                $markers['profile'] = 'operator';
            } elseif ($profile === 'admin') {
                $markers['profile'] = 'admin';
            }
            $stmt->execute($markers) or die(print_r($stmt->errorInfo()));
            $stmt->closeCursor();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Retrieves the username associated with a given user ID from the USERS table.
     * 
     * @param int $userId User ID
     * @return string Username
     */
    function UserWhatIsName(int $userId): string
    {
        global $db;

        $sql = "SELECT username FROM USERS WHERE user_id = :userId;";
        $stmt = $db->prepare($sql);
        $markers['userId'] = Sanitize($userId);
        $stmt->execute($markers) or die(print_r($stmt->errorInfo()));
        $username = $stmt->fetch();
        $stmt->closeCursor();
        return Sanitize($username[0]);
    }

    /**
     * Deletes a user from the USERS table based on user ID and profile type.
     * 
     * @param int $userId User ID
     * @param string $profile Profile type ('superadmin' or 'admin')
     * @return void
     */
    function UserDelete(int $userId, string $profile): void
    {   
        global $db;

        if ($profile === 'superadmin') {
            $sql = "DELETE FROM USERS WHERE user_id = :userId AND profile != 'superadmin'";
        } elseif ($profile === 'admin') {
            $sql = "DELETE FROM USERS WHERE user_id = :userId AND profile = 'operator'";
        }
        $stmt = $db->prepare($sql);
        $markers['userId'] = Sanitize($userId);
        $stmt->execute($markers) or die(print_r($stmt->errorInfo()));
        $stmt->closeCursor();
    }

    /**
     * Resets a user's password in the USERS table based on user ID and profile type.
     * 
     * @param int $loggedUserId Logged User ID
     * @param int $userId User ID
     * @param string $newPassword New password
     * @param string $profile Profile type ('superadmin' or 'admin')
     * @return void
     */
    function UserResetPassword(int $loggedUserId, int $userId, string $newPassword, string $profile): void
    {
        global $db;
        
        $newHash = password_hash(Sanitize($newPassword), PASSWORD_BCRYPT);

        if ($loggedUserId === $userId) {
            if ($profile === 'superadmin') {
                $sql = "UPDATE USERS SET password = :newHash, attempts = 0 WHERE user_id = :userId AND profile = 'superadmin'";
            } elseif ($profile === 'admin') {
                $sql = "UPDATE USERS SET password = :newHash, attempts = 0 WHERE user_id = :userId AND profile = 'admin'";
            }
        } else {
            if ($profile === 'superadmin') {
                $sql = "UPDATE USERS SET password = :newHash, attempts = 0 WHERE user_id = :userId AND profile != 'superadmin'";
            } elseif ($profile === 'admin') {
                $sql = "UPDATE USERS SET password = :newHash, attempts = 0 WHERE user_id = :userId AND profile = 'operator'";
            }
        }
        $stmt = $db->prepare($sql);
        $markers = array('newHash' => $newHash, 'userId' => Sanitize($userId));
        $stmt->execute($markers) or die(print_r($stmt->errorInfo()));
        $stmt->closeCursor();
    }

    /**
     * Retrieves good practices from the database based on specified criteria.
     * 
     * @param array|null $whereIs Filters for WHERE clause
     * @param array|null $orderBy Order by [field, direction]
     * @param array|null $erasedGoodpractices IDs of erased good practices
     * @param array|null $erasedPrograms Erased programs data
     * @param string $profile User profile ('admin' or 'superadmin')
     * @return array Good practices data
     */
    function GoodPracticesSelect(array $whereIs = NULL, array $orderBy = NULL, array $erasedGoodpractices = NULL, array $erasedPrograms = NULL, string $profile): array
    {
        global $db;

        if ($profile !== 'admin' && $profile !== 'superadmin') {
            $sql = " 
                SELECT 
                    GOODPRACTICE.goodpractice_id,
                    GROUP_CONCAT(DISTINCT PROGRAM.program_name ORDER BY PROGRAM.program_name SEPARATOR ', ') AS program_names,
                    PHASE.phase_name,
                    GOODPRACTICE.item,
                    GROUP_CONCAT(DISTINCT KEYWORD.onekeyword ORDER BY KEYWORD.onekeyword SEPARATOR ', ') AS keywords
                FROM GOODPRACTICE
                INNER JOIN PHASE ON GOODPRACTICE.phase_id = PHASE.phase_id
                INNER JOIN GOODPRACTICE_PROGRAM ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_PROGRAM.goodpractice_id
                INNER JOIN PROGRAM ON GOODPRACTICE_PROGRAM.program_id = PROGRAM.program_id
                INNER JOIN GOODPRACTICE_KEYWORD ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_KEYWORD.goodpractice_id
                INNER JOIN KEYWORD ON GOODPRACTICE_KEYWORD.keyword_id = KEYWORD.keyword_id
                WHERE GOODPRACTICE.is_hidden = FALSE AND GOODPRACTICE_PROGRAM.is_hidden = FALSE
            ";
        } else {
            $sql = "
                SELECT
                    GOODPRACTICE.goodpractice_id,
                    GOODPRACTICE.is_hidden AS goodpractice_is_hidden,
                    GROUP_CONCAT(DISTINCT CONCAT(PROGRAM.program_name, ':', GOODPRACTICE_PROGRAM.is_hidden) ORDER BY PROGRAM.program_name SEPARATOR ', ') AS program_names,
                    PHASE.phase_name,
                    GOODPRACTICE.item,
                    GROUP_CONCAT(DISTINCT KEYWORD.onekeyword ORDER BY KEYWORD.onekeyword SEPARATOR ', ') AS keywords
                FROM GOODPRACTICE
                INNER JOIN PHASE ON GOODPRACTICE.phase_id = PHASE.phase_id
                INNER JOIN GOODPRACTICE_PROGRAM ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_PROGRAM.goodpractice_id
                INNER JOIN PROGRAM ON GOODPRACTICE_PROGRAM.program_id = PROGRAM.program_id
                INNER JOIN GOODPRACTICE_KEYWORD ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_KEYWORD.goodpractice_id
                INNER JOIN KEYWORD ON GOODPRACTICE_KEYWORD.keyword_id = KEYWORD.keyword_id
            ";
        }

        $params = array();

        if ($whereIs !== NULL) {
            if ($profile !== 'admin' && $profile !== 'superadmin') {
                $whereClause = " AND ( ";
            } else {
                $whereClause = " WHERE ( ";
            }
            foreach ($whereIs as $column => $filters) {
                if (!empty($column[0]) && !empty($filters[0])) {
                    foreach ($filters as $index => $value) {
                        if (!empty($value)) {
                            $paramName = ":$column$index";
                            $whereClause .= "$column = $paramName OR ";
                            $params[$paramName] = $value;
                        }
                    }
                    $whereClause = ReplaceLastOccurrence('OR ', '', $whereClause);
                    $whereClause .= (') AND ( ');
                } 
            }
            $whereClause = ReplaceLastOccurrence(' AND ( ', '', $whereClause);
            $whereClause = $whereClauseStart.$whereClause;
            if ($whereClause !== ' AND ( ' && $whereClause !== ' WHERE ( ') {
                $sql .= $whereClause;
            }
        }

        if ($erasedGoodpractices !== NULL) {
            $excludedIds = implode(", ", $erasedGoodpractices);
            $sql .= " AND GOODPRACTICE.goodpractice_id NOT IN ($excludedIds)";
        }
        
        $sql .= ' GROUP BY GOODPRACTICE.item';

        if ($orderBy !== NULL) {
            $order = $orderBy[0];
            $direction = $orderBy[1];
            if (($order === 'program_names' || $order === 'phase_name' || $order === 'item' || $order === 'keywords') && ($direction === 'asc' || $direction === 'desc')) {
                $sql .= " ORDER BY {$order} {$direction}";
            }
        }
        $sql .= ";";

        $stmt = $db->prepare($sql);
        foreach ($params as $paramName => $value) {
            $stmt->bindValue($paramName, $value);
        }
        $stmt->execute() or die(print_r($stmt->errorInfo()));
        $goodPractices = $stmt->fetchAll();
        $stmt->closeCursor();

        if ($erasedPrograms !== NULL) {
            foreach ($goodPractices as $key => &$goodPractice) {
                $goodPractice['program_names'] = EraseProgramNames($goodPractice['program_names'], $erasedPrograms['id'.$goodPractice['goodpractice_id']], $profile);
                if (empty($goodPractice['program_names'])) {
                    unset($goodPractices[$key]);
                }
            }
            unset($goodPractice);
        }

        return $goodPractices;
    }

    /**
     * Retrieves programs from the PROGRAM table based on optional filter.
     * 
     * @param string|null $all Retrieve all programs if 'all' is specified
     * @return array Array of programs or program names
     */
    function ProgramSelect(string $all = NULL): array
    {
        global $db;
        if ($all === 'all') {
            $sql = "SELECT * FROM PROGRAM";
        } else {
            $sql = "SELECT program_name FROM PROGRAM";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $programs = $stmt->fetchAll();
        $stmt->closeCursor();
        return $programs;
    }

    /**
     * Retrieves phases from the PHASE table based on optional filter.
     * 
     * @param string|null $all Retrieve all phases if 'all' is specified
     * @return array Array of phases or phase names
     */
    function PhaseSelect(string $all = NULL): array
    {
        global $db;
        if ($all === 'all') {
            $sql = "SELECT * FROM PHASE";
        } else {
            $sql = "SELECT phase_name FROM PHASE";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $phases = $stmt->fetchAll();
        $stmt->closeCursor();
        return $phases;
    }

    /**
     * Retrieves keywords from the KEYWORD table based on optional filter.
     * 
     * @param string|null $all Retrieve all keywords if 'all' is specified
     * @return array Array of keywords or keyword names
     */
    function KeywordSelect(string $all = NULL): array
    {
        global $db;
        if ($all === 'all') {
            $sql = "SELECT * FROM KEYWORD";
        } else {
            $sql = "SELECT onekeyword FROM KEYWORD";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $keywords = $stmt->fetchAll();
        $stmt->closeCursor();
        return $keywords;
    }

    /**
     * Delete a program name from the PROGRAM table, or a keyword from the KEYWORD table.
     * 
     * @param string $fieldType Field name
     * @param string $value Value to delete
     * @param string $profile User profile
     * @return int Status code indicating the result of the operation
     * @throws Exception If any database error occurs
     */
    function DeleteField(string $fieldType, string $value, string $profile): int
    {
        global $db;

        $SUCCESS_PROGRAM_DELETED = 2;
        $SUCCESS_KEYWORD_DELETED = 3;
        $FAILURE_NO_PROGRAM_FOUND = 0;
        $FAILURE_NO_KEYWORD_FOUND = 1;
        $FAILURE_INVALID_FIELD_TYPE = 4;
        $FAILURE_UNAUTHORIZED = 5;

        if ($profile === 'admin' || $profile === 'superadmin') {
            try {
                $db->beginTransaction();

                if ($fieldType === 'program_name') {
                    $sql = "SELECT program_id FROM PROGRAM WHERE program_name = :valueToDelete";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['valueToDelete' => $value]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $primaryKey = $result['program_id'];
                        $sql = "DELETE FROM GOODPRACTICE_PROGRAM WHERE program_id = :primaryKey";
                        $stmt = $db->prepare($sql);
                        $stmt->execute(['primaryKey' => $primaryKey]);

                        $sql = "DELETE FROM PROGRAM WHERE program_id = :primaryKey";
                        $stmt = $db->prepare($sql);
                        $stmt->execute(['primaryKey' => $primaryKey]);

                        $db->commit();
                        return $SUCCESS_PROGRAM_DELETED;
                    } else {
                        $db->rollBack();
                        return $FAILURE_NO_PROGRAM_FOUND;
                    }

                } elseif ($fieldType === 'onekeyword') {
                    $sql = "SELECT keyword_id FROM KEYWORD WHERE onekeyword = :valueToDelete";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['valueToDelete' => $value]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $primaryKey = $result['keyword_id'];

                        // Get all goodpractice_id associated with this keyword_id
                        $sql = "SELECT goodpractice_id FROM GOODPRACTICE_KEYWORD WHERE keyword_id = :primaryKey";
                        $stmt = $db->prepare($sql);
                        $stmt->execute(['primaryKey' => $primaryKey]);
                        $goodPracticeIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

                        // Delete entries from GOODPRACTICE_KEYWORD
                        $sql = "DELETE FROM GOODPRACTICE_KEYWORD WHERE keyword_id = :primaryKey";
                        $stmt = $db->prepare($sql);
                        $stmt->execute(['primaryKey' => $primaryKey]);

                        // Delete the keyword
                        $sql = "DELETE FROM KEYWORD WHERE keyword_id = :primaryKey";
                        $stmt = $db->prepare($sql);
                        $stmt->execute(['primaryKey' => $primaryKey]);

                        // Ensure each good practice has at least one keyword
                        foreach ($goodPracticeIds as $goodPracticeId) {
                            EnsureGoodPracticeHasKeyword($goodPracticeId);
                        }

                        $db->commit();
                        return $SUCCESS_KEYWORD_DELETED;
                    } else {
                        $db->rollBack();
                        return $FAILURE_NO_KEYWORD_FOUND;
                    }
                } else {
                    $db->rollBack();
                    return $FAILURE_INVALID_FIELD_TYPE;
                }

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            return $FAILURE_UNAUTHORIZED;
        }
    }


    /**
     * Ensure that a good practice has an associated keyword in the GOODPRACTICE_KEYWORD table.
     * If the good practice is not associated, add an entry with the good practice primary key
     * and the primary key 1 of the keywords.
     * 
     * @param int $goodPracticeId Primary key of the good practice
     */
    function EnsureGoodPracticeHasKeyword(int $goodPracticeId): void
    {
        global $db;

        $sql = "SELECT COUNT(*) FROM GOODPRACTICE_KEYWORD WHERE goodpractice_id = :goodPracticeId";
        $stmt = $db->prepare($sql);
        $stmt->execute(['goodPracticeId' => $goodPracticeId]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $sql = "INSERT INTO GOODPRACTICE_KEYWORD (goodpractice_id, keyword_id) VALUES (:goodPracticeId, 1)";
            $stmt = $db->prepare($sql);
            $stmt->execute(['goodPracticeId' => $goodPracticeId]);
        }
    }

    /**
     * Validates and filters selected keywords against available keywords in the database.
     * 
     * @param string|null $keywordsSelection Comma-separated string of selected keywords
     * @return array Array containing two arrays: validated keywords and wrong keywords
     */
    function ValidateKeywordsSelection(string $keywordsSelection = NULL): array
    {   
        if ($keywordsSelection === NULL || empty($keywordsSelection)) {
            return array(array(''),array(''));
        }
        $keywordSelect = KeywordSelect();
        foreach ($keywordSelect as $keyword) {
            $keywords[] = $keyword[0];
        }
        $keywordsSelection = explode(', ', $keywordsSelection);
        $wrongKeywords = array_diff($keywordsSelection, $keywords);
        $keywordsSelection = array_diff($keywordsSelection, $wrongKeywords);
        return array($keywordsSelection, $wrongKeywords);
    }

        /**
     * Inserts a new item into the GOODPRACTICE table.
     * 
     * @param string $item The item to insert
     * @param int $phaseId The ID of the phase associated with the item
     * @return int The ID of the newly inserted item
     */
    function InsertGoodpracticeItem(string $item, int $phaseId): int
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO GOODPRACTICE (item, phase_id) VALUES (:item, :phaseId)");
        $stmt->bindParam(':item', Desanitize($item), PDO::PARAM_STR);
        $stmt->bindParam(':phaseId', Desanitize($phaseId), PDO::PARAM_INT);
        $stmt->execute();
        return $db->lastInsertId();
    }

    /**
     * Inserts a relationship between a good practice and a program into the GOODPRACTICE_PROGRAM table.
     * 
     * @param int $goodpracticeId The ID of the good practice
     * @param int $programId The ID of the program
     */
    function InsertGoodpracticeProgram(int $goodpracticeId, int $programId): void
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO GOODPRACTICE_PROGRAM (goodpractice_id, program_id) VALUES (:goodpracticeId, :programId)");
        $stmt->bindParam(':goodpracticeId', Desanitize($goodpracticeId), PDO::PARAM_INT);
        $stmt->bindParam(':programId', Desanitize($programId), PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Inserts a relationship between a good practice and a keyword into the GOODPRACTICE_KEYWORD table.
     * 
     * @param int $goodpracticeId The ID of the good practice
     * @param int $keywordId The ID of the keyword
     */
    function InsertGoodpracticeKeyword(int $goodpracticeId, int $keywordId): void
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO GOODPRACTICE_KEYWORD (goodpractice_id, keyword_id) VALUES (:goodpracticeId, :keywordId)");
        $stmt->bindParam(':goodpracticeId', Desanitize($goodpracticeId), PDO::PARAM_INT);
        $stmt->bindParam(':keywordId', Desanitize($keywordId), PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Retrieves the phase ID from the PHASE table based on the phase name.
     * If the phase does not exist, inserts it into the PHASE table and returns its ID.
     * 
     * @param string $phaseName The name of the phase
     * @return int The ID of the phase
     */
    function GetPhaseId(string $phaseName): int
    {
        global $db;
        $stmt = $db->prepare("SELECT phase_id FROM PHASE WHERE phase_name = :phaseName");
        $stmt->bindParam(':phaseName', Desanitize($phaseName), PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            return $result['phase_id'];
        } else {
            return InsertPhase($phaseName);
        }
    }

    /**
     * Inserts a new phase into the PHASE table.
     * 
     * @param string $phaseName The name of the phase to insert
     * @return int The ID of the newly inserted phase
     */
    function InsertPhase(string $phaseName): int
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO PHASE (phase_name) VALUES (:phaseName)");
        $stmt->bindParam(':phaseName', Desanitize($phaseName), PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    }

    /**
     * Retrieves the program ID from the PROGRAM table based on the program name.
     * If the program does not exist, inserts it into the PROGRAM table and returns its ID.
     * 
     * @param string $programName The name of the program
     * @return int The ID of the program
     */
    function GetProgramId(string $programName): int
    {
        global $db;
        $stmt = $db->prepare("SELECT program_id FROM PROGRAM WHERE program_name = :programName");
        $stmt->bindParam(':programName', Desanitize($programName), PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            return $result['program_id'];
        } else {
            return InsertProgram($programName);
        }
    }

    /**
     * Inserts a new program into the PROGRAM table.
     * 
     * @param string $programName The name of the program to insert
     * @return int The ID of the newly inserted program
     */
    function InsertProgram(string $programName): int
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO PROGRAM (program_name) VALUES (:programName)");
        $stmt->bindParam(':programName', Desanitize($programName), PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    }

    /**
     * Retrieves the keyword ID from the KEYWORD table based on the keyword name.
     * If the keyword does not exist, inserts it into the KEYWORD table and returns its ID.
     * 
     * @param string $keyword The keyword
     * @return int The ID of the keyword
     */
    function GetKeywordId(string $keyword): int
    {
        global $db;
        $stmt = $db->prepare("SELECT keyword_id FROM KEYWORD WHERE onekeyword = :keyword");
        $stmt->bindParam(':keyword', Desanitize($keyword), PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            return $result['keyword_id'];
        } else {
            return InsertKeyword($keyword);
        }
    }

    /**
     * Inserts a new keyword into the KEYWORD table.
     * 
     * @param string $keyword The keyword to insert
     * @return int The ID of the newly inserted keyword
     */
    function InsertKeyword(string $keyword): int
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO KEYWORD (onekeyword) VALUES (:keyword)");
        $stmt->bindParam(':keyword', Desanitize($keyword), PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    }

    /**
     * Inserts a new good practice into the database with associated programs and keywords.
     * 
     * @param array $programNames Array of program names associated with the good practice
     * @param string $phaseName Name of the phase associated with the good practice
     * @param string $item The item of the good practice
     * @param array $keywords Array of keywords associated with the good practice
     */
    function InsertGoodpractice(array $programNames, string $phaseName, string $item, array $keywords): void
    {
        global $db;
        $phaseId = GetPhaseId($phaseName);
        $goodpracticeId = InsertGoodpracticeItem($item, $phaseId);
        foreach ($programNames as $programName) {
            $programId = GetProgramId($programName);
            InsertGoodpracticeProgram($goodpracticeId, $programId);
        }
        foreach ($keywords as $keyword) {
            $keywordId = GetKeywordId($keyword);
            InsertGoodpracticeKeyword($goodpracticeId, $keywordId);
        }
    }

    /**
     * Duplicates the associations of programs to a good practice.
     * 
     * @param array $programNames Array of program names associated with the good practice
     * @param int $goodpracticeId The ID of the good practice to duplicate
     */
    function DuplicateGoodpractice(array $programNames, int $goodpracticeId): void
    {
        foreach ($programNames as $programName) {
            $programId = GetProgramId(Sanitize($programName));
            InsertGoodpracticeProgram($goodpracticeId, $programId);
        }
    }

    /**
     * Erases specified program names from a string based on the user's profile.
     * 
     * @param string $programNames Comma-separated string of program names
     * @param array|null $erasedProgramNames Array of program names to erase
     * @param string $profile User profile ('admin', 'superadmin', etc.)
     * @return string Sanitized string of program names after erasure
     */
    function EraseProgramNames(string $programNames, array $erasedProgramNames = NULL, string $profile): string
    {
        if ($erasedProgramNames !== NULL) {
            if ($profile !== 'admin' && $profile !== 'superadmin') {
                return Sanitize(implode(', ', array_diff(explode(', ', $programNames), $erasedProgramNames)));
            } else {
                $programArray = [];
                foreach (explode(', ', $programNames) as $programName) {
                    $programArray[$programName] = substr($programName, 0, -2);
                }
                return Sanitize(implode(', ',array_keys(array_diff($programArray, $erasedProgramNames))));
            }
        } else {
            return Sanitize($programNames);
        }
    }

        /**
     * Deletes a good practice and its associations from the database.
     * If $programNames is provided, only deletes associations with those programs.
     * 
     * @param int $goodpracticeId The ID of the good practice to delete
     * @param array|null $programNames Array of program names to restrict deletion (optional)
     */
    function DeleteGoodpractice(int $goodpracticeId, array $programNames = NULL): void
    {
        global $db;

        try {
            $db->beginTransaction();

            if (empty($programNames)) {
                $sql1 = "DELETE FROM GOODPRACTICE_PROGRAM WHERE goodpractice_id = :goodpractice_id;";
                $sql2 = "DELETE FROM GOODPRACTICE_KEYWORD WHERE goodpractice_id = :goodpractice_id;";
                $sql3 = "DELETE FROM GOODPRACTICE WHERE goodpractice_id = :goodpractice_id;";

                $stmt1 = $db->prepare($sql1);
                $stmt1->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
                $stmt1->execute();
                $stmt1->closeCursor();

                $stmt2 = $db->prepare($sql2);
                $stmt2->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
                $stmt2->execute();
                $stmt2->closeCursor();

                $stmt3 = $db->prepare($sql3);
                $stmt3->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
                $stmt3->execute();
                $stmt3->closeCursor();
            } else {
                $sql = "DELETE FROM GOODPRACTICE_PROGRAM WHERE goodpractice_id = :goodpractice_id AND program_id = :program_id";
                foreach ($programNames as $programName) {
                    $programId = GetProgramId($programName);
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
                    $stmt->bindValue(':program_id', $programId, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();
                }
            }

            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            throw new Exception("Error deleting good practice: " . $e->getMessage());
        }
    }

    /**
     * Marks a good practice as hidden by updating its 'is_hidden' status in the database.
     * If $programNames is provided, only marks associations with those programs as hidden.
     * 
     * @param int $goodpracticeId The ID of the good practice to mark as hidden
     * @param array|null $programNames Array of program names to restrict hiding (optional)
     */
    function DeleteOperatorGoodpractice(int $goodpracticeId, array $programNames = NULL): void
    {
        global $db;

        try {
            $db->beginTransaction();

            if (empty($programNames)) {
                $sql = "UPDATE GOODPRACTICE SET is_hidden = TRUE WHERE goodpractice_id = :goodpractice_id;";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
                $stmt->execute();
                $stmt->closeCursor();
            } else {
                $sql = "UPDATE GOODPRACTICE_PROGRAM SET is_hidden = TRUE WHERE goodpractice_id = :goodpractice_id AND program_id = :program_id";
                foreach ($programNames as $programName) {
                    $programId = GetProgramId($programName);
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
                    $stmt->bindValue(':program_id', $programId, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();
                }
            }

            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            throw new Exception("Error deleting good practice: " . $e->getMessage());
        }
    }

    /**
     * Restores a hidden good practice by updating its 'is_hidden' status in the database.
     * 
     * @param int $goodpracticeId The ID of the good practice to restore
     */
    function RestoreGoodpractice(int $goodpracticeId): void
    {
        global $db;
        $sql = "
            UPDATE GOODPRACTICE SET is_hidden = FALSE WHERE goodpractice_id = :goodpractice_id;
            UPDATE GOODPRACTICE_PROGRAM SET is_hidden = FALSE WHERE goodpractice_id = :goodpractice_id;
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':goodpractice_id', $goodpracticeId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
    }
?>