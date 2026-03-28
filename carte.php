<?php
require_once 'config/db.php';
$page_title = 'Carte interactive';

$sites = $pdo->query("
    SELECT s.id, s.nom, s.categorie, s.latitude, s.longitude, s.adresse, s.patrimoine,
           s.description,
           ROUND(AVG(a.note),1) AS note_moy,
           COUNT(DISTINCT a.id) AS nb_avis
    FROM sites s
    LEFT JOIN avis a ON a.site_id = s.id AND a.valide=1
    WHERE s.actif = 1 AND s.latitude IS NOT NULL
    GROUP BY s.id
")->fetchAll();

$site_focus = isset($_GET['site']) ? (int)$_GET['site'] : 0;

include 'includes/header.php';
?>

<!-- Leaflet chargé ici avant le div#map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<div class="page-header">
    <h1>Carte Interactive</h1>
    <p>Explorez tous les sites touristiques de Djibouti</p>
</div>

<div class="map-wrapper">
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
        <button class="filter-btn active" onclick="filtrerCarte('',this)">🌍 Tous</button>
        <button class="filter-btn" onclick="filtrerCarte('naturel',this)">🌋 Naturel</button>
        <button class="filter-btn" onclick="filtrerCarte('culturel',this)">🕌 Culturel</button>
        <button class="filter-btn" onclick="filtrerCarte('historique',this)">🏛️ Historique</button>
        <button class="filter-btn" onclick="filtrerCarte('gastronomique',this)">🍽️ Gastronomique</button>
    </div>

    <!-- HAUTEUR FIXE ICI — indispensable pour Leaflet -->
    <div id="map" style="height:580px;width:100%;border-radius:8px;border:2px solid var(--ocean);"></div>

    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;padding:10px 16px;background:var(--gris-pale);border-radius:4px;font-size:0.82rem;color:var(--texte-dim);">
        <span>Légende :</span>
        <span>🌋 Naturel</span><span>🕌 Culturel</span><span>🏛️ Historique</span>
        <span>🍽️ Gastronomique</span><span>⭐ Patrimoine</span>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const sitesData = <?= json_encode(array_values($sites), JSON_UNESCAPED_UNICODE) ?>;
const siteFocus  = <?= (int)$site_focus ?>;

const couleurs = {
    naturel:'#1E6B3A', culturel:'#7B3FA0',
    historique:'#8B5000', gastronomique:'#A0300A'
};
const emojis = { naturel:'🌋', culturel:'🕌', historique:'🏛️', gastronomique:'🍽️' };

const map = L.map('map').setView([11.75, 42.80], 8);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 18
}).addTo(map);

const markerLayer = L.layerGroup().addTo(map);
const allMarkers  = [];

sitesData.forEach(function(site) {
    if (!site.latitude || !site.longitude) return;
    var couleur  = couleurs[site.categorie] || '#1A5276';
    var emoji    = emojis[site.categorie]   || '📍';
    var patriTag = site.patrimoine == 1 ? '⭐ ' : '';

    var icone = L.divIcon({
        html: '<div style="width:36px;height:36px;background:'+couleur+';border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 3px 10px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);font-size:14px;">'+emoji+'</span></div>',
        iconSize:[36,36], iconAnchor:[18,36], popupAnchor:[0,-40], className:''
    });

    var starsHtml = site.note_moy
        ? '<span style="color:#C89B3C;">' + '★'.repeat(Math.round(site.note_moy)) + '☆'.repeat(5-Math.round(site.note_moy)) + '</span> <span style="font-size:0.78rem;color:#888;">'+site.note_moy+'/5 ('+site.nb_avis+' avis)</span>'
        : '<span style="font-size:0.78rem;color:#aaa;">Pas encore d\'avis</span>';

    var desc = site.description ? site.description.substring(0,100)+'…' : '';

    var popupHtml =
        '<div style="font-family:sans-serif;width:230px;">' +
        '<div style="background:'+couleur+';color:white;padding:10px 12px;margin:-14px -20px 12px;border-radius:4px 4px 0 0;">' +
        '<div style="font-size:0.65rem;text-transform:uppercase;opacity:0.8;">'+site.categorie+'</div>' +
        '<div style="font-weight:600;">'+patriTag+site.nom+'</div></div>' +
        '<div style="font-size:0.78rem;color:#7A6A50;margin-bottom:6px;">📍 '+(site.adresse||'Djibouti')+'</div>' +
        '<div style="margin-bottom:8px;">'+starsHtml+'</div>' +
        '<div style="font-size:0.78rem;color:#555;margin-bottom:12px;line-height:1.5;">'+desc+'</div>' +
        '<a href="site_detail.php?id='+site.id+'" style="display:block;background:#1A5276;color:white;padding:8px;border-radius:4px;text-decoration:none;text-align:center;font-size:0.82rem;">Voir la fiche →</a>' +
        '</div>';

    var marker = L.marker([parseFloat(site.latitude), parseFloat(site.longitude)], {icon:icone});
    marker.bindPopup(popupHtml, {maxWidth:260});
    marker._cat   = site.categorie;
    marker._id    = site.id;
    allMarkers.push(marker);
    markerLayer.addLayer(marker);

    if (siteFocus && site.id == siteFocus) {
        setTimeout(function(){ map.setView([parseFloat(site.latitude), parseFloat(site.longitude)], 13); marker.openPopup(); }, 400);
    }
});

function filtrerCarte(cat, btn) {
    document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    markerLayer.clearLayers();
    allMarkers.forEach(function(m){ if (!cat || m._cat === cat) markerLayer.addLayer(m); });
}

setTimeout(function(){ map.invalidateSize(); }, 200);
</script>

<?php include 'includes/footer.php'; ?>
