<?php 
    session_start();   
    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    // Retrieve the password configuration settings
    $passwordParameters = PasswordSelect();

    // Sanitize the password configuration parameters
    $n = Sanitize($passwordParameters['n']);
    $p = Sanitize($passwordParameters['p']);
    $q = Sanitize($passwordParameters['q']);
    $r = Sanitize($passwordParameters['r']);

    // Retrieve the user selection order from the session
    $usersSelectionOrder = $_SESSION['USERS_SELECTION_ORDER'];

    // Sanitize the profile of the logged-in user
    $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);

    // Retrieve the list of users based on the selection order and profile
    $users = UsersSelect($usersSelectionOrder, $profile);
?>

<section>
    <h2>Tableau des utilisateurs</h2>
    <div class="table-container">
        <table>
            <div class="grid-container">
                <thead>
                    <tr>
                        <th class="username-column">Nom d'utilisateur</th>
                        <th class="firstname-column">Prénom</th>
                        <th class="lastname-column">Nom</th>
                        <th class="profile-column">Profil</th>
                        <th class="attempts-column">Tentatives de connexion</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
            </div>
            <div class="grid-container">
                <tbody class="scrollable-tbody" id="users-tbody">
                    <?php if ($_SESSION['LOGGED_USER']['profile'] === 'admin' || $_SESSION['LOGGED_USER']['profile'] === 'superadmin') : ?>
                        <tr id="self-admin">
                            <td class="username-column"><?= Sanitize($_SESSION['LOGGED_USER']['username']) ?></td>
                            <td class="firstname-column"><?= Sanitize($_SESSION['LOGGED_USER']['firstname']) ?></td>
                            <td class="lastname-column"><?= Sanitize($_SESSION['LOGGED_USER']['lastname']) ?></td>
                            <td class="profile-column"><?= Sanitize($_SESSION['LOGGED_USER']['profile']) ?></td>
                            <td class="attempts-column">0</td>
                            <td class="actions-column">
                                <div class="action-btn-container">
                                    <button class="action-btn" onclick="openUserForm(1, <?= Sanitize($_SESSION['LOGGED_USER']['user_id']) ?>, 0, <?= $n ?>, <?= $p ?>, <?= $q ?>, <?= $r ?>)">Gérer</button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($users as $user) { ?>
                        <tr <?= (UserIsBlocked(Sanitize($user['attempts']))) ? 'class="blocked"' : '' ?>>
                            <td class="username-column"><?= Sanitize($user['username']) ?></td>
                            <td class="firstname-column"><?= Sanitize($user['firstname']) ?></td>
                            <td class="lastname-column"><?= Sanitize($user['lastname']) ?></td>
                            <td class="profile-column"><?= Sanitize($user['profile']) ?></td>
                            <?php if (UserIsBlocked(Sanitize($user['attempts']))) : ?>
                                <td class="attempts-column">Compte bloqué</td>
                            <?php else : ?>
                                <td class="attempts-column"><?= Sanitize($user['attempts'])?></td>
                            <?php endif; ?>
                            <td class="actions-column">
                                <div class="action-btn-container">
                                    <button class="action-btn" onclick="openUserForm(0, <?= Sanitize($user['user_id']) ?>, <?= (UserIsBlocked(Sanitize($user['attempts']))) ? 1 : 0 ?>, <?= $n ?>, <?= $p ?>, <?= $q ?>, <?= $r ?>)">Gérer</button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </div>
        </table>
    </div>

    <div class="form-popup" id="userForm">
        <form action="manage_user.php" class="form-container" method="POST">
            <h3>Administration de l'utilisateur</h3>
            <p>Vous pouvez : </p>
            <ul>
                <li>Supprimer un utilisateur.</li>
                <li>Réinitialiser le mot de passe d'un utilisateur bloqué.</li>
                <li>Réinitialiser votre mot de passe.</li>
            </ul>
            <input type="hidden" id="userId" name="userId" value="">
            <button id="cancel" type="button" class="btn" onclick="closeUserForm()">Annuler</button>
        </form>
    </div>
</section>

<script>
    function openUserForm(option, userId, userIsBlocked, n, p, q, r) {
        var form = document.querySelector("#userForm form");
        var cancelButton = document.querySelector('button[id="cancel"]');

        // Remove password rules if they exist
        var passwordRulesDiv = document.getElementById('password-rules');
        if (passwordRulesDiv) {
            passwordRulesDiv.remove();
        }

        // Remove reset button if it exists
        var existingResetButton = document.getElementById('reset-password-button');
        if (existingResetButton) {
            existingResetButton.remove();
        }

        // Remove delete button if it exists
        var deleteButton = document.querySelector('button[value="delete-user"]');
        if (deleteButton) {
            deleteButton.remove();
        }

        // Set the userId input value
        document.getElementById("userId").value = userId;

        // Check if the user is blocked or option is 1
        if (userIsBlocked || option) {
            
            // Add reset password button if it doesn't exist
            var resetButton = document.createElement('button');
            resetButton.type = 'submit';
            resetButton.id = 'reset-password-button';
            resetButton.className = 'btn';
            resetButton.name = 'submit';
            resetButton.value = 'reset-password';
            resetButton.textContent = 'Réinitialiser le mot de passe';

            form.insertBefore(resetButton, cancelButton);

            // Add password rules if they don't exist
            var passwordRulesDiv = document.createElement('div');
            passwordRulesDiv.id = 'password-rules';

            let passwordRulesHTML = `
                <div class="gestion">
                        <div class="reset-user-password">
                            <h4>Règles de configuration du mot de passe : </h4>
                            <ol>
                                <li>Ne doit pas contenir d'accent.</li>
                                <li>Ne doit pas contenir le nom d'utilisateur.</li>
            `;
            if (n > 0) {
                passwordRulesHTML += `<li>Doit contenir au moins ${n} chiffre${n > 1 ? 's' : ''}.</li>`;
            }
            if (p > 0) {
                passwordRulesHTML += `<li>Doit contenir au moins ${p} minuscule${p > 1 ? 's' : ''}.</li>`;
            }
            if (q > 0) {
                passwordRulesHTML += `<li>Doit contenir au moins ${q} majuscule${q > 1 ? 's' : ''}.</li>`;
            }
            if (r > 0) {
                passwordRulesHTML += `<li>Doit contenir au moins ${r} caractère${r > 1 ? 's' : ''} spécia${r > 1 ? 'ux' : 'l'}.</li>`;
            }
            passwordRulesHTML += `
                        </ol>
                        <h4>Renseignez le nouveau mot de passe.</h4>
                        <div class="reset-user-password-input-area">
                            <div class="reset-user-password-input-line">
                                <label for="password">Mot de passe : </label>
                                <input id="password" name="password" type="password" placeholder="Saisir le mot de passe" required autofocus/>
                            </div>
                            <div class="reset-user-password-input-line">
                                <label for="password2">Mot de passe : </label>
                                <input id="password2" name="password2" type="password" placeholder="Ressaisir le mot de passe" required/>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            passwordRulesDiv.innerHTML = passwordRulesHTML;
            form.insertBefore(passwordRulesDiv, resetButton);
        }

        // Add delete button only if option is 0
        if (!option) {
            var deleteButton = document.createElement('button');
            deleteButton.type = 'submit';
            deleteButton.className = 'btn-warning';
            deleteButton.name = 'submit';
            deleteButton.value = 'delete-user';
            deleteButton.textContent = 'Supprimer';

            form.insertBefore(deleteButton, cancelButton);
        }

        // Display the user form
        document.getElementById("userForm").style.display = "block";
    }

    function closeUserForm() {
        // Hide the user form
        document.getElementById("userForm").style.display = "none";
    }
</script>