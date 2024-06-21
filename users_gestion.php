<?php 
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    // Retrieve the password configuration settings
    $passwordParameters = PasswordSelect();

    // Determine the order type and direction for user selection
    $orderType = isset($_SESSION['USERS_SELECTION_ORDER'][0]) ? $_SESSION['USERS_SELECTION_ORDER'][0] : '';
    $orderDirection = isset($_SESSION['USERS_SELECTION_ORDER'][1]) ? $_SESSION['USERS_SELECTION_ORDER'][1] : '';
?>

<section class="users-gestion">
    <h2>Interface de gestion des utilisateurs</h2>
    <form class="selection-form" action="submit_users_gestion.php" method="POST">
        <div class="gestion">
            <div class="users-order-selection">
                <h3>Sélection de tri</h3>
                <div class="radio-area">
                    <div class='radio-line'>
                        <label for='order-type'>Type de tri :</label>
                        <select id='order-type' name='users-order[type]'>
                            <option value='username' <?= ($orderType === 'username' && empty($orderType)) ? 'selected' : '' ?>>Nom d'utilisateur</option>
                            <option value='firstname' <?= $orderType == 'firstname' ? 'selected' : '' ?>>Prénom</option>
                            <option value='lastname' <?= $orderType == 'lastname' ? 'selected' : '' ?>>Nom</option>
                            <option value='profile' <?= $orderType == 'profile' ? 'selected' : '' ?>>Profil</option>
                            <option value='attempts' <?= $orderType == 'attempts' ? 'selected' : '' ?>>Tentatives de connexion</option>
                        </select>
                    </div>

                    <div class='radio-line'>
                        <label for='order-direction'>Direction :</label>
                        <select id='order-direction' name='users-order[direction]'>
                            <option value='asc' <?= ($orderDirection == 'asc' && empty($orderDirection)) ? 'selected' : '' ?>>Ascendant</option>
                            <option value='desc' <?= $orderDirection == 'desc' ? 'selected' : '' ?>>Descendant</option>
                        </select>
                    </div>
                </div>
                <div class="selection-button">
                    <button id="submit" type="submit" name="submit" value="users-order">Appliquer</button>
                </div>
            </div>

            <div class="password-update-selection">
                <h3>Configuration des paramètres des mots de passe.</h3>
                <p>Au moins...</p>
                <div class="password-input-area">
                    <div class="password-input-line">
                        <label for="n">Chiffres : </label>
                        <input type="number" id="n" name="n" min="0" max="10" value="<?= Sanitize($passwordParameters['n']) ?>" required>
                    </div>
                    <div class="password-input-line">
                        <label for="p">Minuscules : </label>
                        <input type="number" id="p" name="p" min="0" max="10" value="<?= Sanitize($passwordParameters['p']) ?>" required>
                    </div>
                    <div class="password-input-line">
                        <label for="q">Majuscules : </label>
                        <input type="number" id="q" name="q" min="0" max="10" value="<?= Sanitize($passwordParameters['q']) ?>" required>
                    </div>
                    <div class="password-input-line">
                        <label for="r">Caractères spéciaux : </label>
                        <input type="number" id="r" name="r" min="0" max="10" value="<?= Sanitize($passwordParameters['r']) ?>" required>
                    </div>
                </div>
                <div class="selection-button">
                    <button id="submit" type="submit" name="submit" value="password-update">Appliquer</button>
                </div>
            </div>
            <div class="create-user-selection">
                <h3>Créer un nouvel utilisateur</h3>
                <div class="selection-button">
                    <button id="submit" type="submit" name="submit" value="create-user">Créer</button>
                </div>
            </div>
            <div class="log-selection" id="to-log">
                <h3>Visualiser les logs</h3>
                <div class="selection-button">
                    <button id="submit" type="submit" name="submit" value="to-logs">Visualiser</button>
                </div>
            </div>
        </div>
    </form> 
</section>