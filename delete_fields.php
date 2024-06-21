<?php 
    session_start();

    require_once(__DIR__ . '/functions.php');
    CheckAdminRights();

    // Include header, user management scripts, and footer
    require_once(__DIR__ . '/header.php');
?>

<section class="goodpractices-selection">
    <h2>Interface de suppression de programmes et de mots-clés</h2>
    <form class="selection-form" action="submit_delete_fields.php" method="POST">
        <div class="gestion">
            <div class="phase-selection">
                <h3>Séléction du type de champ à supprimer</h3>
                <div class='radio-line'>
                    <label for="fieldType">Souhaitez-vous supprimer un programme ou un mot-clé : </label>
                    <select id='fieldType' name='fieldType'>
                        <option id="programField" name="programField" value="program_name">Programme</option>
                        <option id="keywordField" name="keywordField" value="onekeyword">Mot-clé</option>
                    </select>
                </div>
            </div>

            <div class="keywordsOne-selection">
                <h3>Séléction du programme ou du mot-clé à supprimer</h3>
                <input class='search-input' type="text" name="fieldToDelete" placeholder="Saisir le programme ou le mot-clé">
            </div>
        </div>

        <div class="selection-button" id="field-button">
            <button id="submit" type="submit" name="submit" value="submit">Supprimer le programme ou le mot-clé</button>
        </div>
</section>

<?php require_once(__DIR__ . '/footer.php'); ?>

<?php if (isset($_GET['e'])) : ?>
    <script>alert('<?= Sanitize(urldecode($_GET['e'])) ?>')</script>
<?php endif; ?>