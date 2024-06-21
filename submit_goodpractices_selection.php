<?php 
    session_start(); 
    require_once(__DIR__ . '/functions.php');
    CheckPostRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;

    // Check which button was clicked
    if ($postData['submit'] === 'select-all-programs') {
        // Toggle SELECT_ALL_PROGRAMS session variable
        if ($_SESSION['SELECT_ALL_PROGRAMS']) {
            $_SESSION['SELECT_ALL_PROGRAMS'] = 0;
            unset($_SESSION['SELECT_ALL_PROGRAMS_CHECK']);
        } else {
            $_SESSION['SELECT_ALL_PROGRAMS'] = 1;
        }

        // Set PHASE_CHECK session variable
        $_SESSION['PHASE_CHECK'] = $postData['phasesSelection'];

        // Set KEYWORDS_CHECK session variable based on keyword search
        if (isset($_SESSION['GOODPRACTICES_SELECTION']['onekeyword'])) {
            $keywordsSelectionChain = Sanitize(implode(', ', $_SESSION['GOODPRACTICES_SELECTION']['onekeyword']));
        } else {
            $keywordsSelectionChain = '';
        }
        $_SESSION['KEYWORDS_CHECK'] = str_replace($keywordsSelectionChain, '', Sanitize($postData['keywordSearch']));

    } elseif ($postData['submit'] === 'submit') {
        // Set GOODPRACTICES_SELECTION session variables
        $_SESSION['GOODPRACTICES_SELECTION']['program_name'] = $postData['programsSelection'];
        $_SESSION['SELECT_ALL_PROGRAMS_CHECK'] = $postData['programsSelection'];
        $_SESSION['GOODPRACTICES_SELECTION']['phase_name'] = $postData['phasesSelection'];

        // Validate and set keywords selection
        $validateKeywordsSelection = ValidateKeywordsSelection($postData['keywordSearch']);
        $keywordsSelection = $validateKeywordsSelection[0];
        $wrongKeywords = Sanitize(implode(', ', $validateKeywordsSelection[1]));
        $_SESSION['GOODPRACTICES_SELECTION']['onekeyword'] = $keywordsSelection;

        // Handle wrong keywords
        if (!empty($wrongKeywords)) {
            $_SESSION['GOODPRACTICES_KEYWORDS_SELECTION_MESSAGE'] = 'Erreur avec les mots-clés suivant : '.$wrongKeywords;
            Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 1, 'Goodpractices selection issue, wrong keywords');
        } else {
            unset($_SESSION['GOODPRACTICES_KEYWORDS_SELECTION_MESSAGE']);
        }

        // Set order if provided
        if (!empty($postData['order']['type']) && !empty($postData['order']['direction'])) {
            $orderType = Sanitize($postData['order']['type']);
            $orderDirection = Sanitize($postData['order']['direction']);
            $_SESSION['GOODPRACTICES_ORDER'] = array($orderType, $orderDirection);
        }

        // Unset unnecessary session variables
        unset($_SESSION['PHASE_CHECK']);
        unset($_SESSION['KEYWORDS_CHECK']);

    } elseif ($postData['submit'] === 'reset') {
        // Reset session variables for goodpractices selection
        unset($_SESSION['GOODPRACTICES_SELECTION']);
        unset($_SESSION['GOODPRACTICES_ORDER']);
        unset($_SESSION['GOODPRACTICES_KEYWORDS_SELECTION_MESSAGE']);
        unset($_SESSION['SELECT_ALL_PROGRAMS_CHECK']);
        unset($_SESSION['PHASE_CHECK']);
        unset($_SESSION['KEYWORDS_CHECK']);
        $_SESSION['SELECT_ALL_PROGRAMS'] = 0;

    } elseif ($postData['submit'] === 'create') {
        // Redirect to create_goodpractice.php
        header('Location:create_goodpractice.php');
        exit();

    } elseif ($postData['submit'] === 'export-csv') {
        // Set variables for CSV download
        $whereIs = $_SESSION['GOODPRACTICES_SELECTION'];
        $orderBy = $_SESSION['GOODPRACTICES_ORDER'];
        $erased = $_SESSION['ERASED_GOODPRACTICES'];
        $erasedPrograms = $_SESSION['ERASED_GOODPRACTICES_PROGRAMS'];
        $username = Sanitize($_SESSION['LOGGED_USER']['username']);
        $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);

        // Download checklist as CSV
        $download = DownloadChecklist($whereIs, $orderBy, $erased, $erasedPrograms, $username, $profile, 'csv');
        $_SESSION['CHECKLIST_CREATION_OUTPUT'] = Sanitize($download[1]);
        if ($download[0] === 0) {
            $_SESSION['CHECKLIST_FILENAME'] = Sanitize($download[2]);
        }
    } elseif ($postData['submit'] === 'export-pdf') {
        // Set variables for PDF download
        $whereIs = $_SESSION['GOODPRACTICES_SELECTION'];
        $orderBy = $_SESSION['GOODPRACTICES_ORDER'];
        $erased = $_SESSION['ERASED_GOODPRACTICES'];
        $erasedPrograms = $_SESSION['ERASED_GOODPRACTICES_PROGRAMS'];
        $username = Sanitize($_SESSION['LOGGED_USER']['username']);
        $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);

        // Download checklist as PDF
        $download = DownloadChecklist($whereIs, $orderBy, $erased, $erasedPrograms, $username, $profile, 'pdf');
        $_SESSION['CHECKLIST_CREATION_OUTPUT'] = Sanitize($download[1]);
        if ($download[0] === 0) {
            $_SESSION['CHECKLIST_FILENAME'] = Sanitize($download[2]);
        }
    }

    header('Location:index.php');
?>
