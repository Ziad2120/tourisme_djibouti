<?php
require_once 'includes/auth.php';
require_once '../config/db.php';
requiertAdmin();
$page_title = 'Modérer les avis';

// Actions
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($action === 'supprimer' && $id) {
    $pdo->prepare("DELETE FROM avis WHERE id=?")->execute([$id]);
    header('Location: avis.php?msg=supprime'); exit;
}
if ($action === 'toggle' && $id) {
    $pdo->prepare("UPDATE avis SET valide = NOT valide WHERE id=?")->execute([$id]);
    header('Location: avis.php?msg=modifie'); exit;
}

$msg = '';
if (isset($_GET['msg'])) {
    $msgs=['supprime'=>'🗑️ Avis supprimé.','modifie'=>'✅ Statut modifié.'];
    $t = $_GET['msg']==='supprime' ? 'alert-error' : 'alert-success';
    $msg = '<div class="alert '.$t.'">'.$msgs[$_GET['msg']].'</div>';
}

$filtre = $_GET['filtre'] ?? 'tous';
$where  = $filtre==='valides' ? 'WHERE a.valide=1' : ($filtre==='masques' ? 'WHERE a.valide=0' : '');

$avis_list = $pdo->query("
    SELECT a.*, s.nom AS site_nom
    FROM avis a JOIN sites s ON s.id=a.site_id
    $where
    ORDER BY a.date_avis DESC
")->fetchAll();

include 'includes/admin_header.php';
?>
<?= $msg ?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
    <a href="avis.php" class="btn btn-ghost btn-sm <?= $filtre==='tous'?'btn-primary':'' ?>">Tous (<?= count($avis_list) ?>)</a>
    <a href="avis.php?filtre=valides"  class="btn btn-ghost btn-sm <?= $filtre==='valides'?'btn-primary':'' ?>">✅ Visibles</a>
    <a href="avis.php?filtre=masques" class="btn btn-ghost btn-sm <?= $filtre==='masques'?'btn-primary':'' ?>">🙈 Masqués</a>
</div>
<div class="table-card">
    <div class="table-card-header"><h3>⭐ Avis touristes (<?= count($avis_list) ?>)</h3></div>
    <table class="admin-table">
        <thead><tr><th>Visiteur</th><th>Site</th><th>Note</th><th>Commentaire</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($avis_list as $a): ?>
        <tr>
            <td style="font-weight:500;"><?= htmlspecialchars($a['nom_visiteur'] ?? 'Anonyme') ?></td>
            <td style="color:var(--text-dim);font-size:0.8rem;"><?= htmlspecialchars($a['site_nom']) ?></td>
            <td style="color:var(--or);"><?= str_repeat('★',$a['note']) ?><span style="font-size:0.75rem;color:var(--text-dim);"> <?=$a['note']?>/5</span></td>
            <td style="max-width:240px;font-size:0.82rem;color:var(--text-dim);"><?= htmlspecialchars(mb_strimwidth($a['commentaire'],0,100,'…')) ?></td>
            <td style="font-size:0.78rem;color:var(--text-dim);"><?= date('d/m/Y',strtotime($a['date_avis'])) ?></td>
            <td><span class="badge <?= $a['valide']?'badge-actif':'badge-inactif' ?>"><?= $a['valide']?'Visible':'Masqué' ?></span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="avis.php?action=toggle&id=<?=$a['id']?>" class="btn btn-ghost btn-sm" title="Basculer visibilité"><?= $a['valide']?'🙈':'👁️' ?></a>
                    <button onclick="confirmDelete('avis.php?action=supprimer&id=<?=$a['id']?>', 'cet avis')" class="btn btn-danger btn-sm">🗑️</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($avis_list)): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--text-dim);padding:40px;">Aucun avis.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/admin_footer.php'; ?>
