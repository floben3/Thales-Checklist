<?php 
    session_start();

    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    // Include header, user management scripts, and footer
    require_once(__DIR__ . '/header.php');
    require_once(__DIR__ . '/users_gestion.php');
    require_once(__DIR__ . '/users_print.php');
    require_once(__DIR__ . '/footer.php');
?>

<?php if (isset($_SESSION['RESET_USER_PASSWORD_MESSAGE'])) : ?>
    <script>
        // Alert the user with a sanitized message retrieved from session
        alert('<?= Sanitize($_SESSION['RESET_USER_PASSWORD_MESSAGE']) ?>');
    </script>
    <?php 
        // Unset the session variable after displaying the message
        unset($_SESSION['RESET_USER_PASSWORD_MESSAGE']); 
    ?>
<?php endif; ?>
