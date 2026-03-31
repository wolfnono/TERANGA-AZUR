<?php
/**
 * admin_guard.php — Middleware de protection des pages admin
 * À inclure en tête de chaque page du dossier admin/
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../connexion.php?redirect=admin/dashboard.php');
    exit;
}
