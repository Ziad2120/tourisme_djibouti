<?php
require_once 'includes/auth.php';
require_once '../config/db.php';
requiertAdmin();
$page_title = 'Dashboard';

// Statistiques
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM sites WHERE actif=1) AS sites_actifs,
        (SELECT COUNT(*) FROM sites) AS sites_total,
        (SELECT COUNT(*) FROM parcours WHERE actif=1) AS parcours_actifs,
        (SELECT COUNT(*) FROM avis WHERE valide=1) AS avis_total,
        (SELECT ROUND(AVG(note),1) FROM avis WHERE valide=1) AS note_moy,
        (SELECT COUNT(*) FROM audio_guides) AS nb_audios,
        (SELECT COUNT(*) FROM fiches_historiques) AS nb_fiches,
        (SELECT COUNT(*) FROM realite_augmentee WHERE actif=1) AS nb_ra
")->fetch();

// Derniers sites ajoutés
$derniers_sites = $pdo->query("
    SELECT s.*, ROUND(AVG(a.note),1) AS note_moy, COUNT(a.id) AS nb_avis
    FROM sites s
    LEFT JOIN avis a ON a.site_id = s.id
    GROUP BY s.id ORDER BY s.date_creation DESC LIMIT 5
")->fetchAll();

// Derniers avis
$derniers_avis = $pdo->query("
    SELECT a.*, s.nom AS site_nom
    FROM avis a JOIN sites s ON s.id = a.site_id
    WHERE a.valide=1 ORDER BY a.date_avis DESC LIMIT 5
")->fetchAll();

// Répartition par catégorie
$categories = $pdo->query("
    SELECT categorie, COUNT(*) AS nb FROM sites WHERE actif=1 GROUP BY categorie
")->fetchAll();

include 'includes/admin_header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">🌋</div>
        <div class="stat-num"><?= $stats['sites_actifs'] ?></div>
        <div class="stat-label">Sites actifs</div>
        <div style="font-size:0.72rem;color:var(--text-dim);margin-top:4px;"><?= $stats['sites_total'] ?> au total</div>
    </div>
    <div class="stat-card coral">
        <div class="stat-icon">🗺️</div>
        <div class="stat-num"><?= $stats['parcours_actifs'] ?></div>
        <div class="stat-label">Parcours actifs</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon">⭐</div>
        <div class="stat-num"><?= $stats['note_moy'] ?? '—' ?></div>
        <div class="stat-label">Note moyenne</div>
        <div style="font-size:0.72rem;color:var(--text-dim);margin-top:4px;"><?= $stats['avis_total'] ?> avis</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">🎧</div>
        <div class="stat-num"><?= $stats['nb_audios'] ?></div>
        <div class="stat-label">Audio-guides</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">📄</div>
        <div class="stat-num"><?= $stats['nb_fiches'] ?></div>
        <div class="stat-label">Fiches historiques</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon">🔮</div>
        <div class="stat-num"><?= $stats['nb_ra'] ?></div>
        <div class="stat-label">Expériences RA</div>
    </div>
</div>

<!-- Actions rapides -->
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;">
    <a href="sites.php?action=nouveau" class="btn btn-or">+ Nouveau site</a>
    <a href="parcours.php?action=nouveau" class="btn btn-primary">+ Nouveau parcours</a>
    <a href="avis.php" class="btn btn-ghost">📋 Modérer les avis</a>
    <a href="/tourisme_djibouti/index.php" target="_blank" class="btn btn-ghost">🌐 Voir le site public</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:flex-start;">

    <!-- Derniers sites -->
    <div class="table-card">
        <div class="table-card-header">
            <h3>🌋 Sites récents</h3>
            <a href="sites.php" class="btn btn-ghost btn-sm">Voir tout →</a>
        </div>
        <table class="admin-table">
            <thead><tr>
                <th>Site</th><th>Catégorie</th><th>Note</th><th>Statut</th>
            </tr></thead>
            <tbody>
            <?php foreach ($derniers_sites as $s): ?>
            <tr>
                <td>
                    <a href="sites.php?action=modifier&id=<?= $s['id'] ?>" style="color:var(--or);text-decoration:none;font-weight:500;">
                        <?= htmlspecialchars($s['nom']) ?>
                    </a>
                </td>
                <td><span class="badge badge-<?= $s['categorie'] ?>"><?= $s['categorie'] ?></span></td>
                <td>
                    <?php if ($s['note_moy']): ?>
                    <span style="color:var(--or);"><?= $s['note_moy'] ?></span>
                    <span style="color:var(--text-dim);font-size:0.75rem;"> (<?= $s['nb_avis'] ?>)</span>
                    <?php else: ?>
                    <span style="color:var(--text-dim);">—</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge <?= $s['actif'] ? 'badge-actif' : 'badge-inactif' ?>"><?= $s['actif'] ? 'Actif' : 'Masqué' ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Derniers avis -->
    <div class="table-card">
        <div class="table-card-header">
            <h3>⭐ Derniers avis</h3>
            <a href="avis.php" class="btn btn-ghost btn-sm">Gérer →</a>
        </div>
        <table class="admin-table">
            <thead><tr>
                <th>Visiteur</th><th>Site</th><th>Note</th>
            </tr></thead>
            <tbody>
            <?php foreach ($derniers_avis as $a): ?>
            <tr>
                <td style="font-weight:500;"><?= htmlspecialchars($a['nom_visiteur'] ?? 'Anonyme') ?></td>
                <td style="color:var(--text-dim);font-size:0.8rem;"><?= htmlspecialchars($a['site_nom']) ?></td>
                <td style="color:var(--or);"><?= str_repeat('★',$a['note']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Répartition catégories -->
<div class="table-card" style="margin-top:24px;">
    <div class="table-card-header">
        <h3>📊 Répartition par catégorie</h3>
    </div>
    <div style="padding:20px;display:flex;gap:20px;flex-wrap:wrap;">
        <?php
        $cat_colors = ['naturel'=>'#1E6B3A','culturel'=>'#7B3FA0','historique'=>'#8B5000','gastronomique'=>'#A0300A'];
        $total_sites = $stats['sites_actifs'] ?: 1;
        foreach ($categories as $c):
            $pct = round(($c['nb'] / $total_sites) * 100);
        ?>
        <div style="flex:1;min-width:140px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:0.82rem;">
                <span style="color:var(--text);"><?= ucfirst($c['categorie']) ?></span>
                <span style="color:var(--text-dim);"><?= $c['nb'] ?> site<?= $c['nb']>1?'s':'' ?></span>
            </div>
            <div style="height:8px;background:var(--surface2);border-radius:4px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:<?= $cat_colors[$c['categorie']] ?? '#1A5276' ?>;border-radius:4px;"></div>
            </div>
            <div style="font-size:0.72rem;color:var(--text-dim);margin-top:4px;"><?= $pct ?>%</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
