<?php
require_once 'includes/auth.php';
require_once '../config/db.php';
requiertAdmin();

$action = $_GET['action'] ?? 'liste';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';

// ============================================================
//  SUPPRESSION
// ============================================================
if ($action === 'supprimer' && $id) {
    $pdo->prepare("DELETE FROM sites WHERE id = ?")->execute([$id]);
    header('Location: sites.php?msg=supprime'); exit;
}

// ============================================================
//  BASCULER ACTIF/INACTIF
// ============================================================
if ($action === 'toggle' && $id) {
    $pdo->prepare("UPDATE sites SET actif = NOT actif WHERE id = ?")->execute([$id]);
    header('Location: sites.php?msg=modifie'); exit;
}

// ============================================================
//  ENREGISTREMENT (ajouter / modifier)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sauvegarder'])) {
    $data = [
        'nom'         => trim($_POST['nom'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'categorie'   => $_POST['categorie'] ?? 'naturel',
        'latitude'    => $_POST['latitude']  !== '' ? (float)$_POST['latitude']  : null,
        'longitude'   => $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null,
        'adresse'     => trim($_POST['adresse'] ?? ''),
        'image_url'   => trim($_POST['image_url'] ?? ''),
        'patrimoine'  => isset($_POST['patrimoine']) ? 1 : 0,
        'actif'       => isset($_POST['actif'])       ? 1 : 0,
    ];

    if (empty($data['nom'])) {
        $msg = '<div class="alert alert-error">⚠️ Le nom du site est obligatoire.</div>';
    } else {
        $site_id = (int)($_POST['site_id'] ?? 0);

        if ($site_id) {
            // MODIFIER
            $sql = "UPDATE sites SET nom=?, description=?, categorie=?, latitude=?, longitude=?,
                    adresse=?, image_url=?, patrimoine=?, actif=? WHERE id=?";
            $pdo->prepare($sql)->execute([
                $data['nom'], $data['description'], $data['categorie'],
                $data['latitude'], $data['longitude'], $data['adresse'],
                $data['image_url'], $data['patrimoine'], $data['actif'], $site_id
            ]);
            $msg = 'modifie';
        } else {
            // AJOUTER
            $sql = "INSERT INTO sites (nom, description, categorie, latitude, longitude, adresse, image_url, patrimoine, actif)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([
                $data['nom'], $data['description'], $data['categorie'],
                $data['latitude'], $data['longitude'], $data['adresse'],
                $data['image_url'], $data['patrimoine'], $data['actif']
            ]);
            $site_id = $pdo->lastInsertId();
            $msg = 'ajoute';
        }
        header("Location: sites.php?msg=$msg"); exit;
    }
}

// ============================================================
//  FORMULAIRE (ajouter / modifier)
// ============================================================
if ($action === 'nouveau' || $action === 'modifier') {
    $page_title = $action === 'nouveau' ? 'Nouveau site' : 'Modifier un site';
    $site = [];
    if ($action === 'modifier' && $id) {
        $site = $pdo->prepare("SELECT * FROM sites WHERE id = ?")->execute([$id]) ? null : null;
        $stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
        $stmt->execute([$id]);
        $site = $stmt->fetch();
        if (!$site) { header('Location: sites.php'); exit; }
    }

    include 'includes/admin_header.php';
    ?>

    <div style="max-width:800px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <a href="sites.php" class="btn btn-ghost btn-sm">← Retour</a>
            <h2 style="font-size:1.1rem;color:var(--text);">
                <?= $action==='nouveau' ? '➕ Ajouter un nouveau site' : '✏️ Modifier : '.htmlspecialchars($site['nom']) ?>
            </h2>
        </div>

        <?= $msg ?>

        <?php if ($action === 'modifier' && $site): ?>
        <!-- Aperçu image actuelle -->
        <?php if (!empty($site['image_url'])): ?>
        <div style="margin-bottom:20px;padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:8px;">
            <div style="font-size:0.75rem;color:var(--text-dim);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px;">Image actuelle</div>
            <img src="<?= htmlspecialchars($site['image_url']) ?>" alt="Aperçu"
                 style="max-height:180px;border-radius:6px;border:1px solid var(--border);"
                 onerror="this.src='';this.alt='Image non disponible'">
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <form method="POST" class="table-card" style="padding:24px;">
            <input type="hidden" name="site_id" value="<?= $site['id'] ?? '' ?>">

            <div class="form-grid cols2">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Nom du site *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($site['nom'] ?? '') ?>" placeholder="Ex: Lac Assal" required>
                </div>

                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie">
                        <?php foreach (['naturel'=>'🌋 Naturel','culturel'=>'🕌 Culturel','historique'=>'🏛️ Historique','gastronomique'=>'🍽️ Gastronomique'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= ($site['categorie'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Adresse / Région</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($site['adresse'] ?? '') ?>" placeholder="Ex: Région Tadjourah">
                </div>

                <div class="form-group">
                    <label>Latitude GPS</label>
                    <input type="number" name="latitude" step="0.0000001"
                           value="<?= $site['latitude'] ?? '' ?>" placeholder="11.6553">
                    <div class="help">Exemple Lac Assal : 11.6553</div>
                </div>

                <div class="form-group">
                    <label>Longitude GPS</label>
                    <input type="number" name="longitude" step="0.0000001"
                           value="<?= $site['longitude'] ?? '' ?>" placeholder="42.4150">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Décrivez ce site touristique…"><?= htmlspecialchars($site['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>URL de l'image (Unsplash ou autre)</label>
                    <input type="url" name="image_url"
                           value="<?= htmlspecialchars($site['image_url'] ?? '') ?>"
                           placeholder="https://images.unsplash.com/photo-…?w=800&q=80"
                           id="img-url-input" oninput="previewImg(this.value)">
                    <div class="help">Copiez une URL d'image depuis <a href="https://unsplash.com" target="_blank" style="color:var(--or);">unsplash.com</a> (gratuit)</div>
                    <img id="img-preview" src="" alt="" style="max-height:140px;margin-top:10px;border-radius:4px;border:1px solid var(--border);display:none;">
                </div>
            </div>

            <div style="display:flex;gap:24px;margin:16px 0;">
                <div class="form-check">
                    <input type="checkbox" name="patrimoine" id="patrimoine" value="1" <?= ($site['patrimoine'] ?? 0) ? 'checked' : '' ?>>
                    <label for="patrimoine">⭐ Site du patrimoine</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="actif" id="actif" value="1" <?= ($site['actif'] ?? 1) ? 'checked' : '' ?>>
                    <label for="actif">✅ Site visible sur le site public</label>
                </div>
            </div>

            <div style="display:flex;gap:10px;padding-top:16px;border-top:1px solid var(--border);">
                <button type="submit" name="sauvegarder" class="btn btn-success">
                    <?= $action==='nouveau' ? '✅ Créer le site' : '💾 Enregistrer les modifications' ?>
                </button>
                <a href="sites.php" class="btn btn-ghost">Annuler</a>
                <?php if ($action === 'modifier' && $site): ?>
                <a href="/tourisme_djibouti/site_detail.php?id=<?= $site['id'] ?>" target="_blank" class="btn btn-ghost" style="margin-left:auto;">
                    🌐 Voir la fiche publique
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
    function previewImg(url) {
        var img = document.getElementById('img-preview');
        if (url) {
            img.src = url;
            img.style.display = 'block';
            img.onerror = function(){ this.style.display='none'; };
        } else {
            img.style.display = 'none';
        }
    }
    // Aperçu au chargement si une URL est déjà renseignée
    var existingUrl = document.getElementById('img-url-input').value;
    if (existingUrl) previewImg(existingUrl);
    </script>

    <?php include 'includes/admin_footer.php';
    exit;
}

// ============================================================
//  LISTE DES SITES
// ============================================================
$page_title = 'Gérer les sites';

// Message de confirmation
if (isset($_GET['msg'])) {
    $msgs = ['ajoute'=>'✅ Site ajouté avec succès et visible sur le site public !','modifie'=>'💾 Site modifié avec succès.','supprime'=>'🗑️ Site supprimé.'];
    $msg = '<div class="alert '.($_GET['msg']==='supprime'?'alert-error':'alert-success').'">'.$msgs[$_GET['msg']].'</div>';
}

// Filtre
$cat_filtre = $_GET['cat'] ?? '';
$where = ''; $params = [];
if ($cat_filtre) { $where = 'WHERE s.categorie = ?'; $params[] = $cat_filtre; }

$sites = $pdo->prepare("
    SELECT s.*,
           ROUND(AVG(a.note),1) AS note_moy,
           COUNT(DISTINCT a.id) AS nb_avis,
           (SELECT COUNT(*) FROM audio_guides WHERE site_id=s.id) AS has_audio,
           (SELECT COUNT(*) FROM fiches_historiques WHERE site_id=s.id) AS has_fiche,
           (SELECT COUNT(*) FROM realite_augmentee WHERE site_id=s.id AND actif=1) AS has_ra
    FROM sites s
    LEFT JOIN avis a ON a.site_id = s.id AND a.valide=1
    $where
    GROUP BY s.id
    ORDER BY s.date_creation DESC
");
$sites->execute($params);
$sites = $sites->fetchAll();

include 'includes/admin_header.php';
?>

<?= $msg ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="sites.php" class="btn btn-ghost btn-sm <?= !$cat_filtre?'btn-primary':'' ?>">Tous (<?= count($sites) ?>)</a>
        <?php foreach(['naturel','culturel','historique','gastronomique'] as $c): ?>
        <a href="sites.php?cat=<?= $c ?>" class="btn btn-ghost btn-sm <?= $cat_filtre===$c?'btn-primary':'' ?>"><?= ucfirst($c) ?></a>
        <?php endforeach; ?>
    </div>
    <a href="sites.php?action=nouveau" class="btn btn-or">+ Nouveau site</a>
</div>

<div class="table-card">
    <div class="table-card-header">
        <h3>🌋 Sites touristiques (<?= count($sites) ?>)</h3>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Catégorie</th>
            <th>GPS</th>
            <th>Contenu</th>
            <th>Note</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($sites as $s): ?>
        <tr>
            <td>
                <?php if (!empty($s['image_url'])): ?>
                <img src="<?= htmlspecialchars($s['image_url']) ?>" class="img-preview"
                     onerror="this.style.display='none'" alt="">
                <?php else: ?>
                <div class="no-img">🖼️</div>
                <?php endif; ?>
            </td>
            <td>
                <div style="font-weight:600;color:var(--text);"><?= htmlspecialchars($s['nom']) ?></div>
                <?php if ($s['patrimoine']): ?>
                <div style="font-size:0.7rem;color:var(--or);margin-top:2px;">⭐ Patrimoine</div>
                <?php endif; ?>
                <div style="font-size:0.72rem;color:var(--text-dim);margin-top:2px;"><?= htmlspecialchars($s['adresse'] ?? '') ?></div>
            </td>
            <td><span class="badge badge-<?= $s['categorie'] ?>"><?= $s['categorie'] ?></span></td>
            <td style="font-family:monospace;font-size:0.75rem;color:var(--text-dim);">
                <?php if ($s['latitude']): ?>
                <?= $s['latitude'] ?><br><?= $s['longitude'] ?>
                <?php else: ?>
                <span style="color:var(--red);">Non défini</span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display:flex;flex-direction:column;gap:3px;font-size:0.72rem;">
                    <span <?= $s['has_fiche'] ? 'style="color:var(--green)"' : 'style="color:var(--text-dim)"' ?>>
                        <?= $s['has_fiche'] ? '✓' : '✗' ?> Fiche
                    </span>
                    <span <?= $s['has_audio'] ? 'style="color:var(--green)"' : 'style="color:var(--text-dim)"' ?>>
                        <?= $s['has_audio'] ? '✓' : '✗' ?> Audio
                    </span>
                    <span <?= $s['has_ra'] ? 'style="color:var(--green)"' : 'style="color:var(--text-dim)"' ?>>
                        <?= $s['has_ra'] ? '✓' : '✗' ?> RA
                    </span>
                </div>
            </td>
            <td>
                <?php if ($s['note_moy']): ?>
                <span style="color:var(--or);font-weight:600;"><?= $s['note_moy'] ?></span>
                <span style="color:var(--text-dim);font-size:0.75rem;"> /5 (<?= $s['nb_avis'] ?>)</span>
                <?php else: ?>
                <span style="color:var(--text-dim);">—</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="badge <?= $s['actif'] ? 'badge-actif' : 'badge-inactif' ?>">
                    <?= $s['actif'] ? 'Visible' : 'Masqué' ?>
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="sites.php?action=modifier&id=<?= $s['id'] ?>" class="btn btn-primary btn-sm" title="Modifier">✏️</a>
                    <a href="sites.php?action=toggle&id=<?= $s['id'] ?>" class="btn btn-ghost btn-sm"
                       title="<?= $s['actif'] ? 'Masquer' : 'Afficher' ?>">
                       <?= $s['actif'] ? '👁️' : '🙈' ?>
                    </a>
                    <a href="/tourisme_djibouti/site_detail.php?id=<?= $s['id'] ?>" target="_blank"
                       class="btn btn-ghost btn-sm" title="Voir sur le site">🌐</a>
                    <button onclick="confirmDelete('sites.php?action=supprimer&id=<?= $s['id'] ?>', '<?= addslashes($s['nom']) ?>')"
                            class="btn btn-danger btn-sm" title="Supprimer">🗑️</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($sites)): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--text-dim);padding:40px;">Aucun site trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>
