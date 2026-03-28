<?php
// admin/includes/auth.php — Vérification session admin
session_start();

function adminConnecte() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requiertAdmin() {
    if (!adminConnecte()) {
        header('Location: ' . BASE_ADMIN . 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Chemin de base admin
define('BASE_ADMIN', '/tourisme_djibouti/admin/');
define('BASE_SITE',  '/tourisme_djibouti/');
