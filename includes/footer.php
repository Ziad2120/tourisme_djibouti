<?php // includes/footer.php ?>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="brand-ar">جيبوتي</span>
            <p>Valoriser le patrimoine culturel<br>et naturel de Djibouti</p>
        </div>
        <div class="footer-links">
            <h4>Navigation</h4>
            <a href="/tourisme_djibouti/index.php">Accueil</a>
            <a href="/tourisme_djibouti/sites.php">Sites touristiques</a>
            <a href="/tourisme_djibouti/parcours.php">Parcours</a>
            <a href="/tourisme_djibouti/avis.php">Avis</a>
            <a href="/tourisme_djibouti/carte.php">Carte interactive</a>
        </div>
        <div class="footer-links">
            <h4>Catégories</h4>
            <a href="/tourisme_djibouti/sites.php?cat=naturel">Sites naturels</a>
            <a href="/tourisme_djibouti/sites.php?cat=culturel">Sites culturels</a>
            <a href="/tourisme_djibouti/sites.php?cat=historique">Sites historiques</a>
            <a href="/tourisme_djibouti/sites.php?cat=gastronomique">Gastronomie</a>
        </div>
        <div class="footer-info">
            <h4>Projet Académique</h4>
            <p>Application de valorisation du patrimoine djiboutien.<br>
            Développée dans le cadre d'un projet tutoré.</p>
            <span class="footer-badge">PHP · MySQL · Leaflet.js</span>
        </div>
        <div class="footer-contact">
            <h4>Contact Agente</h4>
            <p>Pour réservations et informations :</p>
            <div class="contact-details">
                <span>📧 agente@tourisme-djibouti.dj</span>
                <span>📞 +253 77 12 34 56</span>
            </div>
            <p style="font-size:0.8rem;margin-top:8px;color:var(--texte-dim);">
                Disponible du lundi au vendredi, 9h-17h.
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?= date('Y') ?> Tourisme Local Djibouti — Projet Tutoré</span>
    </div>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/tourisme_djibouti/assets/js/main.js"></script>
</body>
</html>
