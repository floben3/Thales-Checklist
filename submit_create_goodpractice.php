<?php
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckPostRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;

    // Handle 'select-all-programs' form submission
    if ($postData['submit'] === 'select-all-programs') {
        // Toggle CREATE_ALL_PROGRAMS session variable
        if ($_SESSION['CREATE_ALL_PROGRAMS']) {
            $_SESSION['CREATE_ALL_PROGRAMS'] = 0;
            unset($_SESSION['GOODPRACTICES_CREATION']['program_name']);
        } else {
            $_SESSION['CREATE_ALL_PROGRAMS'] = 1;
        }

        $_SESSION['CREATE_PHASE_CHECK'] = $postData['phasesSelection']; // Store phases selection in session

        // Prepare keywords selection chain and store in session
        if (isset($_SESSION['GOODPRACTICES_CREATION']['onekeyword'])) {
            $keywordsSelectionChain = Sanitize(implode(', ', $_SESSION['GOODPRACTICES_CREATION']['onekeyword']));
        } else {
            $keywordsSelectionChain = '';
        }
        $_SESSION['CREATE_KEYWORDS_CHECK'] = str_replace($keywordsSelectionChain, '', Sanitize($postData['keywordSearch']));

        $_SESSION['CREATE_ADD_KEYWORDS_CHECK'] = Sanitize($postData['addKeyword']); // Store additional keywords selection in session
        $_SESSION['CREATE_ADD_PROGRAMS_CHECK'] = Sanitize($postData['addProgram']); // Store additional programs selection in session

    // Handle 'reset' form submission
    } elseif ($postData['submit'] === 'reset') {
        $item = rtrim(Sanitize($postData['goodpractice'])); // Sanitize and trim good practice item
        if (!empty($item)) {
            $_SESSION['GOODPRACTICE_TEXT'] = $item;
        }
        // Unset all relevant session variables
        unset($_SESSION['GOODPRACTICES_CREATION']);
        unset($_SESSION['GOODPRACTICES_KEYWORDS_CREATION_MESSAGE']);
        unset($_SESSION['CREATE_PHASE_CHECK']);
        unset($_SESSION['CREATE_KEYWORDS_CHECK']);
        unset($_SESSION['CREATE_ADD_KEYWORDS_CHECK']);
        unset($_SESSION['CREATE_ADD_PROGRAMS_CHECK']);
        $_SESSION['CREATE_ALL_PROGRAMS'] = 0;

    // Handle 'submit' form submission
    } elseif ($postData['submit'] === 'submit') {
        $programsSelection = $postData['programsSelection'];
        $_SESSION['GOODPRACTICES_CREATION']['program_name'] = $programsSelection; // Store program name selection in session
        
        if ($_SESSION['LOGGED_USER']['profile'] === 'admin' || $_SESSION['LOGGED_USER']['profile'] === 'superadmin') {
            $addProgram = Sanitize($postData['addProgram']); // Sanitize additional program names
            $_SESSION['GOODPRACTICES_CREATION']['addProgram'] = $addProgram;
        } else {
            $addProgram = '';
        }

        $_SESSION['GOODPRACTICES_CREATION']['phase_name'] = $postData['phasesSelection']; // Store phase name selection in session

        // Validate and store keywords selection in session
        $validateKeywordsSelection = ValidateKeywordsSelection(Sanitize($postData['keywordSearch']));
        $keywordsSelection = $validateKeywordsSelection[0];
        $wrongKeywords = Sanitize(implode(', ', $validateKeywordsSelection[1]));
        $_SESSION['GOODPRACTICES_CREATION']['onekeyword'] = $keywordsSelection;

        if ($_SESSION['LOGGED_USER']['profile'] === 'admin' || $_SESSION['LOGGED_USER']['profile'] === 'superadmin') {
            $addKeyword = Sanitize($postData['addKeyword']); // Sanitize additional keywords
            $_SESSION['GOODPRACTICES_CREATION']['addOnekeyword'] = $addKeyword; 
        } else {
            $addKeyword = '';
        }

        // Handle error message if there are invalid keywords
        if (!empty($wrongKeywords)) {
            $_SESSION['GOODPRACTICE_CREATION_MESSAGE'] = 'Erreur !\n\nUn ou des mots-clés sont invalides.';
            $_SESSION['GOODPRACTICES_KEYWORDS_CREATION_MESSAGE'] = 'Erreur avec les mots-clés suivant : '.$wrongKeywords;
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Failed to create a goodpractice, wrong keywords');
        } else {
            // Proceed if no invalid keywords
            if (!empty($postData['programsSelection']) || !empty($postData['addProgram'])) {
                $item = rtrim(Sanitize($postData['goodpractice'])); // Sanitize and trim good practice item

                // Handle error message if good practice is empty
                if (!empty($item)) {
                    unset($_SESSION['GOODPRACTICE_TEXT']);

                    if (!empty($programsSelection && !empty($addProgram))) {
                        $programNames = array_merge($programsSelection, explode(', ', $addProgram));
                    } elseif (!empty($programsSelection)) {
                        $programNames = $programsSelection;
                    } elseif (!empty($addProgram)) {
                        $programNames = explode(', ', $addProgram);
                    }

                    $phaseName = Sanitize($postData['phasesSelection']);

                    // Merge keywords and handle different scenarios
                    if (!empty($keywordsSelection[0]) && !empty($addKeyword)) {
                        $keywords = array_merge($keywordsSelection, explode(', ', $addKeyword));
                    } elseif (!empty($keywordsSelection[0])) {
                        $keywords = $keywordsSelection;
                    } elseif (!empty($addKeyword)) {
                        $keywords = explode(', ', $addKeyword);
                    } else {
                        $keywords = array(' ');
                    }

                    // Insert good practice into database
                    InsertGoodpractice($programNames, $phaseName, $item, $keywords);
                    
                    // Set success message for good practice creation
                    $_SESSION['GOODPRACTICE_CREATION_MESSAGE'] = 'Succès !\n\nLa bonne pratique : \n\n'.$item.'\n\nA bien été créée.';

                    // Log successful creation of good practice
                    Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Successfully create a goodpractice');

                    // Unset additional programs from session after successful creation
                    unset($_SESSION['GOODPRACTICES_CREATION']['addProgram']);

                    // Unset additional keywords from session after successful creation
                    unset($_SESSION['GOODPRACTICES_CREATION']['addOnekeyword']);
                        
                } else {
                    // Set error message if the good practice text is empty
                    $_SESSION['GOODPRACTICE_CREATION_MESSAGE'] = 'Erreur !\n\nLa bonne pratique ne doit pas être vide.';
                    
                    // Log failure to create a good practice due to empty good practice text
                    Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Failed to create a goodpractice, empty goodpractice');
                }
            } else {
                // If no programs were selected for the good practice, set an error message
                $_SESSION['GOODPRACTICE_CREATION_MESSAGE'] = 'Erreur !\n\nVeuillez sélectionner au moins un programme pour la bonne pratique.';

                // Log failure to create a good practice due to no program selection
                Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 0, 'Failed to create a goodpractice, no program selected');
            }
            // Unset the keywords error message from session
            unset($_SESSION['GOODPRACTICES_KEYWORDS_CREATION_MESSAGE']);
        }
        // Unset session variables related to keyword selection and creation flags
        unset($_SESSION['CREATE_PHASE_CHECK']);
        unset($_SESSION['CREATE_KEYWORDS_CHECK']);
        unset($_SESSION['CREATE_ADD_KEYWORDS_CHECK']);
        unset($_SESSION['CREATE_ADD_PROGRAMS_CHECK']);
    }

    header('Location:create_goodpractice.php');
?>