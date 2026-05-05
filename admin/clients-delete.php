<?php
require_once '../admin_guard.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $client_id = (int)$_POST['client_id'];

    try {
        $pdo->prepare("DELETE FROM clients WHERE id = ?")->execute([$client_id]);
        header('Location: clients.php?success=deleted');
        exit;
    } catch (Exception $e) {
        header('Location: clients.php?error=delete_failed');
        exit;
    }
}

header('Location: clients.php');
exit;
