<?php
require_once 'config/db.php';

$page_title = 'Accueil';

// Derniers sites (4)
$stmt = $pdo->query("
    SELECT s.*, 
           ROUND(AVG(a.note),1) AS note_moy,
           COUNT(a.id) AS nb_avis
    FROM sites s
    LEFT JOIN avis a ON a.site_id = s.id AND a.valide = 1
    WHERE s.actif = 1
    GROUP BY s.id
    ORDER BY s.date_creation DESC LIMIT 4
");
$derniers_sites = $stmt->fetchAll();

// Statistiques
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM sites WHERE actif=1) AS nb_sites,
        (SELECT COUNT(*) FROM parcours WHERE actif=1) AS nb_parcours,
        (SELECT COUNT(*) FROM avis WHERE valide=1) AS nb_avis,
        (SELECT COUNT(*) FROM audio_guides) AS nb_audios
")->fetch();

// Parcours vedettes (2)
$parcours = $pdo->query("
    SELECT p.*, COUNT(ps.site_id) AS nb_sites
    FROM parcours p
    LEFT JOIN parcours_sites ps ON ps.parcours_id = p.id
    WHERE p.actif = 1
    GROUP BY p.id
    LIMIT 2
")->fetchAll();

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-badge">
            <span>🌍</span> Valoriser le patrimoine djiboutien
        </div>
        <h1>Découvrez <span>Djibouti</span>,<br>Terre de Contrastes</h1>
        <p>Explorez les merveilles naturelles, le patrimoine culturel et les itinéraires authentiques de la Corne de l'Afrique.</p>
        <div class="hero-btns">
            <a href="sites.php" class="btn btn-primary">Explorer les sites</a>
            <a href="carte.php" class="btn btn-outline">Voir la carte</a>
            <a href="parcours.php" class="btn btn-outline">Nos parcours</a>
        </div>
    </div>
</section>

<!-- STATISTIQUES -->
<div class="stats-bar">
    <div class="stat-item">
        <span class="stat-num"><?= $stats['nb_sites'] ?></span>
        <div class="stat-label">Sites touristiques</div>
    </div>
    <div class="stat-item">
        <span class="stat-num"><?= $stats['nb_parcours'] ?></span>
        <div class="stat-label">Parcours guidés</div>
    </div>
    <div class="stat-item">
        <span class="stat-num"><?= $stats['nb_avis'] ?></span>
        <div class="stat-label">Avis de touristes</div>
    </div>
    <div class="stat-item">
        <span class="stat-num"><?= $stats['nb_audios'] ?></span>
        <div class="stat-label">Audio-guides</div>
    </div>
</div>

<!-- DERNIERS SITES -->
<section class="section">
    <div class="section-header">
        <div class="section-tag">À découvrir</div>
        <h2>Sites <span>Incontournables</span></h2>
        <p>Des paysages volcaniques aux médinas historiques, Djibouti vous surprendra.</p>
    </div>
    <div class="cards-grid">
        <?php foreach ($derniers_sites as $site): ?>
        <div class="card">
            <div class="card-img">
                <?php
                $imgs_def=[1=>'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=600&q=80',2=>'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=600&q=80',3=>'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80',4=>'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?w=600&q=80',5=>'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80',6=>'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&q=80',7=>'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80',8=>'https://images.unsplash.com/photo-1561681173-c2ef9b1dfbe3?w=600&q=80'];
                $src = !empty($site['image_url']) ? $site['image_url'] : ($imgs_def[$site['id']] ?? 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=600&q=80');
                ?>
                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($site['nom']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'" loading="lazy">
                <span class="card-badge badge-<?= $site['categorie'] ?>">
                    <?= ucfirst($site['categorie']) ?>
                </span>
                <?php if ($site['patrimoine']): ?>
                <span style="position:absolute;top:12px;right:12px;background:rgba(200,155,60,0.9);color:white;padding:3px 8px;border-radius:3px;font-size:0.68rem;letter-spacing:1px;">★ PATRIMOINE</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-title"><?= htmlspecialchars($site['nom']) ?></div>
                <div class="card-desc"><?= htmlspecialchars($site['description']) ?></div>
                <div class="card-meta">
                    <div class="card-rating">
                        <?php if ($site['note_moy']): ?>
                        <span class="stars"><?= str_repeat('★', round($site['note_moy'])) ?><?= str_repeat('☆', 5 - round($site['note_moy'])) ?></span>
                        <span class="rating-count"><?= $site['note_moy'] ?> (<?= $site['nb_avis'] ?>)</span>
                        <?php else: ?>
                        <span class="rating-count" style="color:var(--texte-dim)">Pas encore d'avis</span>
                        <?php endif; ?>
                    </div>
                    <a href="site_detail.php?id=<?= $site['id'] ?>" class="btn btn-ocean btn-sm">Voir →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-8">
        <a href="sites.php" class="btn btn-ocean">Voir tous les sites</a>
    </div>
</section>

<!-- PARCOURS -->
<?php if (!empty($parcours)): ?>
<section class="section section-alt">
    <div class="section-header">
        <div class="section-tag">Itinéraires</div>
        <h2>Nos <span>Parcours Guidés</span></h2>
        <p>Des itinéraires soigneusement conçus pour vivre Djibouti comme jamais.</p>
    </div>
    <div class="cards-grid" style="max-width:900px;margin:0 auto;">
        <?php foreach ($parcours as $p): ?>
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
                <a href="parcours_detail.php?id=<?= $p['id'] ?>" class="btn btn-ocean btn-sm">Voir le parcours →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-8">
        <a href="parcours.php" class="btn btn-primary">Tous les parcours</a>
    </div>
</section>
<?php endif; ?>

<!-- FONCTIONNALITÉS -->
<section class="section">
    <div class="section-header">
        <div class="section-tag">Fonctionnalités</div>
        <h2>Une Expérience <span>Interactive</span></h2>
    </div>
    <div class="cards-grid" style="max-width:1000px;margin:0 auto;">
        <div class="card" style="border-top:3px solid var(--ocean);">
            <div class="card-body" style="text-align:center;padding:32px 24px;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🗺️</div>
                <div class="card-title">Carte Interactive</div>
                <div class="card-desc">Localisez tous les sites sur une carte Leaflet avec filtres par catégorie et navigation GPS.</div>
                <a href="carte.php" class="btn btn-ocean btn-sm mt-4">Ouvrir la carte</a>
            </div>
        </div>
        <div class="card" style="border-top:3px solid var(--coral);">
            <div class="card-body" style="text-align:center;padding:32px 24px;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🎧</div>
                <div class="card-title">Fiches Audio</div>
                <div class="card-desc">Écoutez des audio-guides réalisés par des experts locaux pour enrichir votre visite.</div>
                <a href="sites.php" class="btn btn-primary btn-sm mt-4">Écouter</a>
            </div>
        </div>
        <div class="card" style="border-top:3px solid var(--or);">
            <div class="card-body" style="text-align:center;padding:32px 24px;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🔮</div>
                <div class="card-title">Réalité Augmentée</div>
                <div class="card-desc">Superposez des informations visuelles sur votre environnement grâce à la RA simplifiée.</div>
                <a href="sites.php" class="btn btn-sm mt-4" style="background:var(--or);color:var(--texte)">Découvrir</a>
            </div>
        </div>
        <div class="card" style="border-top:3px solid var(--ocean-mid);">
            <div class="card-body" style="text-align:center;padding:32px 24px;">
                <div style="font-size:2.5rem;margin-bottom:12px;">⭐</div>
                <div class="card-title">Avis Touristes</div>
                <div class="card-desc">Consultez et partagez vos expériences pour aider la communauté des voyageurs.</div>
                <a href="avis.php" class="btn btn-ocean btn-sm mt-4">Voir les avis</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
