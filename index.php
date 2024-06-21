<?php 
    session_start();
    // clear logout tentative flag if it exists
    if (isset($_SESSION['LOGOUT_ATTEMPT'])) { 
        unset($_SESSION['LOGOUT_ATTEMPT']); 
    }

    require_once(__DIR__ . '/header.php');
?>

<?php if (!isset($_SESSION['LOGGED_USER'])) : ?>
    <section class="welcome">
        <h2>Bienvenue sur le projet Thales Checklist.</h2>
        <ul>
            <p>Connectez-vous pour : </p>
            <li>gérer les bonnes pratiques</li>
            <li>créer des checklists</li>
        </ul>
        <form action="login.php" method="POST">
            <button type="submit" name="submit" class="btn">Se connecter</button>
        </form>
    </section>
<?php endif; ?>

<?php if (isset($_SESSION['LOGGED_USER'])) : ?>
    <?php 
        // Check user's profile and include appropriate sections
        if ($_SESSION['LOGGED_USER']['profile'] === 'operator' || $_SESSION['LOGGED_USER']['profile'] === 'admin' || $_SESSION['LOGGED_USER']['profile'] === 'superadmin') { 
            require_once(__DIR__ . '/goodpractices_selection.php');
            require_once(__DIR__ . '/goodpractices_print.php');
        } else {
            // Redirect to logout page if user profile is unexpected
            header('Location:logout.php');
            exit();
        }
    ?>
<?php endif; ?>

<?php require_once(__DIR__ . '/footer.php'); ?>