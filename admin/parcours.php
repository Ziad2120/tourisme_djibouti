<?php
require_once 'includes/auth.php';
require_once '../config/db.php';
requiertAdmin();

$action = $_GET['action'] ?? 'liste';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';

// Suppression
if ($action === 'supprimer' && $id) {
    $pdo->prepare("DELETE FROM parcours WHERE id=?")->execute([$id]);
    header('Location: parcours.php?msg=supprime'); exit;
}

// Toggle actif
if ($action === 'toggle' && $id) {
    $pdo->prepare("UPDATE parcours SET actif = NOT actif WHERE id=?")->execute([$id]);
    header('Location: parcours.php?msg=modifie'); exit;
}

// Sauvegarde
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['sauvegarder'])) {
    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $theme       = $_POST['theme'] ?? 'nature';
    $difficulte  = $_POST['difficulte'] ?? 'facile';
    $duree       = (int)($_POST['duree_estimee'] ?? 0);
    $distance    = $_POST['distance_km'] !== '' ? (float)$_POST['distance_km'] : null;
    $actif       = isset($_POST['actif']) ? 1 : 0;
    $sites_sel   = $_POST['sites'] ?? [];
    $parc_id     = (int)($_POST['parcours_id'] ?? 0);

    if (!$titre) {
        $msg = '<div class="alert alert-error">⚠️ Le titre est obligatoire.</div>';
    } else {
        if ($parc_id) {
            $pdo->prepare("UPDATE parcours SET titre=?,description=?,theme=?,difficulte=?,duree_estimee=?,distance_km=?,actif=? WHERE id=?")
                ->execute([$titre,$description,$theme,$difficulte,$duree,$distance,$actif,$parc_id]);
        } else {
            $pdo->prepare("INSERT INTO parcours (titre,description,theme,difficulte,duree_estimee,distance_km,actif) VALUES(?,?,?,?,?,?,?)")
                ->execute([$titre,$description,$theme,$difficulte,$duree,$distance,$actif]);
            $parc_id = $pdo->lastInsertId();
        }
        // Resynchroniser les sites du parcours
        $pdo->prepare("DELETE FROM parcours_sites WHERE parcours_id=?")->execute([$parc_id]);
        foreach ($sites_sel as $ordre => $site_id) {
            $pdo->prepare("INSERT INTO parcours_sites (parcours_id,site_id,ordre) VALUES(?,?,?)")
                ->execute([$parc_id, (int)$site_id, $ordre+1]);
        }
        header("Location: parcours.php?msg=".($_POST['parcours_id']?'modifie':'ajoute')); exit;
    }
}

// Formulaire
if ($action === 'nouveau' || $action === 'modifier') {
    $page_title = $action==='nouveau' ? 'Nouveau parcours' : 'Modifier un parcours';
    $p = []; $sites_parc = [];
    if ($action==='modifier' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM parcours WHERE id=?"); $stmt->execute([$id]);
        $p = $stmt->fetch();
        if (!$p) { header('Location: parcours.php'); exit; }
        $stmt = $pdo->prepare("SELECT site_id FROM parcours_sites WHERE parcours_id=? ORDER BY ordre");
        $stmt->execute([$id]);
        $sites_parc = array_column($stmt->fetchAll(), 'site_id');
    }
    $tous_sites = $pdo->query("SELECT id,nom,categorie FROM sites WHERE actif=1 ORDER BY categorie,nom")->fetchAll();
    include 'includes/admin_header.php';
    ?>
    <div style="max-width:800px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <a href="parcours.php" class="btn btn-ghost btn-sm">← Retour</a>
            <h2 style="font-size:1.1rem;color:var(--text);">
                <?= $action==='nouveau' ? '➕ Nouveau parcours' : '✏️ '.htmlspecialchars($p['titre']) ?>
            </h2>
        </div>
        <?= $msg ?>
        <form method="POST" class="table-card" style="padding:24px;">
            <input type="hidden" name="parcours_id" value="<?= $p['id'] ?? '' ?>">
            <div class="form-grid cols2">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Titre du parcours *</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($p['titre'] ?? '') ?>" required placeholder="Ex: Merveilles naturelles">
                </div>
                <div class="form-group">
                    <label>Thème</label>
                    <select name="theme">
                        <?php foreach(['nature','culture','histoire','aventure','gastronomie'] as $t): ?>
                        <option value="<?=$t?>" <?= ($p['theme']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Difficulté</label>
                    <select name="difficulte">
                        <?php foreach(['facile','moyen','difficile'] as $d): ?>
                        <option value="<?=$d?>" <?= ($p['difficulte']??'')===$d?'selected':'' ?>><?= ucfirst($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Durée estimée (minutes)</label>
                    <input type="number" name="duree_estimee" value="<?= $p['duree_estimee'] ?? '' ?>" placeholder="180">
                </div>
                <div class="form-group">
                    <label>Distance (km)</label>
                    <input type="number" name="distance_km" step="0.1" value="<?= $p['distance_km'] ?? '' ?>" placeholder="50.5">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Décrivez ce parcours…"><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Sélection des sites -->
            <div style="margin:16px 0;">
                <label style="display:block;font-size:0.75rem;font-weight:500;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
                    Sites du parcours (sélectionnez dans l'ordre de visite)
                </label>
                <div id="sites-selected" style="min-height:40px;border:1.5px dashed var(--border);border-radius:4px;padding:10px;margin-bottom:10px;display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach ($sites_parc as $i => $sid):
                        $sn = array_filter($tous_sites, fn($s) => $s['id']==$sid);
                        $sn = reset($sn);
                    ?>
                    <div class="site-chip" data-id="<?=$sid?>" style="background:var(--ocean);color:white;padding:4px 10px;border-radius:3px;font-size:0.78rem;cursor:pointer;display:flex;align-items:center;gap:6px;">
                        <span><?= $i+1 ?>. <?= htmlspecialchars($sn['nom'] ?? 'Site #'.$sid) ?></span>
                        <span onclick="retirerSite(this)" style="opacity:0.6;font-size:0.9rem;">✕</span>
                        <input type="hidden" name="sites[]" value="<?=$sid?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px;max-height:220px;overflow-y:auto;padding:10px;background:var(--surface2);border-radius:4px;">
                    <?php foreach ($tous_sites as $s): ?>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:6px 8px;border-radius:3px;transition:background 0.15s;font-size:0.8rem;"
                           onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background=''"
                           onclick="ajouterSite(<?=$s['id']?>, '<?= addslashes($s['nom']) ?>')">
                        <span style="font-size:1rem;"><?= ['naturel'=>'🌋','culturel'=>'🕌','historique'=>'🏛️','gastronomique'=>'🍽️'][$s['categorie']] ?></span>
                        <span style="color:var(--text);"><?= htmlspecialchars($s['nom']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" name="actif" id="actif" value="1" <?= ($p['actif'] ?? 1) ? 'checked' : '' ?>>
                <label for="actif">✅ Parcours visible sur le site public</label>
            </div>

            <div style="display:flex;gap:10px;padding-top:16px;border-top:1px solid var(--border);margin-top:16px;">
                <button type="submit" name="sauvegarder" class="btn btn-success">
                    <?= $action==='nouveau' ? '✅ Créer le parcours' : '💾 Enregistrer' ?>
                </button>
                <a href="parcours.php" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
    <script>
    var sitesSelectionnes = [<?= implode(',', array_map('intval', $sites_parc)) ?>];

    function ajouterSite(id, nom) {
        if (sitesSelectionnes.includes(id)) return;
        sitesSelectionnes.push(id);
        var container = document.getElementById('sites-selected');
        var n = container.querySelectorAll('.site-chip').length + 1;
        var chip = document.createElement('div');
        chip.className = 'site-chip';
        chip.dataset.id = id;
        chip.style.cssText = 'background:var(--ocean);color:white;padding:4px 10px;border-radius:3px;font-size:0.78rem;cursor:pointer;display:flex;align-items:center;gap:6px;';
        chip.innerHTML = '<span>' + n + '. ' + nom + '</span><span onclick="retirerSite(this)" style="opacity:0.6;font-size:0.9rem;">✕</span><input type="hidden" name="sites[]" value="' + id + '">';
        container.appendChild(chip);
    }

    function retirerSite(btn) {
        var chip = btn.closest('.site-chip');
        var id = parseInt(chip.dataset.id);
        sitesSelectionnes = sitesSelectionnes.filter(s => s !== id);
        chip.remove();
        // Renuméroter
        document.querySelectorAll('.site-chip').forEach(function(c, i) {
            c.querySelector('span').textContent = (i+1) + '. ' + c.querySelector('span').textContent.replace(/^\d+\. /, '');
        });
    }
    </script>
    <?php include 'includes/admin_footer.php'; exit;
}

// LISTE
$page_title = 'Gérer les parcours';
if (isset($_GET['msg'])) {
    $msgs=['ajoute'=>'✅ Parcours créé !','modifie'=>'💾 Parcours modifié.','supprime'=>'🗑️ Parcours supprimé.'];
    $msg='<div class="alert '.($_GET['msg']==='supprime'?'alert-error':'alert-success').'">'.$msgs[$_GET['msg']].'</div>';
}

$parcours_list = $pdo->query("
    SELECT p.*, COUNT(DISTINCT ps.site_id) AS nb_sites
    FROM parcours p LEFT JOIN parcours_sites ps ON ps.parcours_id=p.id
    GROUP BY p.id ORDER BY p.date_creation DESC
")->fetchAll();

include 'includes/admin_header.php';
?>
<?= $msg ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
    <a href="parcours.php?action=nouveau" class="btn btn-or">+ Nouveau parcours</a>
</div>
<div class="table-card">
    <div class="table-card-header"><h3>🗺️ Parcours (<?= count($parcours_list) ?>)</h3></div>
    <table class="admin-table">
        <thead><tr><th>Titre</th><th>Thème</th><th>Difficulté</th><th>Durée</th><th>Sites</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($parcours_list as $p): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($p['titre']) ?></td>
            <td><span class="badge badge-naturel"><?= $p['theme'] ?></span></td>
            <td><?= ucfirst($p['difficulte']) ?></td>
            <td><?= $p['duree_estimee'] ?> min</td>
            <td style="text-align:center;"><?= $p['nb_sites'] ?></td>
            <td><span class="badge <?= $p['actif']?'badge-actif':'badge-inactif' ?>"><?= $p['actif']?'Visible':'Masqué' ?></span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="parcours.php?action=modifier&id=<?=$p['id']?>" class="btn btn-primary btn-sm">✏️</a>
                    <a href="parcours.php?action=toggle&id=<?=$p['id']?>" class="btn btn-ghost btn-sm"><?= $p['actif']?'👁️':'🙈' ?></a>
                    <a href="/tourisme_djibouti/parcours_detail.php?id=<?=$p['id']?>" target="_blank" class="btn btn-ghost btn-sm">🌐</a>
                    <button onclick="confirmDelete('parcours.php?action=supprimer&id=<?=$p['id']?>', '<?= addslashes($p['titre']) ?>')" class="btn btn-danger btn-sm">🗑️</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/admin_footer.php'; ?>
