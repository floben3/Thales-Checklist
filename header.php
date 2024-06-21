<?php
    $path = $_SERVER['PHP_SELF'];
    $file = basename($path);
    // Creates navigation links based on user permissions
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8" />
		<link rel="stylesheet" href="./style.css" />
        <link rel="icon" type="image/x-icon" href="./img/favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Thales - Checklist</title>
	</head>
	<body>
        <header>
            <a href="https://www.thalesaleniaspace.com/fr">
                <img src="./img/logo.svg" alt="Logo Thales Alenia Space">
            </a>
            <a href="./index.php">
                <h1>Projet Checklist</h1>
            </a>
            <nav>
                <ul>
                    <li>
                        <a href="./index.php" class="<?= ($file === 'index.php') ? 'current-' : '' ?>page">Accueil</a>
                    </li>
                    <?php if (isset($_SESSION['LOGGED_USER'])) : ?>
                        <li>
                            <a href="./create_goodpractice.php" class="<?= ($file === 'create_goodpractice.php') ? 'current-' : '' ?>page">Créer une bonne pratique</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['LOGGED_USER']) && ($_SESSION['LOGGED_USER']['profile'] === 'superadmin' || $_SESSION['LOGGED_USER']['profile'] === 'admin')) : ?>
                        <li>
                            <a href="./delete_fields.php" class="<?= ($file === 'delete_fields.php') ? 'current-' : '' ?>page">Suppression</a>
                        </li>
                        <li>
                            <a href="./admin.php" class="<?= ($file === 'admin.php') ? 'current-' : '' ?>page">Administration</a>
                        </li>
                        <li>
                            <a href="./create_user.php" class="<?= ($file === 'create_user.php') ? 'current-' : '' ?>page">Créer un utilisateur</a>
                        </li>
                        <li>
                            <a href="./log.php" class="<?= ($file === 'log.php') ? 'current-' : '' ?>page">Logs</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['LOGGED_USER'])) : ?>
                        <li>
                            <a href="./logout.php" class="page">Se déconnecter</a>
                        </li>
                    <?php elseif ($file === 'index.php') : ?>
                        <li>
                            <a href="./login.php" class="page">Se connecter</a>
                        </li>
                    <?php elseif ($file === 'login.php') : ?>
                        <li>
                            <a href="./login.php" class="current-page">Se connecter</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <a href="https://univ-cotedazur.fr/">
                <img id ="uca" src="./img/uca.png" alt="Logo Université Côte-d'Azur">
            </a>
        </header>