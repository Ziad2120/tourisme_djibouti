<?php
require_once 'includes/auth.php';
require_once '../config/db.php';
requiertAdmin();

$action = $_GET['action'] ?? 'liste';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';

if ($action==='supprimer' && $id) {
    $pdo->prepare("DELETE FROM fiches_historiques WHERE id=?")->execute([$id]);
    header('Location: fiches.php?msg=supprime'); exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['sauvegarder'])) {
    $site_id  = (int)($_POST['site_id'] ?? 0);
    $titre    = trim($_POST['titre'] ?? '');
    $contenu  = trim($_POST['contenu'] ?? '');
    $langue   = $_POST['langue'] ?? 'fr';
    $fiche_id = (int)($_POST['fiche_id'] ?? 0);

    if (!$site_id || !$titre || !$contenu) {
        $msg = '<div class="alert alert-error">⚠️ Tous les champs sont obligatoires.</div>';
    } else {
        if ($fiche_id) {
            $pdo->prepare("UPDATE fiches_historiques SET site_id=?,titre=?,contenu=?,langue=? WHERE id=?")
                ->execute([$site_id,$titre,$contenu,$langue,$fiche_id]);
        } else {
            $pdo->prepare("INSERT INTO fiches_historiques (site_id,titre,contenu,langue) VALUES(?,?,?,?)")
                ->execute([$site_id,$titre,$contenu,$langue]);
        }
        header("Location: fiches.php?msg=".($fiche_id?'modifie':'ajoute')); exit;
    }
}

if ($action==='nouveau' || $action==='modifier') {
    $page_title = $action==='nouveau' ? 'Nouvelle fiche historique' : 'Modifier une fiche';
    $fiche = [];
    if ($action==='modifier' && $id) {
        $stmt=$pdo->prepare("SELECT * FROM fiches_historiques WHERE id=?"); $stmt->execute([$id]);
        $fiche=$stmt->fetch();
        if (!$fiche) { header('Location: fiches.php'); exit; }
    }
    $sites_list = $pdo->query("SELECT id,nom FROM sites WHERE actif=1 ORDER BY nom")->fetchAll();
    include 'includes/admin_header.php';
    ?>
    <div style="max-width:800px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <a href="fiches.php" class="btn btn-ghost btn-sm">← Retour</a>
            <h2 style="font-size:1.1rem;"><?= $action==='nouveau' ? '➕ Nouvelle fiche' : '✏️ Modifier la fiche' ?></h2>
        </div>
        <?= $msg ?>
        <form method="POST" class="table-card" style="padding:24px;">
            <input type="hidden" name="fiche_id" value="<?= $fiche['id'] ?? '' ?>">
            <div class="form-grid cols2">
                <div class="form-group">
                    <label>Site concerné *</label>
                    <select name="site_id" required>
                        <option value="">— Choisir un site —</option>
                        <?php foreach ($sites_list as $s): ?>
                        <option value="<?=$s['id']?>" <?= ($fiche['site_id']??0)==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Langue</label>
                    <select name="langue">
                        <?php foreach(['fr'=>'Français','ar'=>'Arabe','en'=>'Anglais','so'=>'Somali'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?= ($fiche['langue']??'fr')===$v?'selected':'' ?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Titre de la fiche *</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($fiche['titre'] ?? '') ?>" required placeholder="Ex: Géologie du Lac Assal">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Contenu historique *</label>
                    <textarea name="contenu" rows="10" required placeholder="Rédigez le texte historique…"><?= htmlspecialchars($fiche['contenu'] ?? '') ?></textarea>
                    <div class="help">Ce texte sera lu à voix haute par l'audio-guide TTS sur la page du site.</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:16px;border-top:1px solid var(--border);margin-top:8px;">
                <button type="submit" name="sauvegarder" class="btn btn-success">
                    <?= $action==='nouveau' ? '✅ Créer la fiche' : '💾 Enregistrer' ?>
                </button>
                <a href="fiches.php" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
    <?php include 'includes/admin_footer.php'; exit;
}

// LISTE
$page_title = 'Fiches historiques';
if (isset($_GET['msg'])) {
    $msgs=['ajoute'=>'✅ Fiche créée !','modifie'=>'💾 Fiche modifiée.','supprime'=>'🗑️ Fiche supprimée.'];
    $t=$_GET['msg']==='supprime'?'alert-error':'alert-success';
    $msg='<div class="alert '.$t.'">'.$msgs[$_GET['msg']].'</div>';
}

$fiches = $pdo->query("
    SELECT f.*, s.nom AS site_nom
    FROM fiches_historiques f JOIN sites s ON s.id=f.site_id
    ORDER BY s.nom, f.langue
")->fetchAll();

include 'includes/admin_header.php';
?>
<?= $msg ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
    <a href="fiches.php?action=nouveau" class="btn btn-or">+ Nouvelle fiche</a>
</div>
<div class="table-card">
    <div class="table-card-header"><h3>📄 Fiches historiques (<?= count($fiches) ?>)</h3></div>
    <table class="admin-table">
        <thead><tr><th>Titre</th><th>Site</th><th>Langue</th><th>Extrait</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($fiches as $f): ?>
        <tr>
            <td style="font-weight:500;"><?= htmlspecialchars($f['titre']) ?></td>
            <td style="color:var(--or);"><?= htmlspecialchars($f['site_nom']) ?></td>
            <td><span class="badge badge-actif"><?= strtoupper($f['langue']) ?></span></td>
            <td style="font-size:0.78rem;color:var(--text-dim);max-width:200px;"><?= htmlspecialchars(mb_strimwidth($f['contenu'],0,80,'…')) ?></td>
            <td style="font-size:0.78rem;color:var(--text-dim);"><?= date('d/m/Y',strtotime($f['date_creation'])) ?></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="fiches.php?action=modifier&id=<?=$f['id']?>" class="btn btn-primary btn-sm">✏️</a>
                    <button onclick="confirmDelete('fiches.php?action=supprimer&id=<?=$f['id']?>', '<?= addslashes($f['titre']) ?>')" class="btn btn-danger btn-sm">🗑️</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/admin_footer.php'; ?>
