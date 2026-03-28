<?php
require_once 'config/db.php';
$page_title = 'Sites touristiques';

$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$cats_valides = ['naturel','culturel','historique','gastronomique'];

$where = "WHERE s.actif = 1";
$params = [];
if ($cat && in_array($cat, $cats_valides)) {
    $where .= " AND s.categorie = ?";
    $params[] = $cat;
}

$stmt = $pdo->prepare("
    SELECT s.*, s.image_url,
           ROUND(AVG(a.note),1) AS note_moy,
           COUNT(DISTINCT a.id) AS nb_avis,
           (SELECT COUNT(*) FROM audio_guides WHERE site_id = s.id) AS has_audio,
           (SELECT COUNT(*) FROM realite_augmentee WHERE site_id = s.id AND actif=1) AS has_ra
    FROM sites s
    LEFT JOIN avis a ON a.site_id = s.id AND a.valide = 1
    $where
    GROUP BY s.id
    ORDER BY s.patrimoine DESC, s.nom ASC
");
$stmt->execute($params);
$sites = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Sites Touristiques</h1>
    <p><?= count($sites) ?> site<?= count($sites) > 1 ? 's' : '' ?> disponible<?= count($sites) > 1 ? 's' : '' ?></p>
</div>

<div class="filters">
    <a href="sites.php" class="filter-btn <?= !$cat ? 'active' : '' ?>">Tous</a>
    <a href="sites.php?cat=naturel"       class="filter-btn <?= $cat==='naturel' ? 'active' : '' ?>">🌋 Naturel</a>
    <a href="sites.php?cat=culturel"      class="filter-btn <?= $cat==='culturel' ? 'active' : '' ?>">🕌 Culturel</a>
    <a href="sites.php?cat=historique"    class="filter-btn <?= $cat==='historique' ? 'active' : '' ?>">🏛️ Historique</a>
    <a href="sites.php?cat=gastronomique" class="filter-btn <?= $cat==='gastronomique' ? 'active' : '' ?>">🍽️ Gastronomique</a>
</div>

<div class="content-area">
    <?php if (empty($sites)): ?>
        <div class="no-results">Aucun site trouvé pour cette catégorie.</div>
    <?php else: ?>
    <div class="cards-grid">
        <?php foreach ($sites as $s): ?>
        <div class="card">
            <div class="card-img">
                <?php $imgs_def=[1=>'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=600&q=80',2=>'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=600&q=80',3=>'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80',4=>'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?w=600&q=80',5=>'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80',6=>'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&q=80',7=>'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80',8=>'https://images.unsplash.com/photo-1561681173-c2ef9b1dfbe3?w=600&q=80'];
                $src_s = !empty($s['image_url']) ? $s['image_url'] : ($imgs_def[$s['id']] ?? 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=600&q=80'); ?>
                <img src="<?= htmlspecialchars($src_s) ?>" alt="<?= htmlspecialchars($s['nom']) ?>" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;" loading="lazy" onerror="this.style.display='none'">
                <span class="img-placeholder">
                    <?= $s['categorie']==='naturel' ? '🌋' : ($s['categorie']==='culturel' ? '🕌' : ($s['categorie']==='historique' ? '🏛️' : '🍽️')) ?>
                </span>
                <span class="card-badge badge-<?= $s['categorie'] ?>"><?= ucfirst($s['categorie']) ?></span>
                <?php if ($s['patrimoine']): ?>
                <span style="position:absolute;top:12px;right:12px;background:rgba(200,155,60,0.9);color:white;padding:3px 8px;border-radius:3px;font-size:0.68rem;letter-spacing:1px;">★ PATRIMOINE</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-title"><?= htmlspecialchars($s['nom']) ?></div>
                <div class="card-desc"><?= htmlspecialchars($s['description']) ?></div>

                <div class="card-icons mb-4" style="margin-bottom:12px;">
                    <?php if ($s['has_audio']): ?>
                    <span class="icon-pill">🎧 Audio</span>
                    <?php endif; ?>
                    <?php if ($s['has_ra']): ?>
                    <span class="icon-pill">🔮 RA</span>
                    <?php endif; ?>
                    <?php if ($s['adresse']): ?>
                    <span class="icon-pill">📍 <?= htmlspecialchars(explode(',', $s['adresse'])[0]) ?></span>
                    <?php endif; ?>
                </div>

                <div class="card-meta">
                    <div class="card-rating">
                        <?php if ($s['note_moy']): ?>
                        <span class="stars"><?= str_repeat('★', round($s['note_moy'])) ?><?= str_repeat('☆', 5-round($s['note_moy'])) ?></span>
                        <span class="rating-count"><?= $s['note_moy'] ?> (<?= $s['nb_avis'] ?>)</span>
                        <?php else: ?>
                        <span class="rating-count">Pas d'avis</span>
                        <?php endif; ?>
                    </div>
                    <a href="site_detail.php?id=<?= $s['id'] ?>" class="btn btn-ocean btn-sm">Voir →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
