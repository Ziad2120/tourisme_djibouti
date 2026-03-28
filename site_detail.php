<?php
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ? AND actif = 1");
$stmt->execute([$id]);
$site = $stmt->fetch();
if (!$site) { header('Location: sites.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM fiches_historiques WHERE site_id = ? AND langue='fr' LIMIT 1");
$stmt->execute([$id]);
$fiche = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM audio_guides WHERE site_id = ? AND langue='fr' LIMIT 1");
$stmt->execute([$id]);
$audio = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM realite_augmentee WHERE site_id = ? AND actif=1 LIMIT 1");
$stmt->execute([$id]);
$ra = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM avis WHERE site_id = ? AND valide = 1 ORDER BY date_avis DESC");
$stmt->execute([$id]);
$avis_list = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT ROUND(AVG(note),1) AS moy, COUNT(*) AS nb FROM avis WHERE site_id=? AND valide=1");
$stmt->execute([$id]);
$note_info = $stmt->fetch();

// Soumission avis
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['soumettre_avis'])) {
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $note = (int)($_POST['note'] ?? 0);
    $commentaire = trim(htmlspecialchars($_POST['commentaire'] ?? ''));
    if (!$nom || $note < 1 || $note > 5 || !$commentaire) {
        $msg = '<div class="alert alert-error">⚠️ Veuillez remplir tous les champs et donner une note.</div>';
    } else {
        $pdo->prepare("INSERT INTO avis (site_id, nom_visiteur, note, commentaire) VALUES (?,?,?,?)")
            ->execute([$id, $nom, $note, $commentaire]);
        header("Location: site_detail.php?id=$id&ok=1"); exit;
    }
}
if (isset($_GET['ok'])) $msg = '<div class="alert alert-success">✅ Votre avis a été publié. Merci !</div>';

// Soumission réservation
$msg_reservation = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['soumettre_reservation'])) {
    $nom = trim(htmlspecialchars($_POST['nom_reservation'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email_reservation'] ?? ''));
    $telephone = trim(htmlspecialchars($_POST['telephone'] ?? ''));
    $nombre_personnes = (int)($_POST['nombre_personnes'] ?? 1);
    $date_visite = trim($_POST['date_visite'] ?? '');
    $heure_visite = trim($_POST['heure_visite'] ?? '');
    $langue_guide = trim($_POST['langue_guide'] ?? 'fr');
    $type_guide = trim($_POST['type_guide'] ?? 'standard');
    $commentaires = trim(htmlspecialchars($_POST['commentaires'] ?? ''));

    if (!$nom || !$email || !$date_visite) {
        $msg_reservation = '<div class="alert alert-error">⚠️ Veuillez remplir les champs obligatoires (nom, email, date).</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg_reservation = '<div class="alert alert-error">⚠️ Adresse email invalide.</div>';
    } elseif (strtotime($date_visite) < time()) {
        $msg_reservation = '<div class="alert alert-error">⚠️ La date de visite doit être dans le futur.</div>';
    } else {
        $pdo->prepare("INSERT INTO reservations_guides (site_id, nom_visiteur, email, telephone, nombre_personnes, date_visite, heure_visite, langue_guide, type_guide, commentaires) VALUES (?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id, $nom, $email, $telephone, $nombre_personnes, $date_visite, $heure_visite ?: null, $langue_guide, $type_guide, $commentaires]);
        $msg_reservation = '<div class="alert alert-success">✅ Votre demande de réservation a été enregistrée. L\'agente vous contactera bientôt.</div>';
    }
}

// Images Unsplash par site (recherche par mots-clés du lieu)
$images_unsplash = [
    1 => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=800&q=80', // Lac Assal — sel blanc
    2 => 'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=800&q=80', // Lac Abbé — cheminées
    3 => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&q=80', // Forêt
    4 => 'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?w=800&q=80', // Mosquée
    5 => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80', // Marché épices
    6 => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80', // Plage Arta
    7 => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800&q=80', // Ville côtière
    8 => 'https://images.unsplash.com/photo-1561681173-c2ef9b1dfbe3?w=800&q=80', // Îles corail
];
// Priorité : image_url admin → fallback Unsplash par défaut
$img_url = !empty($site['image_url']) ? $site['image_url'] : ($images_unsplash[$id] ?? 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=800&q=80');

// Texte pour TTS
$texte_tts = $fiche
    ? $fiche['titre'] . '. ' . $fiche['contenu']
    : ($site['nom'] . '. ' . $site['description']);

// Config RA par type
$ra_configs = [
    'panorama'       => ['icon'=>'🌐', 'label'=>'Vue 360°',          'color'=>'#1E6B3A', 'desc'=>'Vue panoramique interactive à 360°'],
    'reconstitution' => ['icon'=>'🏛️', 'label'=>'Reconstitution',    'color'=>'#8B5000', 'desc'=>'Reconstitution historique animée'],
    'faune'          => ['icon'=>'🦅', 'label'=>'Faune locale',       'color'=>'#2C5F2E', 'desc'=>'Identification de la faune endémique'],
    'annotation'     => ['icon'=>'📍', 'label'=>'Annotations',        'color'=>'#4A2060', 'desc'=>'Points d\'intérêt annotés'],
];
$ra_cfg = $ra ? ($ra_configs[$ra['type_overlay']] ?? $ra_configs['annotation']) : null;

$page_title = $site['nom'];
include 'includes/header.php';
?>

<style>
/* ---- Styles spécifiques à cette page ---- */
.site-image-hero {
    width:100%; height:420px; object-fit:cover;
    display:block; position:relative;
}
.site-image-wrap {
    position:relative; overflow:hidden; height:420px;
    background:var(--ocean-deep);
}
.site-image-wrap img {
    width:100%; height:100%; object-fit:cover; display:block;
    transition: transform 6s ease;
}
.site-image-wrap:hover img { transform: scale(1.04); }
.site-image-overlay {
    position:absolute; inset:0;
    background:linear-gradient(to top, rgba(13,27,69,0.85) 0%, rgba(0,0,0,0.1) 60%);
}
.site-image-caption {
    position:absolute; bottom:0; left:0; right:0;
    padding:32px 40px;
    color:white;
}

/* ---- TTS Player ---- */
.tts-player {
    background:linear-gradient(135deg, #0D2B45, #1A5276);
    border-radius:10px; padding:20px; color:white;
}
.tts-header { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
.tts-icon { font-size:1.8rem; }
.tts-title { font-weight:600; font-size:0.95rem; }
.tts-subtitle { font-size:0.78rem; color:rgba(255,255,255,0.6); margin-top:2px; }

.tts-controls { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.tts-btn {
    width:44px; height:44px; border-radius:50%;
    background:var(--coral); border:none; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; transition:all 0.2s; flex-shrink:0;
    color:white;
}
.tts-btn:hover { background:var(--coral-pale); transform:scale(1.08); }
.tts-btn.playing { background: var(--green, #2e7d32); animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(46,125,50,0.5)} 50%{box-shadow:0 0 0 8px rgba(46,125,50,0)} }

.tts-progress-bar {
    flex:1; height:6px; background:rgba(255,255,255,0.15);
    border-radius:3px; overflow:hidden;
}
.tts-progress-fill {
    height:100%; width:0%; background:var(--or);
    border-radius:3px; transition:width 0.3s;
}
.tts-speed { font-size:0.75rem; color:rgba(255,255,255,0.5); text-align:right; margin-top:4px; }

.tts-text-preview {
    background:rgba(255,255,255,0.06);
    border-radius:6px; padding:12px; margin-top:12px;
    font-size:0.78rem; color:rgba(255,255,255,0.65);
    line-height:1.6; max-height:80px; overflow:hidden;
    position:relative;
}
.tts-text-preview::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:30px;
    background:linear-gradient(transparent, rgba(13,43,69,0.9));
}

/* ---- RA ---- */
.ra-box {
    border-radius:10px; overflow:hidden;
    background:#0a1520;
    width:100%; min-height:200px; aspect-ratio:16/9;
    position:relative; cursor:pointer;
    border:2px solid rgba(200,155,60,0.3);
    transition: border-color 0.3s;
}
.ra-box:hover { border-color:var(--or); }
.ra-box.ra-on { border-color:var(--or); }

.ra-idle {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    height:100%; color:white; text-align:center; padding:20px;
}
.ra-idle .ra-big-icon { font-size:3rem; margin-bottom:12px; }
.ra-idle .ra-btn-start {
    margin-top:16px; padding:10px 24px;
    background:var(--or); color:var(--texte);
    border:none; border-radius:20px; font-size:0.85rem; font-weight:600;
    cursor:pointer; transition:all 0.2s;
}
.ra-idle .ra-btn-start:hover { background:var(--or-pale); transform:scale(1.05); }

/* Canvas RA */
#ra-canvas {
    position:absolute; inset:0; width:100%; height:100%;
    display:none;
}
.ra-box.ra-on #ra-canvas { display:block; }

/* Overlay RA activée */
.ra-overlay-ui {
    position:absolute; inset:0; display:none;
    flex-direction:column;
}
.ra-top-bar {
    padding:10px 14px; display:flex; align-items:center; justify-content:space-between;
    background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);
}
.ra-top-bar .ra-badge {
    background:var(--or); color:var(--texte);
    padding:3px 10px; border-radius:10px;
    font-size:0.72rem; font-weight:600; letter-spacing:1px;
}
.ra-close-btn {
    background:rgba(255,255,255,0.2); border:none; color:white;
    padding:4px 10px; border-radius:4px; cursor:pointer; font-size:0.8rem;
}
.ra-annotations { position:absolute; inset:0; pointer-events:none; }
.ra-annotation {
    position:absolute; background:rgba(26,82,118,0.85);
    color:white; padding:6px 10px; border-radius:4px;
    font-size:0.72rem; line-height:1.4; max-width:140px;
    border-left:3px solid var(--or);
    animation:fadeInAnnotation 0.5s ease forwards;
    opacity:0;
}
@keyframes fadeInAnnotation { to { opacity:1; } }
.ra-scan-line {
    position:absolute; left:0; right:0; height:2px;
    background:linear-gradient(90deg,transparent,rgba(200,155,60,0.8),transparent);
    animation:scanMove 3s linear infinite;
}
@keyframes scanMove { 0%{top:0} 100%{top:100%} }
.ra-compass {
    position:absolute; bottom:14px; right:14px;
    width:50px; height:50px; background:rgba(0,0,0,0.6);
    border-radius:50%; border:2px solid var(--or);
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; color:white;
    animation:rotateCompass 8s linear infinite;
}
@keyframes rotateCompass { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

/* Avis étoiles */
.stars-input { display:flex; flex-direction:row-reverse; gap:4px; }
.stars-input input { display:none; }
.stars-input label {
    font-size:1.8rem; color:#ddd; cursor:pointer; transition:color 0.15s;
}
.stars-input label:hover,
.stars-input label:hover ~ label,
.stars-input input:checked ~ label { color:var(--or); }
</style>

<!-- Image hero du site -->
<div class="site-image-wrap">
    <img src="<?= $img_url ?>" alt="<?= htmlspecialchars($site['nom']) ?>"
         onerror="this.src='https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=800&q=80'">
    <div class="site-image-overlay"></div>
    <div class="site-image-caption">
        <div style="margin-bottom:8px;">
            <span class="card-badge badge-<?= $site['categorie'] ?>"><?= ucfirst($site['categorie']) ?></span>
            <?php if ($site['patrimoine']): ?>
            <span style="display:inline-block;margin-left:8px;background:rgba(200,155,60,0.4);border:1px solid var(--or);color:#F0C060;padding:3px 10px;border-radius:3px;font-size:0.7rem;letter-spacing:1px;">★ PATRIMOINE UNESCO</span>
            <?php endif; ?>
        </div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:600;text-shadow:0 2px 8px rgba(0,0,0,0.5);">
            <?= htmlspecialchars($site['nom']) ?>
        </h1>
        <?php if ($site['adresse']): ?>
        <p style="color:rgba(255,255,255,0.75);margin-top:6px;">📍 <?= htmlspecialchars($site['adresse']) ?></p>
        <?php endif; ?>
        <?php if ($note_info['nb'] > 0): ?>
        <div style="margin-top:10px;display:flex;align-items:center;gap:10px;">
            <span style="color:#F0C060;font-size:1.1rem;"><?= str_repeat('★', round($note_info['moy'])) ?><?= str_repeat('☆', 5-round($note_info['moy'])) ?></span>
            <span style="color:rgba(255,255,255,0.7);font-size:0.88rem;"><?= $note_info['moy'] ?>/5 · <?= $note_info['nb'] ?> avis</span>
        </div>
        <?php endif; ?>
        <div style="margin-top:10px;font-size:0.8rem;color:rgba(255,255,255,0.45);">
            <a href="index.php" style="color:rgba(255,255,255,0.5);text-decoration:none;">Accueil</a>
            <span style="margin:0 6px;">›</span>
            <a href="sites.php" style="color:rgba(255,255,255,0.5);text-decoration:none;">Sites</a>
            <span style="margin:0 6px;">›</span>
            <?= htmlspecialchars($site['nom']) ?>
        </div>
    </div>
</div>

<!-- Contenu principal -->
<div class="site-detail-grid">

    <!-- ===== COLONNE GAUCHE ===== -->
    <div>

        <!-- Description -->
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                À propos
            </div>
            <div class="detail-card-body">
                <p style="line-height:1.8;color:var(--texte-dim);font-size:0.95rem;"><?= htmlspecialchars($site['description']) ?></p>
            </div>
        </div>

        <!-- Fiche historique -->
        <?php if ($fiche): ?>
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Fiche Historique
            </div>
            <div class="detail-card-body">
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--ocean-deep);margin-bottom:14px;">
                    <?= htmlspecialchars($fiche['titre']) ?>
                </h3>
                <p id="fiche-contenu" style="line-height:1.9;color:var(--texte-dim);font-size:0.93rem;">
                    <?= nl2br(htmlspecialchars($fiche['contenu'])) ?>
                </p>
                <div style="margin-top:14px;font-size:0.78rem;color:var(--texte-dim);">
                    Publié le <?= date('d/m/Y', strtotime($fiche['date_creation'])) ?> · Langue : <?= strtoupper($fiche['langue']) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Avis -->
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Avis des touristes (<?= count($avis_list) ?>)
            </div>
            <div class="detail-card-body">
                <?= $msg ?>
                <!-- Formulaire -->
                <div style="background:var(--gris-pale);border-radius:8px;padding:20px;margin-bottom:24px;">
                    <h4 style="font-size:0.95rem;color:var(--ocean-deep);margin-bottom:14px;">Laisser un avis</h4>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Votre nom</label>
                                <input type="text" name="nom" placeholder="Prénom N." required maxlength="100">
                            </div>
                            <div class="form-group">
                                <label>Note</label>
                                <div class="stars-input" style="margin-top:6px;">
                                    <input type="radio" name="note" id="s5" value="5"><label for="s5">★</label>
                                    <input type="radio" name="note" id="s4" value="4"><label for="s4">★</label>
                                    <input type="radio" name="note" id="s3" value="3"><label for="s3">★</label>
                                    <input type="radio" name="note" id="s2" value="2"><label for="s2">★</label>
                                    <input type="radio" name="note" id="s1" value="1"><label for="s1">★</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Commentaire</label>
                            <textarea name="commentaire" rows="3" placeholder="Votre expérience…" required maxlength="500" style="resize:vertical;"></textarea>
                        </div>
                        <button type="submit" name="soumettre_avis" class="btn btn-primary">Publier l'avis</button>
                    </form>
                </div>
                <!-- Liste -->
                <?php if (empty($avis_list)): ?>
                    <p style="color:var(--texte-dim);font-size:0.88rem;">Soyez le premier à donner votre avis !</p>
                <?php else: ?>
                <div class="avis-list">
                    <?php foreach ($avis_list as $a): ?>
                    <div class="avis-card">
                        <div class="avis-header">
                            <span class="avis-author">👤 <?= htmlspecialchars($a['nom_visiteur'] ?? 'Anonyme') ?></span>
                            <span class="avis-date"><?= date('d/m/Y', strtotime($a['date_avis'])) ?></span>
                        </div>
                        <div class="avis-stars"><?= str_repeat('★',$a['note']) ?><?= str_repeat('☆',5-$a['note']) ?></div>
                        <div class="avis-text"><?= nl2br(htmlspecialchars($a['commentaire'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== COLONNE DROITE ===== -->
    <div>

        <!-- 🎧 AUDIO TTS — lit la fiche historique -->
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                Audio-guide — Fiche historique
            </div>
            <div class="detail-card-body">
                <div class="tts-player" id="tts-player">
                    <div class="tts-header">
                        <div class="tts-icon">🎙️</div>
                        <div>
                            <div class="tts-title">
                                <?= $fiche ? htmlspecialchars($fiche['titre']) : htmlspecialchars($site['nom']) ?>
                            </div>
                            <div class="tts-subtitle">
                                <?= $audio ? 'Par '.htmlspecialchars($audio['auteur']) : 'Lecture automatique de la fiche' ?>
                            </div>
                        </div>
                    </div>

                    <div class="tts-controls">
                        <button class="tts-btn" id="tts-play-btn" onclick="ttsToggle()" title="Lire / Pause">▶</button>
                        <button class="tts-btn" onclick="ttsStop()" style="background:rgba(255,255,255,0.15);font-size:0.9rem;" title="Arrêter">⏹</button>
                        <div class="tts-progress-bar">
                            <div class="tts-progress-fill" id="tts-fill"></div>
                        </div>
                    </div>

                    <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
                        <span style="font-size:0.72rem;color:rgba(255,255,255,0.5);">Vitesse :</span>
                        <?php
                        $vitesses = [
                            ['val' => '0.8', 'label' => 'Lente'],
                            ['val' => '1.0', 'label' => 'Normale'],
                            ['val' => '1.3', 'label' => 'Rapide'],
                        ];
                        foreach ($vitesses as $vitesse): ?>
                        <button onclick="ttsSetRate(<?= $vitesse['val'] ?>)" class="tts-speed-btn"
                            style="padding:2px 8px;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);border:1px solid rgba(255,255,255,0.15);border-radius:10px;font-size:0.7rem;cursor:pointer;"
                            data-rate="<?= $vitesse['val'] ?>"><?= $vitesse['label'] ?></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="tts-text-preview" id="tts-preview">
                        <?= htmlspecialchars(mb_strimwidth(strip_tags($texte_tts), 0, 200)) ?>…
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔮 RÉALITÉ AUGMENTÉE -->
        <?php if ($ra && $ra_cfg): ?>
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><path d="M1 6l5 5-5 5M23 6l-5 5 5 5M8 12h8"/></svg>
                Réalité Augmentée — <?= $ra_cfg['label'] ?>
            </div>
            <div class="detail-card-body">
                <div class="ra-box" id="ra-box">
                    <!-- État inactif -->
                    <div class="ra-idle" id="ra-idle">
                        <div class="ra-big-icon"><?= $ra_cfg['icon'] ?></div>
                        <div style="font-size:0.9rem;color:rgba(255,255,255,0.8);margin-bottom:6px;">
                            <?= htmlspecialchars($ra_cfg['desc']) ?>
                        </div>
                        <div style="font-size:0.75rem;color:rgba(255,255,255,0.4);">
                            Mode : <?= strtoupper($ra['type_overlay']) ?>
                        </div>
                        <button class="ra-btn-start" onclick="lancerRA()">
                            🔮 Activer la RA
                        </button>
                    </div>

                    <!-- Canvas animé -->
                    <canvas id="ra-canvas"></canvas>

                    <!-- Interface RA active -->
                    <div class="ra-overlay-ui" id="ra-overlay">
                        <div class="ra-top-bar">
                            <span class="ra-badge">⬤ RA ACTIVE</span>
                            <span style="font-size:0.75rem;color:rgba(255,255,255,0.7);"><?= htmlspecialchars($site['nom']) ?></span>
                            <button class="ra-close-btn" onclick="arreterRA()">✕ Quitter</button>
                        </div>
                        <div class="ra-scan-line"></div>
                        <div class="ra-annotations" id="ra-annotations"></div>
                        <div class="ra-compass">🧭</div>
                    </div>
                </div>
                <?php if ($ra['description']): ?>
                <p style="font-size:0.8rem;color:var(--texte-dim);margin-top:10px;"><?= htmlspecialchars($ra['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Infos pratiques -->
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Infos pratiques
            </div>
            <div class="detail-card-body">
                <div style="display:flex;flex-direction:column;gap:12px;font-size:0.85rem;">
                    <div style="display:flex;gap:12px;">
                        <span>📍</span>
                        <div>
                            <div style="color:var(--texte-dim);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Localisation</div>
                            <?= htmlspecialchars($site['adresse'] ?? 'Djibouti') ?>
                        </div>
                    </div>
                    <?php if ($site['latitude']): ?>
                    <div style="display:flex;gap:12px;">
                        <span>🧭</span>
                        <div>
                            <div style="color:var(--texte-dim);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">GPS</div>
                            <span style="font-family:monospace;font-size:0.82rem;"><?= $site['latitude'] ?>, <?= $site['longitude'] ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div style="display:flex;gap:12px;">
                        <span>🏷️</span>
                        <div>
                            <div style="color:var(--texte-dim);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Catégorie</div>
                            <?= ucfirst($site['categorie']) ?>
                        </div>
                    </div>
                </div>
                <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                    <a href="carte.php?site=<?= $site['id'] ?>" class="btn btn-ocean btn-sm" style="justify-content:center;">📍 Voir sur la carte</a>
                    <a href="sites.php" class="btn btn-sm" style="background:var(--gris-pale);color:var(--ocean);justify-content:center;">← Retour aux sites</a>
                </div>
            </div>
        </div>

        <!-- 🧭 RÉSERVATION DE GUIDE -->
        <div class="detail-card">
            <div class="detail-card-header">
                <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Réserver un guide touristique
            </div>
            <div class="detail-card-body">
                <p style="font-size:0.85rem;color:var(--texte-dim);margin-bottom:16px;">
                    Réservez un guide professionnel pour découvrir <?= htmlspecialchars($site['nom']) ?> en profondeur.
                    Choisissez votre date, langue et type de visite.
                </p>
                <?= $msg_reservation ?>
                <form method="post" class="reservation-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom_reservation">Nom complet *</label>
                            <input type="text" id="nom_reservation" name="nom_reservation" required>
                        </div>
                        <div class="form-group">
                            <label for="email_reservation">Email *</label>
                            <input type="email" id="email_reservation" name="email_reservation" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone">
                        </div>
                        <div class="form-group">
                            <label for="nombre_personnes">Nombre de personnes</label>
                            <select id="nombre_personnes" name="nombre_personnes">
                                <option value="1">1 personne</option>
                                <option value="2">2 personnes</option>
                                <option value="3">3 personnes</option>
                                <option value="4">4 personnes</option>
                                <option value="5">5+ personnes</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_visite">Date de visite *</label>
                            <input type="date" id="date_visite" name="date_visite" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="heure_visite">Heure souhaitée</label>
                            <input type="time" id="heure_visite" name="heure_visite">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="langue_guide">Langue du guide</label>
                            <select id="langue_guide" name="langue_guide">
                                <option value="fr">Français</option>
                                <option value="ar">Arabe</option>
                                <option value="en">Anglais</option>
                                <option value="so">Somali</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type_guide">Type de visite</label>
                            <select id="type_guide" name="type_guide">
                                <option value="standard">Standard (2h)</option>
                                <option value="premium">Premium (demi-journée)</option>
                                <option value="groupe">Groupe (adapté)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="commentaires">Commentaires / demandes spéciales</label>
                        <textarea id="commentaires" name="commentaires" rows="3" placeholder="Ex: allergies, mobilité réduite, intérêts particuliers..."></textarea>
                    </div>
                    <button type="submit" name="soumettre_reservation" class="btn btn-primary" style="width:100%;justify-content:center;">
                        📅 Réserver maintenant
                    </button>
                </form>
            </div>
        </div>

    </div><!-- /sidebar -->
</div><!-- /grid -->

<script>
/* ========== TTS (Text-To-Speech) ========== */
const ttsTexte = <?= json_encode($texte_tts, JSON_UNESCAPED_UNICODE) ?>;
let ttsUtt = null, ttsPaused = false, ttsRate = 1.0;
let ttsIntervalId = null, ttsWordCount = 0, ttsWordIndex = 0;

function ttsToggle() {
    if (!window.speechSynthesis) {
        alert('Votre navigateur ne supporte pas la lecture audio (Text-to-Speech). Essayez Chrome ou Edge.');
        return;
    }
    if (speechSynthesis.speaking && !ttsPaused) {
        speechSynthesis.pause();
        ttsPaused = true;
        document.getElementById('tts-play-btn').textContent = '▶';
        document.getElementById('tts-play-btn').classList.remove('playing');
        clearInterval(ttsIntervalId);
    } else if (ttsPaused) {
        speechSynthesis.resume();
        ttsPaused = false;
        document.getElementById('tts-play-btn').textContent = '⏸';
        document.getElementById('tts-play-btn').classList.add('playing');
        startProgressSimulation();
    } else {
        ttsPlay();
    }
}

function ttsPlay() {
    speechSynthesis.cancel();
    ttsUtt = new SpeechSynthesisUtterance(ttsTexte);
    ttsUtt.lang  = 'fr-FR';
    ttsUtt.rate  = ttsRate;
    ttsUtt.pitch = 1;

    // Choisir une voix française si disponible
    const voix = speechSynthesis.getVoices().filter(v => v.lang.startsWith('fr'));
    if (voix.length > 0) ttsUtt.voice = voix[0];

    ttsWordCount = ttsTexte.split(/\s+/).length;
    ttsWordIndex = 0;

    ttsUtt.onboundary = function(e) {
        if (e.name === 'word') {
            ttsWordIndex++;
            var pct = Math.min(100, (ttsWordIndex / ttsWordCount) * 100);
            document.getElementById('tts-fill').style.width = pct + '%';
        }
    };

    ttsUtt.onend = function() {
        document.getElementById('tts-play-btn').textContent = '▶';
        document.getElementById('tts-play-btn').classList.remove('playing');
        document.getElementById('tts-fill').style.width = '100%';
        clearInterval(ttsIntervalId);
    };

    ttsUtt.onerror = function() {
        document.getElementById('tts-play-btn').textContent = '▶';
        document.getElementById('tts-play-btn').classList.remove('playing');
    };

    speechSynthesis.speak(ttsUtt);
    ttsPaused = false;
    document.getElementById('tts-play-btn').textContent = '⏸';
    document.getElementById('tts-play-btn').classList.add('playing');

    // Fallback progression si onboundary pas supporté (Firefox)
    startProgressSimulation();
}

function startProgressSimulation() {
    clearInterval(ttsIntervalId);
    var dureeEstimee = (ttsTexte.length / 15) / ttsRate * 1000; // ~15 chars/sec
    var debut = Date.now();
    ttsIntervalId = setInterval(function() {
        var pct = Math.min(99, ((Date.now()-debut)/dureeEstimee)*100);
        if (document.getElementById('tts-fill').style.width.replace('%','') < pct)
            document.getElementById('tts-fill').style.width = pct+'%';
    }, 500);
}

function ttsStop() {
    speechSynthesis.cancel();
    ttsPaused = false;
    clearInterval(ttsIntervalId);
    document.getElementById('tts-play-btn').textContent = '▶';
    document.getElementById('tts-play-btn').classList.remove('playing');
    document.getElementById('tts-fill').style.width = '0%';
}

function ttsSetRate(r) {
    ttsRate = r;
    document.querySelectorAll('.tts-speed-btn').forEach(function(b){
        b.style.background = parseFloat(b.dataset.rate)===r ? 'rgba(200,155,60,0.4)' : 'rgba(255,255,255,0.1)';
        b.style.color      = parseFloat(b.dataset.rate)===r ? '#F0C060' : 'rgba(255,255,255,0.6)';
    });
    if (speechSynthesis.speaking) { ttsStop(); ttsPlay(); }
}

// Charger les voix (async sur Chrome)
speechSynthesis.onvoiceschanged = function() { speechSynthesis.getVoices(); };


/* ========== RÉALITÉ AUGMENTÉE ========== */
const raType = '<?= $ra ? $ra['type_overlay'] : '' ?>';
const raColor = '<?= $ra_cfg ? $ra_cfg['color'] : '#1A5276' ?>';

const raAnnotationsData = {
    panorama:       [
        {left:'15%',top:'20%', text:'Cratère volcanique — 1M ans'},
        {left:'60%',top:'35%', text:'Cristaux de sel — 34.8% salinité'},
        {left:'75%',top:'60%', text:'Point le plus bas d\'Afrique : -155m'},
    ],
    reconstitution: [
        {left:'20%',top:'25%', text:'Lac préhistorique étendu'},
        {left:'55%',top:'40%', text:'Faune disparue — il y a 10 000 ans'},
        {left:'70%',top:'65%', text:'Cheminées calcaires actives'},
    ],
    faune:          [
        {left:'20%',top:'20%', text:'Francolin de Djibouti (endémique)'},
        {left:'60%',top:'30%', text:'Flamant rose — Phoenicopterus'},
        {left:'40%',top:'65%', text:'Zone protégée — Réserve naturelle'},
    ],
    annotation:     [
        {left:'15%',top:'20%', text:'Arc en ogive — style ottoman'},
        {left:'60%',top:'25%', text:'Minaret — 906 apr. J.-C.'},
        {left:'50%',top:'65%', text:'Faïences yéménites originales'},
    ],
};

function lancerRA() {
    var box    = document.getElementById('ra-box');
    var idle   = document.getElementById('ra-idle');
    var canvas = document.getElementById('ra-canvas');
    var overlay= document.getElementById('ra-overlay');

    // 1. Masquer l'idle
    idle.style.display = 'none';

    // 2. Ajouter la classe ra-on sur le box (active le canvas via CSS)
    box.classList.add('ra-on');

    // 3. Afficher le canvas et l'overlay
    canvas.style.display = 'block';
    overlay.style.display = 'flex';

    // 4. Lire les dimensions du CONTENEUR (ra-box), pas du canvas caché
    var W = box.offsetWidth  || 400;
    var H = box.offsetHeight || 225;
    canvas.width  = W;
    canvas.height = H;

    // 5. Dessiner
    dessinerCanvas(canvas, W, H);
    ajouterAnnotations();
}

function dessinerCanvas(canvas, W, H) {
    // W et H sont passés depuis lancerRA() pour éviter offsetWidth=0
    W = W || canvas.offsetWidth  || 400;
    H = H || canvas.offsetHeight || 225;
    canvas.width  = W;
    canvas.height = H;
    var ctx = canvas.getContext('2d');

    // Fond dégradé selon le type
    var grad = ctx.createLinearGradient(0,0,W,H);
    if (raType === 'panorama') {
        grad.addColorStop(0, '#0a2a1a');
        grad.addColorStop(0.5, '#1E6B3A');
        grad.addColorStop(1, '#2d4a0a');
    } else if (raType === 'faune') {
        grad.addColorStop(0, '#0a1f0a');
        grad.addColorStop(1, '#1a3a1a');
    } else {
        grad.addColorStop(0, '#0a0a20');
        grad.addColorStop(1, '#1a1a40');
    }
    ctx.fillStyle = grad;
    ctx.fillRect(0,0,W,H);

    // Grille RA
    ctx.strokeStyle = 'rgba(200,155,60,0.15)';
    ctx.lineWidth = 1;
    for (var x=0; x<W; x+=40) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
    for (var y=0; y<H; y+=40) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

    // Formes selon le type
    if (raType === 'panorama' || raType === 'faune') {
        // Silhouette paysage
        ctx.fillStyle = 'rgba(255,255,255,0.06)';
        ctx.beginPath();
        ctx.moveTo(0, H*0.7);
        for (var i=0; i<=W; i+=30) {
            ctx.lineTo(i, H*0.7 - Math.sin(i*0.04)*30 - Math.random()*20);
        }
        ctx.lineTo(W, H); ctx.lineTo(0, H); ctx.closePath(); ctx.fill();
    }

    // Cercles de radar
    ctx.strokeStyle = 'rgba(200,155,60,0.25)';
    ctx.lineWidth = 1;
    [60,110,160].forEach(function(r) {
        ctx.beginPath(); ctx.arc(W/2, H/2, r, 0, Math.PI*2); ctx.stroke();
    });

    // Réticule centre
    ctx.strokeStyle = 'rgba(200,155,60,0.6)';
    ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(W/2-20,H/2); ctx.lineTo(W/2+20,H/2); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(W/2,H/2-20); ctx.lineTo(W/2,H/2+20); ctx.stroke();
    ctx.beginPath(); ctx.arc(W/2,H/2,8,0,Math.PI*2); ctx.stroke();

    // Texte info bas
    ctx.fillStyle = 'rgba(200,155,60,0.7)';
    ctx.font = '10px monospace';
    ctx.fillText('RA MODE: ' + raType.toUpperCase() + '  |  LAT: <?= $site['latitude'] ?? '11.58' ?>  |  LON: <?= $site['longitude'] ?? '43.15' ?>', 10, H-10);

    // Animation continue
    animerCanvas(ctx, W, H);
}

var raAngle = 0;
var raAnimFrame;
function animerCanvas(ctx, W, H) {
    raAngle += 0.04;
    
    // Redessiner le fond complètement
    var grad = ctx.createLinearGradient(0,0,W,H);
    if (raType === 'panorama') {
        grad.addColorStop(0, '#0a2a1a');
        grad.addColorStop(0.5, '#1E6B3A');
        grad.addColorStop(1, '#2d4a0a');
    } else if (raType === 'faune') {
        grad.addColorStop(0, '#0a1f0a');
        grad.addColorStop(1, '#1a3a1a');
    } else {
        grad.addColorStop(0, '#0a0a20');
        grad.addColorStop(1, '#1a1a40');
    }
    ctx.fillStyle = grad;
    ctx.fillRect(0,0,W,H);

    // Grille RA
    ctx.strokeStyle = 'rgba(200,155,60,0.15)';
    ctx.lineWidth = 1;
    for (var x=0; x<W; x+=40) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
    for (var y=0; y<H; y+=40) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

    // Cercles de radar
    ctx.strokeStyle = 'rgba(200,155,60,0.25)';
    ctx.lineWidth = 1;
    [60,110,160].forEach(function(r) {
        ctx.beginPath(); ctx.arc(W/2, H/2, r, 0, Math.PI*2); ctx.stroke();
    });

    // Rayon rotatif
    ctx.save();
    ctx.translate(W/2, H/2);
    ctx.rotate(raAngle);
    var grd = ctx.createLinearGradient(0,0,Math.min(W,H)/2,0);
    grd.addColorStop(0,'rgba(200,155,60,0.3)');
    grd.addColorStop(1,'rgba(200,155,60,0)');
    ctx.fillStyle = grd;
    ctx.beginPath(); ctx.moveTo(0,0);
    ctx.arc(0,0,Math.min(W,H)/2, -0.3, 0.3);
    ctx.closePath(); ctx.fill();
    ctx.restore();

    // Réticule centre
    ctx.strokeStyle = 'rgba(200,155,60,0.6)';
    ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(W/2-20,H/2); ctx.lineTo(W/2+20,H/2); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(W/2,H/2-20); ctx.lineTo(W/2,H/2+20); ctx.stroke();
    ctx.beginPath(); ctx.arc(W/2,H/2,8,0,Math.PI*2); ctx.stroke();

    // Texte info bas
    ctx.fillStyle = 'rgba(200,155,60,0.7)';
    ctx.font = '10px monospace';
    ctx.fillText('RA MODE: ' + raType.toUpperCase() + '  |  LAT: <?= $site['latitude'] ?? '11.58' ?>  |  LON: <?= $site['longitude'] ?? '43.15' ?>', 10, H-10);

    // Vérifier si la RA est toujours active
    if (document.getElementById('ra-box') && document.getElementById('ra-box').classList.contains('ra-on')) {
        raAnimFrame = requestAnimationFrame(function(){ animerCanvas(ctx,W,H); });
    }
}

function ajouterAnnotations() {
    var container = document.getElementById('ra-annotations');
    container.innerHTML = '';
    var annots = raAnnotationsData[raType] || raAnnotationsData['annotation'];
    annots.forEach(function(a, i) {
        var el = document.createElement('div');
        el.className = 'ra-annotation';
        el.style.left    = a.left;
        el.style.top     = a.top;
        el.style.animationDelay = (i * 0.6) + 's';
        el.innerHTML = a.text;
        container.appendChild(el);
    });
}

function arreterRA() {
    cancelAnimationFrame(raAnimFrame);
    document.getElementById('ra-idle').style.display    = 'flex';
    document.getElementById('ra-canvas').style.display  = 'none';
    document.getElementById('ra-overlay').style.display = 'none';
    document.getElementById('ra-box').classList.remove('ra-on');
    // Remettre la hauteur du box à auto pour l'idle
}
</script>

<?php include 'includes/footer.php'; ?>
