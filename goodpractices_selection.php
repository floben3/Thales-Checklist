<?php 
    session_start(); 
    require_once(__DIR__ . '/functions.php');
    CheckRights();

    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    // Fetch programs and phases
    $programs = ProgramSelect();
    $phases = PhaseSelect();

    // Sanitize and retrieve session variables
    $orderType = Sanitize($_SESSION['GOODPRACTICES_ORDER'][0]);
    $orderDirection = Sanitize($_SESSION['GOODPRACTICES_ORDER'][1]);

    if (isset($_SESSION['SELECT_ALL_PROGRAMS_CHECK'])) {
        $programsSelectionChain = Sanitize(implode(', ', $_SESSION['SELECT_ALL_PROGRAMS_CHECK']));
    } else {
        $programsSelectionChain = '';
    }
    if (!isset($_SESSION['SELECT_ALL_PROGRAMS'])) {
        $_SESSION['SELECT_ALL_PROGRAMS'] = 0;
    }
    if (isset($_SESSION['GOODPRACTICES_SELECTION']['phase_name'])) {
        $phasesSelectionChain = Sanitize(implode(', ', $_SESSION['GOODPRACTICES_SELECTION']['phase_name']));
    } else {
        $phasesSelectionChain = '';
    }  
    if (isset($_SESSION['PHASE_CHECK'])) {
        $phasesSelectionChain .= Sanitize(implode(', ', $_SESSION['PHASE_CHECK']));
    }
    if (isset($_SESSION['GOODPRACTICES_SELECTION']['onekeyword'])) {
        $keywordsSelectionChain = Sanitize(implode(', ', $_SESSION['GOODPRACTICES_SELECTION']['onekeyword']));
    } else {
        $keywordsSelectionChain = '';
    }
    if (isset($_SESSION['KEYWORDS_CHECK'])) {
        $keywordsSelectionChain .= Sanitize($_SESSION['KEYWORDS_CHECK']);
    }
?>

<section class="goodpractices-selection">
    <h2>Interface de filtrage des bonnes pratiques</h2>
    <form class="selection-form" action="submit_goodpractices_selection.php" method="POST">
        <div class="gestion">
            <div class="programs-selection">
                <div id="programs-selection-title-and-button">
                    <h3>Recherche de programme(s)</h3>
                    <button id="select-all" type="submit" name="submit" value="select-all-programs"><?= $_SESSION['SELECT_ALL_PROGRAMS'] ? 'Tout désélectionner' : 'Tout séléctionner' ?></button>
                </div>
                <div class="checkbox-area">
                    <?php foreach ($programs as $program): ?>
                        <div class="checkbox-line">
                            <input class="checkbox" type="checkbox" id="id<?= $program[0] ?>" name="programsSelection[]" value="<?= $program[0] ?>" <?= (str_contains($programsSelectionChain, $program[0]) || $_SESSION['SELECT_ALL_PROGRAMS'] ? 'checked' : '') ?>>
                            <label for="id<?= $program[0] ?>"><?= $program[0] ?></label>
                        </div>
                    <?php endforeach; ?>   
                </div>         
            </div>

            <div class="phases-and-order-selection">
                <h3>Sélection de phase(s)</h3>
                <div class="checkbox-area">
                    <?php foreach ($phases as $phase): ?>
                        <div class="checkbox-line">
                            <input class="checkbox" type="checkbox" id="<?= $phase[0] ?>" name="phasesSelection[]" value="<?= $phase[0] ?>" <?= (str_contains($phasesSelectionChain, $phase[0]) ? 'checked' : '') ?>>
                            <label for="<?= $phase[0] ?>"><?= $phase[0] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <h3>Sélection de tri</h3>
                <div class="radio-area">
                    <div class='radio-line'>
                        <label for='order-type'>Type de tri :</label>
                        <select id='order-type' name='order[type]'>
                            <option value='program_names' <?= $orderType === 'program_names' ? 'selected' : '' ?>>Programme</option>
                            <option value='phase_name' <?= $orderType === 'phase_name' ? 'selected' : '' ?>>Phase</option>
                            <option value='item' <?= $orderType === 'item' || empty($orderType) ? 'selected' : '' ?>>Item</option>
                            <option value='keywords' <?= $orderType === 'keywords' ? 'selected' : '' ?>>Mots-clés</option>
                        </select>
                    </div>

                    <div class='radio-line'>
                        <label for='order-direction'>Direction :</label>
                        <select id='order-direction' name='order[direction]'>
                            <option value='asc' <?= $orderDirection === 'asc' ? 'selected' : '' ?>>Ascendant</option>
                            <option value='desc' <?= $orderDirection === 'desc' ? 'selected' : '' ?>>Descendant</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="keywords-selection">
                <h3>Recherche de mot(s)-clé(s)</h3>
                <input class="search-input" type="search" id="keywordSearch" name="keywordSearch" placeholder="Mots-clés séparés par des virgules" value="<?= $keywordsSelectionChain ?>">
                <p><?= Sanitize($_SESSION['GOODPRACTICES_KEYWORDS_SELECTION_MESSAGE']) ?></p>
            </div>
        </div>

        <div class="selection-button">
            <button id="submit" type="submit" name="submit" value="submit">Appliquer</button>
            <button id="reset" type="submit" name="submit" value="reset">Effacer les filtres</button>
            <button id="create" type="submit" name="submit" value="create">Créer une bonne pratique</button>
            <button id="export" type="submit" name="submit" value="export-csv">Télécharger la checklist - CSV</button>
            <button id="export" type="submit" name="submit" value="export-pdf">Télécharger la checklist - PDF</button>

        </div>
    </form> 
</section>

<script type="text/javascript">
    // JavaScript to handle popups and alerts after page load
    window.onload = function() {
        <?php if (isset($_SESSION['CHECKLIST_CREATION_OUTPUT'])) : ?>
            alert("<?= Sanitize($_SESSION['CHECKLIST_CREATION_OUTPUT']) ?>");
            <?php unset($_SESSION['CHECKLIST_CREATION_OUTPUT']); ?>
            <?php if (isset($_SESSION['CHECKLIST_FILENAME'])) : ?>
                open("http://<?= $_SERVER['SERVER_NAME'] ?>:<?= $_SERVER['SERVER_PORT'] ?>/checklist/<?= Sanitize($_SESSION['CHECKLIST_FILENAME']) ?>", '_blank');
                <?php unset($_SESSION['CHECKLIST_FILENAME']); ?>
            <?php endif; ?>
        <?php endif; ?>
    };
</script>
