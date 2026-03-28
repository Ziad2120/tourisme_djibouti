<?php
// includes/header.php
session_start();
$page_actuelle = basename($_SERVER['PHP_SELF'], '.php');

// Fonction pour vérifier si admin connecté
function adminConnecte() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>Tourisme Djibouti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&family=Noto+Kufi+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/', 1) - 1) ?>assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="/tourisme_djibouti/index.php" class="nav-brand">
        <span class="brand-ar">جيبوتي</span>
        <span class="brand-fr">Tourisme Djibouti</span>
    </a>
    <div class="nav-links">
        <a href="/tourisme_djibouti/index.php" class="<?= $page_actuelle === 'index' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Accueil
        </a>
        <a href="/tourisme_djibouti/carte.php" class="<?= $page_actuelle === 'carte' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M1 6v16l7-4 8 4 7-4V2l-7 4-8-4-7 4z"/><path d="M8 2v16M16 6v16"/></svg>
            Carte
        </a>
        <a href="/tourisme_djibouti/sites.php" class="<?= $page_actuelle === 'sites' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
            Sites
        </a>
        <a href="/tourisme_djibouti/parcours.php" class="<?= $page_actuelle === 'parcours' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
            Parcours
        </a>
        <a href="/tourisme_djibouti/avis.php" class="<?= $page_actuelle === 'avis' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Avis
        </a>
        <a href="/tourisme_djibouti/recherche.php" class="<?= $page_actuelle === 'recherche' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Recherche
        </a>
        <a href="/tourisme_djibouti/contact.php" class="<?= $page_actuelle === 'contact' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            Contact
        </a>
        <?php if (adminConnecte()): ?>
        <a href="/tourisme_djibouti/admin/index.php" class="btn-admin">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Admin
        </a>
        <?php endif; ?>
    </div>
    <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>
</nav>
