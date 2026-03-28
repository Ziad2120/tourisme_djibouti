<?php
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM parcours WHERE id = ? AND actif = 1");
$stmt->execute([$id]);
$parcours = $stmt->fetch();

if (!$parcours) { header('Location: parcours.php'); exit; }

// Sites du parcours
$stmt = $pdo->prepare("
    SELECT s.*, ps.ordre,
           ROUND(AVG(a.note),1) AS note_moy,
           (SELECT COUNT(*) FROM audio_guides WHERE site_id = s.id) AS has_audio,
           (SELECT COUNT(*) FROM realite_augmentee WHERE site_id = s.id AND actif=1) AS has_ra
    FROM parcours_sites ps
    JOIN sites s ON s.id = ps.site_id
    LEFT JOIN avis a ON a.site_id = s.id AND a.valide=1
    WHERE ps.parcours_id = ?
    GROUP BY s.id, ps.ordre
    ORDER BY ps.ordre ASC
");
$stmt->execute([$id]);
$sites = $stmt->fetchAll();

$page_title = $parcours['titre'];
include 'includes/header.php';
?>

<div class="site-hero">
    <div class="site-hero-inner">
        <div class="breadcrumb">
            <a href="index.php">Accueil</a> / <a href="parcours.php">Parcours</a> / <?= htmlspecialchars($parcours['titre']) ?>
        </div>
        <div style="display:inline-block;background:rgba(200,155,60,0.2);border:1px solid rgba(200,155,60,0.4);color:#F0C060;padding:4px 12px;border-radius:3px;font-size:0.72rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;">
            <?= $parcours['theme'] ?>
        </div>
        <h1><?= htmlspecialchars($parcours['titre']) ?></h1>
        <div class="parcours-meta" style="margin-top:14px;">
            <div class="meta-chip">⏱ <?= $parcours['duree_estimee'] ?> min estimés</div>
            <?php if ($parcours['distance_km']): ?>
            <div class="meta-chip">🛣 <?= $parcours['distance_km'] ?> km</div>
            <?php endif; ?>
            <div class="meta-chip">📍 <?= count($sites) ?> sites</div>
            <div class="meta-chip">🎯 <?= ucfirst($parcours['difficulte']) ?></div>
        </div>
    </div>
</div>

<div style="max-width:900px;margin:40px auto;padding:0 40px;">

    <div class="detail-card">
        <div class="detail-card-header">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            Description du parcours
        </div>
        <div class="detail-card-body">
            <p style="line-height:1.8;color:var(--texte-dim);"><?= htmlspecialchars($parcours['description']) ?></p>
        </div>
    </div>

    <!-- Timeline des sites -->
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:var(--ocean-deep);margin:32px 0 20px;">
        Étapes du Parcours
    </h2>

    <div style="position:relative;">
        <!-- Ligne verticale -->
        <div style="position:absolute;left:20px;top:30px;bottom:30px;width:2px;background:linear-gradient(to bottom, var(--ocean), var(--coral));border-radius:2px;"></div>

        <?php $icons = ['naturel'=>'🌋','culturel'=>'🕌','historique'=>'🏛️','gastronomique'=>'🍽️']; ?>
        <?php foreach ($sites as $i => $s): ?>
        <div style="display:flex;gap:24px;margin-bottom:24px;position:relative;">
            <!-- Numéro étape -->
            <div style="width:42px;height:42px;border-radius:50%;background:var(--ocean);color:white;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0;border:3px solid var(--blanc);box-shadow:0 2px 8px var(--ombre);z-index:1;">
                <?= $s['ordre'] ?>
            </div>
            <!-- Carte site -->
            <div class="detail-card" style="flex:1;margin-bottom:0;">
                <div class="detail-card-body">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <div>
                            <span class="card-badge badge-<?= $s['categorie'] ?>" style="margin-bottom:6px;display:inline-block;"><?= ucfirst($s['categorie']) ?></span>
                            <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--ocean-deep);">
                                <?= $icons[$s['categorie']] ?? '📍' ?> <?= htmlspecialchars($s['nom']) ?>
                            </h3>
                        </div>
                        <?php if ($s['note_moy']): ?>
                        <span style="color:var(--or);"><?= str_repeat('★', round($s['note_moy'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size:0.85rem;color:var(--texte-dim);line-height:1.6;margin-bottom:12px;">
                        <?= htmlspecialchars(mb_strimwidth($s['description'], 0, 200, '...')) ?>
                    </p>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <?php if ($s['has_audio']): ?><span class="icon-pill">🎧 Audio</span><?php endif; ?>
                        <?php if ($s['has_ra']): ?><span class="icon-pill">🔮 RA</span><?php endif; ?>
                        <a href="site_detail.php?id=<?= $s['id'] ?>" class="btn btn-ocean btn-sm" style="margin-left:auto;">
                            Découvrir ce site →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:32px;">
        <a href="carte.php" class="btn btn-ocean">📍 Voir sur la carte</a>
        <a href="parcours.php" class="btn btn-outline" style="border-color:var(--ocean);color:var(--ocean);margin-left:12px;">← Tous les parcours</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
