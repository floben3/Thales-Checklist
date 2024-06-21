<?php 
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();
    
    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    // Retrieve logs and sanitize session variables for filtering
    $log = array_reverse(file('./log/log.txt'));
    $userUsername = Sanitize($_SESSION['LOGGED_USER']['username']);
    $userProfile = Sanitize($_SESSION['LOGGED_USER']['profile']);
    if (isset($_SESSION['LOG_FILTERS']['LOG_DATE_DAY']) && !empty($_SESSION['LOG_FILTERS']['LOG_DATE_DAY'])) {
        $day = Sanitize($_SESSION['LOG_FILTERS']['LOG_DATE_DAY']);
    } else {
        $day = 0;
    }
    if (isset($_SESSION['LOG_FILTERS']['LOG_DATE_MONTH']) && !empty($_SESSION['LOG_FILTERS']['LOG_DATE_MONTH'])) {
        $month = Sanitize($_SESSION['LOG_FILTERS']['LOG_DATE_MONTH']);
    } else {
        $month = '';
    }
    if (isset($_SESSION['LOG_FILTERS']['LOG_DATE_YEAR']) && !empty($_SESSION['LOG_FILTERS']['LOG_DATE_YEAR'])) {
        $year = Sanitize($_SESSION['LOG_FILTERS']['LOG_DATE_YEAR']);
    } else {
        $year = 0;
    }
    if (isset($_SESSION['LOG_FILTERS']['LOG_EVENEMENT_TYPE'])) {
        $evenementType = $_SESSION['LOG_FILTERS']['LOG_EVENEMENT_TYPE'];
        $logEvenementTypeSelectionChain = Sanitize(implode(', ', $_SESSION['LOG_FILTERS']['LOG_EVENEMENT_TYPE']));
    } else {
        $logEvenementTypeSelectionChain ='';
    }    
    if (isset($_SESSION['LOG_FILTERS']['LOG_PROFILES'])) {
        $profiles = $_SESSION['LOG_FILTERS']['LOG_PROFILES'];
        $logUserProfileSelectionChain = Sanitize(implode(', ', $_SESSION['LOG_FILTERS']['LOG_PROFILES']));
    } else {
        $logUserProfileSelectionChain = '';
    }
    if (isset($_SESSION['LOG_FILTERS']['LOG_SEARCH'])) {
        $logSearch = Sanitize($_SESSION['LOG_FILTERS']['LOG_SEARCH']);
        $logSearchSelectionChain = Sanitize($_SESSION['LOG_FILTERS']['LOG_SEARCH']);
    } else {
        $logSearchSelectionChain = '';
    }
    $logFiltered = LogFilter($userUsername, $userProfile, $log, $day, $month, $year, $evenementType, $profiles, $logSearch);

    // Sanitize and prepare filtered logs for display
    if (isset($logFiltered) && !empty($logFiltered)) {
        $logFilteredChain = Sanitize(implode("\n", $logFiltered));
    }

    require_once(__DIR__ . '/header.php'); 
?>

<section>
    <h2>Interface de filtrage de log</h2>
    <form class="selection-form" id="log-form" action="submit_log.php" method="POST">
        <div class="gestion">

            <div class="log-selection" id="log-date-selection<?= $_SESSION['LOGGED_USER']['profile'] === 'admin' ? '-a' : ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' ? '-sa' : '') ?>">
                <h3>Date</h3>
                <div>
                    <label for="log-date-day">Jour : </label>
                    <input id="log-date-day" type="number" name="log-date-day" min="1" max="31" placeholder="Saisir le jour" <?= $day ? 'value="'.$day.'"' : '' ?>>
                </div>
                <div>
                    <label for="log-date-month">Mois : </label>
                    <select id="log-date-month" name="log-date-month">
                        <option value="" <?= empty($month) ? 'selected' : '' ?>>Sélectionner un mois</option>
                        <option value="January" <?= $month === 'January' ? 'selected' : '' ?>>Janvier</option>
                        <option value="February" <?= $month === 'February' ? 'selected' : '' ?>>Février</option>
                        <option value="March" <?= $month === 'March' ? 'selected' : '' ?>>Mars</option>
                        <option value="April" <?= $month === 'April' ? 'selected' : '' ?>>Avril</option>
                        <option value="May" <?= $month === 'May' ? 'selected' : '' ?>>Mai</option>
                        <option value="June" <?= $month === 'June' ? 'selected' : '' ?>>Juin</option>
                        <option value="July" <?= $month === 'July' ? 'selected' : '' ?>>Juillet</option>
                        <option value="August" <?= $month === 'August' ? 'selected' : '' ?>>Août</option>
                        <option value="September" <?= $month === 'September' ? 'selected' : '' ?>>Septembre</option>
                        <option value="October" <?= $month === 'October' ? 'selected' : '' ?>>Octobre</option>
                        <option value="November" <?= $month === 'November' ? 'selected' : '' ?>>Novembre</option>
                        <option value="December" <?= $month === 'December' ? 'selected' : '' ?>>Décembre</option>
                    </select>
                </div>
                <div>
                    <label for="log-date-year">Année : </label>
                    <input id="log-date-year" type="number" name="log-date-year" min="2024" max="2100" placeholder="Saisir l'année" <?= $year ? 'value="'.$year.'"' : '' ?>>
                </div>
            </div>

            <div class="log-selection" id="log-evenement-type-selection<?= $_SESSION['LOGGED_USER']['profile'] === 'admin' ? '-a' : ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' ? '-sa' : '') ?>">
                <h3>Type d'événement</h3>
                <div class="checkbox-line">
                    <input class="checkbox" type="checkbox" id="Information" name="logEvenementTypeSelection[]" value="Information" <?= (str_contains($logEvenementTypeSelectionChain, 'Information') ? 'checked' : '') ?>>
                    <label for="Information">Information</label>
                </div>
                <div class="checkbox-line">
                    <input class="checkbox" type="checkbox" id="Warning" name="logEvenementTypeSelection[]" value="Warning" <?= (str_contains($logEvenementTypeSelectionChain, 'Warning') ? 'checked' : '') ?>>
                    <label for="Warning">Warning</label>
                </div>
                <div class="checkbox-line">
                    <input class="checkbox" type="checkbox" id="Alarm" name="logEvenementTypeSelection[]" value="Alarm" <?= (str_contains($logEvenementTypeSelectionChain, 'Alarm') ? 'checked' : '') ?>>
                    <label for="Alarm">Alarm</label>
                </div>         
            </div>

            <?php if ($_SESSION['LOGGED_USER']['profile'] === 'superadmin') : ?>
                <div class="log-selection" id="log-user-profile-selection-sa">
                    <h3>Profil de l'utilisateur</h3>
                    <div class="checkbox-line">
                        <input class="checkbox" type="checkbox" id="operator" name="logUserProfileSelection[]" value="Operator" <?= (str_contains($logUserProfileSelectionChain, 'Operator') ? 'checked' : '') ?>>
                        <label for="operator">Opérateur</label>
                    </div>
                    <div class="checkbox-line">
                        <input class="checkbox" type="checkbox" id="admin" name="logUserProfileSelection[]" value="Admin" <?= (str_contains($logUserProfileSelectionChain, 'Admin') ? 'checked' : '') ?>>
                        <label for="admin">Administrateur</label>
                    </div>
                    <div class="checkbox-line">
                        <input class="checkbox" type="checkbox" id="superadmin" name="logUserProfileSelection[]" value="Superadmin" <?= (str_contains($logUserProfileSelectionChain, 'Superadmin') ? 'checked' : '') ?>>
                        <label for="superadmin">Super administrateur</label>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="log-selection" id="log-search-selection<?= $_SESSION['LOGGED_USER']['profile'] === 'admin' ? '-a' : ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' ? '-sa' : '') ?>">
                <h3>Barre de recherche</h3>
                <input class="search-input" type="search" id="log-search" name="logSearch" placeholder="Mots séparés par des virgules, adresse IP..." value="<?= $logSearchSelectionChain ?>" autofocus>
            </div>
        </div>

        <div class="selection-button">
            <button id="submit" type="submit" name="submit" value="submit">Appliquer</button>
            <button id="reset" type="submit" name="submit" value="reset">Effacer les filtres</button>
            <button id="copy" value="<?= $logFilteredChain ?>">Copier</button>
        </div>
    </form> 
</section>

<section>
    <h2>Interface de visualisation des logs</h2>
    <div class="log">
        <?php foreach ($logFiltered as $logLine) : ?>
            <p <?= (str_contains($logLine, 'Information') ? 'class="information"' : (str_contains($logLine, 'Warning') ? 'class="warning"' : (str_contains($logLine, 'Alarm') ? 'class="alarm"' : '' ))) ?>><?= $logLine ?></p>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once(__DIR__ . '/footer.php'); ?>

<script>
    // Function to copy the filtered log content to clipboard
    function copy() {
        let copyText = document.querySelector("#copy").value;

        let tempInput = document.createElement("textarea");
        tempInput.value = copyText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");

        // Clean up temporary textarea
        document.body.removeChild(tempInput);
    }

    // Event listener for copy button
    document.querySelector("#copy").addEventListener("click", copy);
</script>