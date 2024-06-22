<?php 
    session_start();

    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    $postData = $_POST;
    
    $fieldType = Sanitize($postData['fieldType']);
    $value = Sanitize($postData['fieldToDelete']);
    $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);

    $result = DeleteField($fieldType, $value, $profile);

    switch ($result) {
        case 0:
            $logMessage = "Failed to delete a program, unknown program : $value";
            $message = 'Erreur !\n\nProgramme inconnu : '.$value;
            break;
        case 1:
            $logMessage = "Failed to delete a keyword, unknown keyword : $value";
            $message = 'Erreur !\n\nMot-clé inconnu : '.$value;
            break;
        case 2:
            $logMessage = "Successfully deleted a program : $value";
            $message = 'Succès !\n\nProgramme supprimé : '.$value;
            break;
        case 3:
            $logMessage = "Successfully deleted a keyword : $value";
            $message = 'Succès !\n\nMot-clé supprimé : '.$value;
            break;
        case 4:
            $logMessage = "Failed to delete a program or keyword, unknown field type : $fieldType";
            $message = 'Erreur !\n\nType de champ inconnu : '.$fieldType;
            break;
        case 5:
            $logMessage = "Failed to delete a program or keyword, don't have permission";
            $message = 'Erreur !\n\nVous n avez pas la permission de supprimer ce programme ou mot-clé.';
            break;
        default:
            $logMessage = 0;
            $message = 0;
    }
    
    if ($logMessage) {
        Logger(Sanitize($_SESSION['LOGGED_USER']['username']), Sanitize($_SESSION['LOGGED_USER']['profile']), 2, $logMessage);
    }

    if ($message) {
        $_SESSION['PROGRAM_OR_KEYWORD_DELETE_MESSAGE'] = $message;
    }
    
    header('Location:delete_fields.php');
?>
