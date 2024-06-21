<?php 
    session_start();
    $_SESSION['ON_LOGIN_PAGE'] = TRUE;
    require_once(__DIR__ . '/functions.php');
    unset($_SESSION['LOGIN_ATTEMPT']);
    require_once(__DIR__ . '/header.php'); 
    // If user is not logged in, display login form
?>

<?php if (!isset($_SESSION['LOGGED_USER'])) : ?>
    <section class="login">
        <form action="submit_login.php" method="POST">
            <?php
                // Display login message if available
                if (isset($_SESSION['LOGIN_MESSAGE'])) {
                    echo "<p>".$_SESSION['LOGIN_MESSAGE']."</p>";
                    unset($_SESSION['LOGIN_MESSAGE']);
                }
            ?>
            <h2>Veuillez vous identifier</h2>
            <div class="input-line"><label for="username">Nom d'utilisateur : </label><input id="username" name="username" type="text" placeholder="Entrez le nom d'utilisateur" required autofocus/></div>
            <div class="input-line"><label for="password">Mot de passe : </label><input id="password" name="password" type="password" placeholder="Entrez le mot de passe" required /></div>
            <button id="submit" name="submit" type="submit" value="submit">Connexion</button>
        </form>
    </section>
<?php else : ?>
    <?php 
        // Redirect to index.php if user is already logged in
        header('Location:index.php');
    ?>
<?php endif; ?>

<?php require_once(__DIR__ . '/footer.php'); ?>
