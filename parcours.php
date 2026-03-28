<?php
require_once 'config/db.php';
$page_title = 'Parcours';

$stmt = $pdo->query("
    SELECT p.*,
           COUNT(DISTINCT ps.site_id) AS nb_sites,
           GROUP_CONCAT(s.nom ORDER BY ps.ordre SEPARATOR '||') AS sites_noms,
           GROUP_CONCAT(s.categorie ORDER BY ps.ordre SEPARATOR '||') AS sites_cats
    FROM parcours p
    LEFT JOIN parcours_sites ps ON ps.parcours_id = p.id
    LEFT JOIN sites s ON s.id = ps.site_id
    WHERE p.actif = 1
    GROUP BY p.id
    ORDER BY p.date_creation DESC
");
$parcours_list = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Parcours Guidés</h1>
    <p>Itinéraires soigneusement conçus pour explorer Djibouti</p>
</div>

<div class="content-area">
    <div class="cards-grid" style="grid-template-columns: repeat(auto-fill, minmax(340px,1fr));">
        <?php foreach ($parcours_list as $p):
            $sites_noms = $p['sites_noms'] ? explode('||', $p['sites_noms']) : [];
            $sites_cats = $p['sites_cats'] ? explode('||', $p['sites_cats']) : [];
            $icons = ['naturel'=>'🌋','culturel'=>'🕌','historique'=>'🏛️','gastronomique'=>'🍽️'];
        ?>
        <div class="parcours-card">
            <div class="parcours-card-header">
                <div class="parcours-theme">
                    <?= strtoupper($p['theme']) ?>
                    <span style="margin-left:10px;padding:2px 8px;background:rgba(255,255,255,0.1);border-radius:2px;font-size:0.68rem;">
                        <?= strtoupper($p['difficulte']) ?>
                    </span>
                </div>
                <div class="parcours-title"><?= htmlspecialchars($p['titre']) ?></div>
                <div class="parcours-meta">
                    <div class="meta-chip">⏱ <?= $p['duree_estimee'] > 60 ? floor($p['duree_estimee']/60).'h'.($p['duree_estimee']%60 > 0 ? ($p['duree_estimee']%60).'min' : '') : $p['duree_estimee'].' min' ?></div>
                    <?php if ($p['distance_km']): ?>
                    <div class="meta-chip">🛣 <?= $p['distance_km'] ?> km</div>
                    <?php endif; ?>
                    <div class="meta-chip">📍 <?= $p['nb_sites'] ?> sites</div>
                </div>
            </div>
            <div class="parcours-body">
                <div class="parcours-desc"><?= htmlspecialchars($p['description']) ?></div>

                <?php if (!empty($sites_noms)): ?>
                <div class="parcours-sites-list">
                    <h4>Étapes du parcours</h4>
                    <?php foreach ($sites_noms as $i => $nom): ?>
                    <div class="parcours-site-item">
                        <div class="step-num"><?= $i+1 ?></div>
                        <span><?= $icons[$sites_cats[$i] ?? ''] ?? '📍' ?></span>
                        <span><?= htmlspecialchars($nom) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div style="margin-top:16px;">
                    <a href="parcours_detail.php?id=<?= $p['id'] ?>" class="btn btn-ocean btn-sm">
                        Voir le parcours complet →
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
