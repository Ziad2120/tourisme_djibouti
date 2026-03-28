<?php
// admin/includes/admin_header.php
$admin_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? htmlspecialchars($page_title).' — ' : '' ?>Admin · Tourisme Djibouti</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@600&display=swap" rel="stylesheet">
<style>
:root {
    --bg:        #0F1923;
    --surface:   #172130;
    --surface2:  #1E2D40;
    --border:    #253650;
    --ocean:     #1A5276;
    --ocean-l:   #2471A3;
    --coral:     #C0533A;
    --or:        #C89B3C;
    --or-l:      #F0C060;
    --green:     #27AE60;
    --red:       #C0392B;
    --text:      #E8E0D0;
    --text-dim:  #7A8FA6;
    --sand:      #E8D5A3;
}
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; }

/* ---- SIDEBAR ---- */
.sidebar {
    width:240px; background:var(--surface); border-right:1px solid var(--border);
    display:flex; flex-direction:column; position:fixed; top:0; bottom:0; left:0; z-index:100;
    overflow-y:auto;
}
.sidebar-brand {
    padding:20px 20px 16px;
    border-bottom:1px solid var(--border);
}
.sidebar-brand .brand-title {
    font-family:'Cormorant Garamond',serif;
    font-size:1.1rem; color:var(--sand); line-height:1.2;
}
.sidebar-brand .brand-sub {
    font-size:0.68rem; color:var(--text-dim); letter-spacing:2px;
    text-transform:uppercase; margin-top:4px;
}
.sidebar-admin-info {
    padding:12px 20px; background:var(--surface2);
    border-bottom:1px solid var(--border);
    font-size:0.78rem;
}
.sidebar-admin-info .admin-name { color:var(--or); font-weight:600; }
.sidebar-admin-info .admin-role { color:var(--text-dim); font-size:0.7rem; margin-top:2px; }

.sidebar-nav { flex:1; padding:12px 0; }
.nav-section-title {
    font-size:0.62rem; text-transform:uppercase; letter-spacing:2px;
    color:var(--text-dim); padding:10px 20px 6px; font-weight:600;
}
.sidebar-nav a {
    display:flex; align-items:center; gap:10px;
    padding:10px 20px; text-decoration:none;
    color:var(--text-dim); font-size:0.83rem;
    transition:all 0.15s; border-left:3px solid transparent;
}
.sidebar-nav a:hover { color:var(--text); background:var(--surface2); }
.sidebar-nav a.active { color:var(--or); background:rgba(200,155,60,0.08); border-left-color:var(--or); }
.sidebar-nav a .nav-icon { font-size:1rem; width:18px; text-align:center; }
.sidebar-nav a .nav-badge {
    margin-left:auto; background:var(--coral);
    color:white; font-size:0.62rem; padding:1px 6px; border-radius:10px;
}

.sidebar-footer {
    padding:14px 20px; border-top:1px solid var(--border);
    display:flex; gap:10px;
}
.sidebar-footer a {
    flex:1; padding:8px; text-align:center;
    background:var(--surface2); color:var(--text-dim);
    text-decoration:none; border-radius:4px; font-size:0.78rem;
    transition:all 0.15s;
}
.sidebar-footer a:hover { background:var(--ocean); color:white; }

/* ---- MAIN ---- */
.main-content {
    margin-left:240px; flex:1; display:flex; flex-direction:column; min-height:100vh;
}
.topbar {
    background:var(--surface); border-bottom:1px solid var(--border);
    padding:14px 32px; display:flex; align-items:center; justify-content:space-between;
    position:sticky; top:0; z-index:50;
}
.topbar-title { font-size:1rem; font-weight:600; color:var(--text); }
.topbar-actions { display:flex; gap:10px; align-items:center; }

.page-body { padding:28px 32px; flex:1; }

/* ---- COMPOSANTS ---- */
.btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:4px; border:none;
    font-size:0.83rem; font-weight:500; cursor:pointer;
    text-decoration:none; transition:all 0.15s;
    font-family:'Inter',sans-serif;
}
.btn-primary { background:var(--ocean); color:white; }
.btn-primary:hover { background:var(--ocean-l); }
.btn-success { background:var(--green); color:white; }
.btn-success:hover { filter:brightness(1.1); }
.btn-danger  { background:var(--red); color:white; }
.btn-danger:hover  { filter:brightness(1.1); }
.btn-ghost   { background:var(--surface2); color:var(--text-dim); border:1px solid var(--border); }
.btn-ghost:hover   { color:var(--text); border-color:var(--text-dim); }
.btn-sm { padding:6px 12px; font-size:0.78rem; }
.btn-or { background:var(--or); color:#1a0f00; }
.btn-or:hover { background:var(--or-l); }

/* Stats cards */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:28px; }
.stat-card {
    background:var(--surface); border:1px solid var(--border); border-radius:8px;
    padding:20px; position:relative; overflow:hidden;
}
.stat-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
}
.stat-card.blue::before  { background:var(--ocean-l); }
.stat-card.coral::before { background:var(--coral); }
.stat-card.gold::before  { background:var(--or); }
.stat-card.green::before { background:var(--green); }
.stat-card.red::before   { background:var(--red); }

.stat-icon { font-size:1.8rem; margin-bottom:10px; }
.stat-num  { font-size:2rem; font-weight:700; color:var(--text); line-height:1; }
.stat-label { font-size:0.75rem; color:var(--text-dim); margin-top:4px; text-transform:uppercase; letter-spacing:0.5px; }

/* Table */
.table-card {
    background:var(--surface); border:1px solid var(--border); border-radius:8px;
    overflow:hidden; margin-bottom:24px;
}
.table-card-header {
    padding:14px 20px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between;
}
.table-card-header h3 { font-size:0.9rem; font-weight:600; color:var(--text); }
table.admin-table { width:100%; border-collapse:collapse; }
table.admin-table th {
    padding:10px 16px; text-align:left;
    font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;
    color:var(--text-dim); border-bottom:1px solid var(--border);
    background:var(--surface2);
}
table.admin-table td {
    padding:12px 16px; font-size:0.83rem; color:var(--text);
    border-bottom:1px solid rgba(37,54,80,0.5);
    vertical-align:middle;
}
table.admin-table tr:last-child td { border-bottom:none; }
table.admin-table tr:hover td { background:var(--surface2); }

/* Badges */
.badge {
    display:inline-block; padding:3px 8px; border-radius:3px;
    font-size:0.68rem; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;
}
.badge-naturel    { background:rgba(30,107,58,0.25); color:#7FC99A; }
.badge-culturel   { background:rgba(123,63,160,0.25); color:#C9A0F0; }
.badge-historique { background:rgba(139,80,0,0.25); color:var(--or-l); }
.badge-gastronomique { background:rgba(160,48,10,0.25); color:#F09070; }
.badge-actif      { background:rgba(39,174,96,0.2); color:#6FD09A; }
.badge-inactif    { background:rgba(192,57,43,0.2); color:#F08070; }

/* Form */
.form-grid { display:grid; gap:16px; }
.form-grid.cols2 { grid-template-columns:1fr 1fr; }
.form-grid.cols3 { grid-template-columns:1fr 1fr 1fr; }
.form-group label {
    display:block; font-size:0.75rem; font-weight:500;
    color:var(--text-dim); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;
}
.form-group input,
.form-group select,
.form-group textarea {
    width:100%; padding:10px 12px;
    background:var(--surface2); border:1.5px solid var(--border);
    color:var(--text); border-radius:4px;
    font-size:0.85rem; font-family:'Inter',sans-serif;
    transition:border-color 0.15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline:none; border-color:var(--ocean-l);
    box-shadow:0 0 0 3px rgba(36,113,163,0.12);
}
.form-group select option { background:var(--surface2); }
.form-group textarea { resize:vertical; min-height:100px; }
.form-group .help { font-size:0.72rem; color:var(--text-dim); margin-top:5px; }

.form-check { display:flex; align-items:center; gap:10px; padding:10px 0; }
.form-check input[type=checkbox] { width:18px; height:18px; accent-color:var(--ocean-l); }
.form-check label { font-size:0.85rem; color:var(--text); cursor:pointer; }

/* Alerts */
.alert {
    padding:12px 16px; border-radius:4px;
    font-size:0.85rem; margin-bottom:20px;
    display:flex; align-items:center; gap:10px;
    border-left:4px solid;
}
.alert-success { background:rgba(39,174,96,0.1); border-color:var(--green); color:#6FD09A; }
.alert-error   { background:rgba(192,57,43,0.1); border-color:var(--red); color:#F08070; }
.alert-info    { background:rgba(26,82,118,0.15); border-color:var(--ocean-l); color:#88BBDD; }

/* Image preview */
.img-preview {
    width:80px; height:55px; object-fit:cover; border-radius:4px;
    border:1px solid var(--border);
}
.no-img {
    width:80px; height:55px; background:var(--surface2);
    border-radius:4px; border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem;
}

/* Confirm delete overlay */
.confirm-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,0.7); z-index:1000;
    align-items:center; justify-content:center;
}
.confirm-overlay.show { display:flex; }
.confirm-box {
    background:var(--surface); border:1px solid var(--border);
    border-radius:8px; padding:28px; max-width:400px; width:90%;
    text-align:center;
}
.confirm-box h3 { margin-bottom:10px; color:var(--text); }
.confirm-box p  { color:var(--text-dim); font-size:0.85rem; margin-bottom:20px; }
.confirm-box .btn-row { display:flex; gap:10px; justify-content:center; }

/* Responsive */
@media(max-width:768px){
    .sidebar { width:200px; }
    .main-content { margin-left:200px; }
    .page-body { padding:16px; }
    .form-grid.cols2, .form-grid.cols3 { grid-template-columns:1fr; }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-title">🌍 Tourisme Djibouti</div>
        <div class="brand-sub">Dashboard Admin</div>
    </div>
    <div class="sidebar-admin-info">
        <div class="admin-name">👤 <?= htmlspecialchars($_SESSION['admin_nom'] ?? 'Administrateur') ?></div>
        <div class="admin-role">Accès complet</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="/tourisme_djibouti/admin/index.php" class="<?= $admin_page==='index'?'active':'' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="nav-section-title">Contenu</div>
        <a href="/tourisme_djibouti/admin/sites.php" class="<?= $admin_page==='sites'||$admin_page==='site_form'?'active':'' ?>">
            <span class="nav-icon">🌋</span> Sites touristiques
        </a>
        <a href="/tourisme_djibouti/admin/parcours.php" class="<?= $admin_page==='parcours'||$admin_page==='parcours_form'?'active':'' ?>">
            <span class="nav-icon">🗺️</span> Parcours
        </a>
        <a href="/tourisme_djibouti/admin/fiches.php" class="<?= $admin_page==='fiches'?'active':'' ?>">
            <span class="nav-icon">📄</span> Fiches historiques
        </a>
        <a href="/tourisme_djibouti/admin/avis.php" class="<?= $admin_page==='avis'?'active':'' ?>">
            <span class="nav-icon">⭐</span> Avis touristes
            <?php
            // Badge avis non lus
            if (isset($pdo)) {
                $nb_avis = $pdo->query("SELECT COUNT(*) FROM avis WHERE valide=1")->fetchColumn();
                if ($nb_avis > 0) echo '<span class="nav-badge">'.$nb_avis.'</span>';
            }
            ?>
        </a>

        <div class="nav-section-title">Système</div>
        <a href="/tourisme_djibouti/admin/compte.php" class="<?= $admin_page==='compte'?'active':'' ?>">
            <span class="nav-icon">⚙️</span> Mon compte
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="/tourisme_djibouti/index.php" target="_blank">🌐 Voir le site</a>
        <a href="/tourisme_djibouti/admin/logout.php">🔒 Déconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-content">
<div class="topbar">
    <div class="topbar-title"><?= $page_title ?? 'Dashboard' ?></div>
    <div class="topbar-actions">
        <span style="font-size:0.78rem;color:var(--text-dim);"><?= date('d/m/Y H:i') ?></span>
        <a href="/tourisme_djibouti/admin/sites.php?action=nouveau" class="btn btn-or btn-sm">+ Ajouter un site</a>
    </div>
</div>
<div class="page-body">

<!-- Confirm delete modal -->
<div class="confirm-overlay" id="confirm-overlay">
    <div class="confirm-box">
        <div style="font-size:2rem;margin-bottom:12px;">⚠️</div>
        <h3>Confirmer la suppression</h3>
        <p id="confirm-msg">Cette action est irréversible.</p>
        <div class="btn-row">
            <button class="btn btn-ghost" onclick="closeConfirm()">Annuler</button>
            <a id="confirm-link" href="#" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
</div>
<script>
function confirmDelete(url, nom) {
    document.getElementById('confirm-msg').textContent = 'Supprimer "' + nom + '" ? Cette action est irréversible.';
    document.getElementById('confirm-link').href = url;
    document.getElementById('confirm-overlay').classList.add('show');
}
function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('show'); }
</script>
