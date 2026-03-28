<?php
require_once 'config/db.php';
$page_title = 'Avis touristes';

// Soumission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom  = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $site_id = (int)($_POST['site_id'] ?? 0);
    $note = (int)($_POST['note'] ?? 0);
    $commentaire = trim(htmlspecialchars($_POST['commentaire'] ?? ''));

    if (!$nom || !$site_id || $note < 1 || $note > 5 || !$commentaire) {
        $msg = '<div class="alert alert-error">⚠️ Tous les champs sont obligatoires.</div>';
    } else {
        $ins = $pdo->prepare("INSERT INTO avis (site_id, nom_visiteur, note, commentaire) VALUES (?,?,?,?)");
        $ins->execute([$site_id, $nom, $note, $commentaire]);
        $msg = '<div class="alert alert-success">✅ Votre avis a été publié avec succès !</div>';
    }
}

// Filtre par site
$site_filtre = isset($_GET['site']) ? (int)$_GET['site'] : 0;
$where = "WHERE a.valide = 1";
$params = [];
if ($site_filtre) {
    $where .= " AND a.site_id = ?";
    $params[] = $site_filtre;
}

$stmt = $pdo->prepare("
    SELECT a.*, s.nom AS site_nom, s.categorie
    FROM avis a
    JOIN sites s ON s.id = a.site_id
    $where
    ORDER BY a.date_avis DESC
");
$stmt->execute($params);
$avis_list = $stmt->fetchAll();

// Statistiques globales
$stats = $pdo->query("
    SELECT 
        COUNT(*) AS total,
        ROUND(AVG(note),1) AS moy,
        SUM(note=5) AS nb5,
        SUM(note=4) AS nb4,
        SUM(note=3) AS nb3,
        SUM(note<=2) AS nb2
    FROM avis WHERE valide=1
")->fetch();

// Liste sites pour formulaire
$sites_list = $pdo->query("SELECT id, nom, categorie FROM sites WHERE actif=1 ORDER BY nom")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Avis des Touristes</h1>
    <p>Ce que pensent les visiteurs de Djibouti</p>
</div>

<div class="content-area" style="max-width:960px;">

    <!-- Stats globales -->
    <?php if ($stats['total'] > 0): ?>
    <div style="background:linear-gradient(135deg,var(--ocean-deep),var(--ocean));color:white;border-radius:8px;padding:28px;margin-bottom:32px;display:flex;gap:32px;flex-wrap:wrap;align-items:center;">
        <div style="text-align:center;">
            <div style="font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:600;color:var(--or);line-height:1;"><?= $stats['moy'] ?></div>
            <div style="font-size:1.3rem;color:var(--or);margin:4px 0;"><?= str_repeat('★', round($stats['moy'])) ?></div>
            <div style="font-size:0.8rem;color:rgba(255,255,255,0.6);"><?= $stats['total'] ?> avis</div>
        </div>
        <div style="flex:1;min-width:200px;">
            <?php
            $bars = [5=>$stats['nb5'], 4=>$stats['nb4'], 3=>$stats['nb3'], 2=>$stats['nb2']];
            foreach ($bars as $n => $count):
                $pct = $stats['total'] > 0 ? round(($count/$stats['total'])*100) : 0;
            ?>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;font-size:0.82rem;">
                <span style="color:var(--or);width:60px;"><?= str_repeat('★',$n) ?></span>
                <div style="flex:1;background:rgba(255,255,255,0.15);border-radius:2px;height:8px;">
                    <div style="width:<?= $pct ?>%;background:var(--or);height:100%;border-radius:2px;transition:width 0.5s;"></div>
                </div>
                <span style="color:rgba(255,255,255,0.6);width:30px;text-align:right;"><?= $count ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:flex-start;">

        <!-- Liste des avis -->
        <div>
            <!-- Filtre par site -->
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
                <a href="avis.php" class="filter-btn <?= !$site_filtre ? 'active' : '' ?>">Tous les sites</a>
                <?php foreach ($sites_list as $s): ?>
                <a href="avis.php?site=<?= $s['id'] ?>" class="filter-btn <?= $site_filtre == $s['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($s['nom']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($avis_list)): ?>
                <div class="no-results">Aucun avis pour l'instant. Soyez le premier !</div>
            <?php else: ?>
            <div class="avis-list">
                <?php foreach ($avis_list as $a): ?>
                <div class="avis-card">
                    <div class="avis-header">
                        <span class="avis-author">👤 <?= htmlspecialchars($a['nom_visiteur'] ?? 'Anonyme') ?></span>
                        <span class="avis-date"><?= date('d/m/Y', strtotime($a['date_avis'])) ?></span>
                    </div>
                    <div class="avis-stars"><?= str_repeat('★', $a['note']) ?><?= str_repeat('☆', 5-$a['note']) ?></div>
                    <div class="avis-text"><?= nl2br(htmlspecialchars($a['commentaire'])) ?></div>
                    <div class="avis-site">
                        Sur : <a href="site_detail.php?id=<?= $a['site_id'] ?>" style="color:var(--ocean);">
                            <?= htmlspecialchars($a['site_nom']) ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Formulaire -->
        <div style="position:sticky;top:80px;">
            <div class="avis-form-card">
                <h3>Partager votre expérience</h3>
                <?= $msg ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Site visité</label>
                        <select name="site_id" required>
                            <option value="">— Choisir un site —</option>
                            <?php foreach ($sites_list as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($site_filtre==$s['id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($s['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Votre nom</label>
                        <input type="text" name="nom" placeholder="Prénom Nom" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <div class="star-rating">
                            <input type="radio" name="note" id="r5" value="5"><label for="r5">★</label>
                            <input type="radio" name="note" id="r4" value="4"><label for="r4">★</label>
                            <input type="radio" name="note" id="r3" value="3"><label for="r3">★</label>
                            <input type="radio" name="note" id="r2" value="2"><label for="r2">★</label>
                            <input type="radio" name="note" id="r1" value="1"><label for="r1">★</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Votre avis</label>
                        <textarea name="commentaire" rows="4" placeholder="Décrivez votre visite..." required maxlength="500" style="resize:vertical;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Publier l'avis ★
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
