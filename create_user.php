<?php 
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    // Display alert message if set in session
    if (isset($_SESSION['CREATE_USER_MESSAGE'])) {
        echo '<script>alert("'.Sanitize($_SESSION['CREATE_USER_MESSAGE']).'")</script>';
    }
    unset($_SESSION['CREATE_USER_MESSAGE']); 

    require_once(__DIR__ . '/header.php');
?>

<section>
    <h2>Interface de création d'utilisateurs</h2>
    <form class="create-user-form" action="submit_create_user.php" method="POST">
        <div class="gestion">
            <div class="create-user">
                <h3>Veuillez remplir les informations du nouvel utilisateur</h3>
                <div class="create-user-input-area">
                    <div class="create-user-input-line">
                        <label for="username">Nom d'utilisateur : </label><input id="username" name="username" type="text" placeholder="Saisir le nom d'utilisateur" required autofocus/>
                    </div>
                    <div class="create-user-input-line">
                        <label for="firstname">Prénom : </label><input id="firstname" name="firstname" type="text" placeholder="Saisir le prénom" required />
                    </div>
                    <div class="create-user-input-line">
                        <label for="lastname">Nom : </label><input id="lastname" name="lastname" type="text" placeholder="Saisir le nom" required />
                    </div>
                    <?php if ($_SESSION['LOGGED_USER']['profile'] === 'superadmin') : ?>
                        <div class="create-user-input-line">
                            <label for="profile">Profil : </label>
                            <select id='profile' name='profile'>
                                <option value='operator' selected>Opérateur</option>
                                <option value='admin'>Administrateur</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h4>Règles de configuration du mot de passe : </h4>
                <ol>
                    <li>Ne doit pas contenir d'accent.</li>
                    <li>Ne doit pas contenir le nom d'utilisateur.</li>
                    <?php $parameters = PasswordSelect(); ?>
                    <?php if ($parameters['n'] > 0) : ?>
                    <li>Doit contenir au moins <?= $parameters['n'] ?> chiffre<?= ($parameters['n'] > 1) ? 's' : '' ?>.</li>
                    <?php endif; ?>
                    <?php if ($parameters['p'] > 0) : ?>
                    <li>Doit contenir au moins <?= $parameters['p'] ?> minuscule<?= ($parameters['p'] > 1) ? 's' : '' ?>.</li>
                    <?php endif; ?>
                    <?php if ($parameters['q'] > 0) : ?>
                    <li>Doit contenir au moins <?= $parameters['q'] ?> majuscule<?= ($parameters['q'] > 1) ? 's' : '' ?>.</li>
                    <?php endif; ?>
                    <?php if ($parameters['r'] > 0) : ?>
                    <li>Doit contenir au moins <?= $parameters['r'] ?> caractère<?= ($parameters['r'] > 1) ? 's' : '' ?> spécia<?= ($parameters['r'] > 1) ? 'ux' : 'l' ?>.</li>
                    <?php endif; ?>
                </ol>

                <h4>Renseignez votre mot de passe.</h4>
                <div class="create-user-input-area">
                    <div class="create-user-input-line">
                        <label for="password">Mot de passe : </label><input id="password" name="password" type="password" placeholder="Saisir le mot de passe" required />
                    </div>
                    <div class="create-user-input-line">
                        <label for="password2">Mot de passe : </label><input id="password2" name="password2" type="password" placeholder="Ressaisir le mot de passe" required />
                    </div>
                </div>
                <div class="create-user-button">
                    <button id="submit" name="submit" type="submit" value="submit">Créer le nouvel utilisateur</button>
                </div>
            </div>
        </div>
    </form>
</section>

<?php require_once(__DIR__ . '/footer.php'); ?>
