<?php 
    // Start session and include necessary files
    session_start();
    require_once(__DIR__ . '/functions.php');
    CheckRights();
    
    // Include database connection and SQL functions
    require_once(__DIR__ . '/config/database_connect.php');
    require_once(__DIR__ . '/sql_functions.php');

    // Retrieve session variables
    $whereIs = $_SESSION['GOODPRACTICES_SELECTION'];
    $orderBy = $_SESSION['GOODPRACTICES_ORDER'];
    $erased = $_SESSION['ERASED_GOODPRACTICES'];
    $erasedPrograms = $_SESSION['ERASED_GOODPRACTICES_PROGRAMS'];
    $profile = Sanitize($_SESSION['LOGGED_USER']['profile']);

    // Retrieve good practices based on session variables
    $goodPractices = GoodPracticesSelect($whereIs, $orderBy, $erased, $erasedPrograms, $profile);
?>

<section>
    <h2>Tableau des bonnes pratiques</h2>
    <div class="table-container">
        <table>
            <div class="grid-container">
                <thead>
                    <tr>
                        <th class="programs-column">Programmes</th>
                        <th class="phase-column">Phase</th>
                        <th class="item-column">Item</th>
                        <th class="keywords-column">Mots clés</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
            </div>
            <div class="grid-container">
                <tbody class="scrollable-tbody">
                    <?php foreach ($goodPractices as $goodPractice) { ?>
                        <tr>
                            <td class="programs-column">
                                <?php 
                                    $restore = FALSE;
                                    if ($profile === 'admin' || $profile === 'superadmin') {
                                        foreach (explode(', ', Sanitize($goodPractice['program_names'])) as $program) {
                                            $isHidden = substr($program, -2);
                                            $programName = substr($program, 0, -2);
                                            if ($isHidden === ':1') {
                                                $restore = TRUE;
                                                $programNames .= '<span class="darkred">'.$programName.'</span>, ';
                                            } else {
                                                $programNames .= $programName.', ';
                                            }
                                        }
                                        $programNames = rtrim($programNames, ', ');
                                        echo $programNames;
                                        $programNames = '';
                                    } else {
                                        echo Sanitize($goodPractice['program_names']);
                                    }
                                ?>
                            </td>
                            <td class="phase-column"><?= Sanitize($goodPractice['phase_name']) ?></td>
                            <td class="item-column"><?= $goodPractice['goodpractice_is_hidden'] === 1 ? '<span class="darkred">'.Sanitize($goodPractice['item']).'</span>' : Sanitize($goodPractice['item']) ?></td>
                            <td class="keywords-column"><?= Sanitize($goodPractice['keywords']) ?></td>
                            <td class="actions-column">
                                <div class="action-btn-container">
                                    <button class="action-btn" onclick="openGoodpracticeForm('<?= Sanitize($goodPractice['goodpractice_id']) ?>', '<?= Sanitize($goodPractice['program_names']) ?>', <?= ($goodPractice['goodpractice_is_hidden'] === 1 || $restore === TRUE) ? 1 : 0 ?>, '<?= Sanitize($profile) ?>')">Modifier</button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </div>
        </table>
    </div>

    <div class="form-popup" id="goodpracticeForm">
        <form action="manage_goodpractice.php" class="form-container" method="POST">
            <input type="hidden" id="goodpracticeId" name="goodpracticeId" value="">

            <h3>Modifier la bonne pratique</h3>
            <p>Vous pouvez : </p>
            <ul>
                <li>Dupliquer la bonne pratique pour un ou des programmes.</li>
                <?php if (isset($_SESSION['LOGGED_USER']) && ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' || $_SESSION['LOGGED_USER']['profile'] === 'admin')) : ?>
                    <li>Effacer ou supprimer définitivement la bonne pratique pour un ou des programmes.</li>
                    <li>Si aucun programme n'est sélectionné, effacer ou supprimer définitivement la bonne pratique pour tous les programmes.</li>
                    <li>Restaurer une bonne pratique supprimée par un opérateur.</li>
                <?php elseif (isset($_SESSION['LOGGED_USER']) && $_SESSION['LOGGED_USER']['profile'] === 'operator') : ?>
                    <li>Effacer ou supprimer la bonne pratique pour un ou des programmes.</li>
                    <li>Si aucun programme n'est sélectionné, effacer ou supprimer la bonne pratique pour tous les programmes.</li>
                <?php endif; ?>
            </ul>
            <div class="popup-programs-selection">
                <h4>Programme(s)</h4>
                <div class="popup-checkbox-area">
                    <?php foreach ($programs as $program): ?>
                        <div class="popup-checkbox-line">
                            <input class="popup-programs-checkbox" type="checkbox" id="<?= $program[0] ?>" name="programNames[]" value="<?= $program[0] ?>">
                            <label for="<?= $program[0] ?>"><?= $program[0] ?></label>
                        </div>
                    <?php endforeach; ?>   
                </div>         
            </div>
            <button type="submit" class="btn" name="submit" value="duplicate">Dupliquer</button>
            <?php if (isset($_SESSION['LOGGED_USER']) && ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' || $_SESSION['LOGGED_USER']['profile'] === 'admin')) : ?>
                <button type="submit" class="btn-warning" name="submit" value="delete">Supprimer définitivement</button>
            <?php elseif (isset($_SESSION['LOGGED_USER']) && $_SESSION['LOGGED_USER']['profile'] === 'operator') : ?>
                <button type="submit" class="btn-warning" name="submit" value="operator-delete">Supprimer</button>
            <?php endif; ?>
            <button type="submit" class="btn-warning" name="submit" value="erase">Effacer</button>
            <button type="button" class="btn" onclick="closeGoodpracticeForm()">Annuler</button>
        </form>
    </div>
</section>

<script>
    // Function to open the manage good practice form
    function openGoodpracticeForm(goodpracticeId, programNamesString, restore, profile) {
        // Set the good practice ID
        document.getElementById("goodpracticeId").value = goodpracticeId;
        
        // Process program names based on user profile
        var programNamesArray = [];
        if (profile !== 'admin' && profile !== 'superadmin') {
            programNamesArray = programNamesString.split(', ');
        } else {
            programNamesArray = programNamesString.replace(/:0|:1/g, '').split(', ');
        }

        // Highlight selected program names in the form
        const labels = document.querySelectorAll('.popup-programs-selection label');
        labels.forEach(label => {
            if (programNamesArray.includes(label.getAttribute('for'))) {
                label.style.color = 'red'; // Change label color to red
            } else {
                label.style.color = '#fff'; // Reset label color
            }        
        });

        // Manage visibility of restore button
        if (restore) {
            // Check if restore button exists
            if (!document.getElementById('restore-button')) {
                // Select erase and duplicate buttons
                const eraseButton = document.querySelector('button[value="erase"]');
                const duplicateButton = document.querySelector('button[value="duplicate"]');

                // Create restore button
                const restoreButton = document.createElement('button');
                restoreButton.type = 'submit';
                restoreButton.id = 'restore-button';
                restoreButton.className = 'btn';
                restoreButton.name = 'submit';
                restoreButton.value = 'restore';
                restoreButton.textContent = 'Restaurer';

                // Insert restore button before duplicate button
                eraseButton.parentNode.insertBefore(restoreButton, duplicateButton);
            }
        } else {
            // Remove restore button if restore is false
            const existingRestoreButton = document.getElementById('restore-button');
            if (existingRestoreButton) {
                existingRestoreButton.parentNode.removeChild(existingRestoreButton);
            }
        }

        // Display the manage good practice form
        document.getElementById("goodpracticeForm").style.display = "block";
    }

    // Function to close the manage good practice form
    function closeGoodpracticeForm() {
        document.getElementById("goodpracticeForm").style.display = "none";
    }
</script>
