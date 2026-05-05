<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../connexion.php?redirect=admin/dashboard.php');
    exit;
}
