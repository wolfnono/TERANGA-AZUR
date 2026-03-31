<?php
// logout.php — Déconnexion sécurisée
session_start();
session_destroy();
header('Location: index.php');
exit;
