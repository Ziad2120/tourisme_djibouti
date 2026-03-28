<?php
require_once 'config/db.php';
$page_title = 'Recherche';

$q = trim($_GET['q'] ?? '');
$resultats_sites    = [];
$resultats_parcours = [];

if (strlen($q) >= 2) {
    $like = "%" . $q . "%";

    // ── Recherche dans les sites ──────────────────────────
    $stmt = $pdo->prepare("
        SELECT s.*,
               ROUND(AVG(a.note),1) AS note_moy,
               COUNT(DISTINCT a.id) AS nb_avis
        FROM sites s
        LEFT JOIN avis a ON a.site_id = s.id AND a.valide = 1
        WHERE s.actif = 1
          AND (s.nom LIKE ? OR s.description LIKE ? OR s.adresse LIKE ?)
        GROUP BY s.id
        ORDER BY note_moy DESC
    ");
    $stmt->execute([$like, $like, $like]);
    $resultats_sites = $stmt->fetchAll();

    // ── Recherche dans les parcours ───────────────────────
    $stmt = $pdo->prepare("
        SELECT p.*,
               COUNT(DISTINCT ps.site_id) AS nb_sites
        FROM parcours p
        LEFT JOIN parcours_sites ps ON ps.parcours_id = p.id
        WHERE p.actif = 1
          AND (p.titre LIKE ? OR p.description LIKE ?)
        GROUP BY p.id
        ORDER BY p.date_creation DESC
    ");
    $stmt->execute([$like, $like]);
    $resultats_parcours = $stmt->fetchAll();
}

$total = count($resultats_sites) + count($resultats_parcours);

// Images par défaut
$imgs_def = [
    1=>'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=600&q=80',
    2=>'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=600&q=80',
    3=>'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80',
    4=>'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?w=600&q=80',
    5=>'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80',
    6=>'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&q=80',
    7=>'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80',
    8=>'https://images.unsplash.com/photo-1561681173-c2ef9b1dfbe3?w=600&q=80',
];

include 'includes/header.php';
?>

<div class="search-hero">
    <h1>Rechercher</h1>
    <form method="GET">
        <div class="search-box">
            <input type="text" name="q"
                   value="<?= htmlspecialchars($q) ?>"
                   placeholder="Lac Assal, mosquée, nature..."
                   autofocus>
            <button type="submit">Rechercher</button>
        </div>
    </form>
    <?php if ($q): ?>
    <p style="margin-top:14px;color:rgba(255,255,255,0.6);font-size:0.88rem;">
        <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?> pour « <?= htmlspecialchars($q) ?> »
    </p>
    <?php endif; ?>
</div>

<div class="content-area">

<?php if ($q && $total === 0): ?>
    <!-- Aucun résultat -->
    <div class="no-results">
        <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
        <p>Aucun résultat pour « <strong><?= htmlspecialchars($q) ?></strong> »</p>
        <a href="sites.php" style="color:var(--ocean);margin-top:16px;display:inline-block;">
            Parcourir tous les sites →
        </a>
    </div>

<?php elseif ($total > 0): ?>

    <!-- ── SITES ── -->
    <?php if (!empty($resultats_sites)): ?>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;color:var(--ocean-deep);margin-bottom:16px;">
        🌋 Sites touristiques (<?= count($resultats_sites) ?>)
    </h2>
    <div class="cards-grid" style="margin-bottom:40px;">
        <?php foreach ($resultats_sites as $r):
            $src = !empty($r['image_url']) ? $r['image_url'] : ($imgs_def[$r['id']] ?? $imgs_def[1]);
            $cat_icon = ['naturel'=>'🌋','culturel'=>'🕌','historique'=>'🏛️','gastronomique'=>'🍽️'][$r['categorie']] ?? '📍';
        ?>
        <div class="card">
            <div class="card-img">
                <img src="<?= htmlspecialchars($src) ?>"
                     alt="<?= htmlspecialchars($r['nom']) ?>"
                     style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;"
                     loading="lazy" onerror="this.style.display='none'">
                <span class="img-placeholder" style="position:relative;z-index:1;"><?= $cat_icon ?></span>
                <span class="card-badge badge-<?= $r['categorie'] ?>"><?= ucfirst($r['categorie']) ?></span>
            </div>
            <div class="card-body">
                <div class="card-title"><?= htmlspecialchars($r['nom']) ?></div>
                <div class="card-desc"><?= htmlspecialchars($r['description']) ?></div>
                <div class="card-meta">
                    <div class="card-rating">
                        <?php if ($r['note_moy']): ?>
                        <span class="stars"><?= str_repeat('★', (int)round($r['note_moy'])) ?><?= str_repeat('☆', 5 - (int)round($r['note_moy'])) ?></span>
                        <span class="rating-count"><?= $r['note_moy'] ?>/5</span>
                        <?php else: ?>
                        <span class="rating-count">Pas d'avis</span>
                        <?php endif; ?>
                    </div>
                    <a href="site_detail.php?id=<?= $r['id'] ?>" class="btn btn-ocean btn-sm">Voir →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── PARCOURS ── -->
    <?php if (!empty($resultats_parcours)): ?>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;color:var(--ocean-deep);margin-bottom:16px;">
        🗺️ Parcours (<?= count($resultats_parcours) ?>)
    </h2>
    <div class="cards-grid" style="grid-template-columns:repeat(auto-fill,minmax(340px,1fr));">
        <?php foreach ($resultats_parcours as $p): ?>
        <div class="parcours-card">
            <div class="parcours-card-header">
                <div class="parcours-theme"><?= strtoupper($p['theme']) ?></div>
                <div class="parcours-title"><?= htmlspecialchars($p['titre']) ?></div>
                <div class="parcours-meta">
                    <div class="meta-chip">⏱ <?= $p['duree_estimee'] ?> min</div>
                    <div class="meta-chip">📍 <?= $p['nb_sites'] ?> sites</div>
                    <div class="meta-chip">🎯 <?= ucfirst($p['difficulte']) ?></div>
                </div>
            </div>
            <div class="parcours-body">
                <div class="parcours-desc"><?= htmlspecialchars($p['description']) ?></div>
                <a href="parcours_detail.php?id=<?= $p['id'] ?>" class="btn btn-ocean btn-sm">
                    Voir le parcours →
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Page vide — suggestions -->
    <div style="text-align:center;padding:40px 0;">
        <p style="color:var(--texte-dim);margin-bottom:20px;">Suggestions :</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
            <?php foreach (['Lac Assal','Mosquée','Forêt','Plage','Tadjourah','Flamants roses'] as $sug): ?>
            <a href="recherche.php?q=<?= urlencode($sug) ?>" class="filter-btn"><?= $sug ?></a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
