<?php
// includes/header.php - Navigation & Head commune
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= isset($page_title) ? $page_title . ' — Teranga Azur' : 'Teranga Azur — À toi ton toit de rêve' ?></title>
  <meta name="description" content="<?= isset($page_desc) ? $page_desc : 'Teranga Azur, location de villas de luxe avec piscine au bord de la mer. Découvrez nos villas d\'exception et nos activités.' ?>">

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="css/style.css">
  <?php if (isset($extra_css)): ?>
  <link rel="stylesheet" href="css/<?= $extra_css ?>">
  <?php endif; ?>
</head>
<body class="<?= $current_page ?>-page">

<nav class="navbar" id="navbar">
  <a href="index.php" class="navbar-logo">
    <img src="images/Logo.png" alt="Teranga Azur Logo">
    <div class="brand-text">
      <span class="brand-name">TERANGA AZUR</span>
      <span class="brand-tagline">À toi ton toit de rêve</span>
    </div>
  </a>

  <ul class="nav-links" id="navLinks">
    <li><a href="index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">Accueil</a></li>
    <li><a href="villas.php" class="<?= $current_page === 'villas' ? 'active' : '' ?>">Nos Villas</a></li>
    <li><a href="activites.php" class="<?= $current_page === 'activites' ? 'active' : '' ?>">Activités</a></li>
    <?php if (isset($_SESSION['client_id'])): ?>
    <li><a href="mes-reservations.php" class="<?= $current_page === 'mes-reservations' ? 'active' : '' ?>">Mes Réservations</a></li>
    <li><a href="logout.php">Déconnexion</a></li>
    <?php else: ?>
    <li><a href="inscription.php" class="<?= $current_page === 'inscription' ? 'active' : '' ?>">S'inscrire</a></li>
    <li><a href="connexion.php" class="nav-cta">Se Connecter</a></li>
    <?php endif; ?>
  </ul>

  <div class="hamburger" id="hamburger" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
  </div>
</nav>
