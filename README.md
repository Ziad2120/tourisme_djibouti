# Tourisme Local Djibouti

Application web PHP/MySQL de valorisation du patrimoine touristique djiboutien. Le projet propose un site public pour découvrir les sites touristiques, les parcours, les avis et une carte interactive, ainsi qu’un espace d’administration pour gérer le contenu.

## 1. Vue d’ensemble

Le projet est construit en **PHP procédural**, avec une base de données **MySQL** et une interface en **HTML/CSS/JavaScript**. La carte interactive repose sur **Leaflet.js**.

Le site est organisé en deux parties :

- **Front-office** : consultation des sites, recherche, carte, parcours, avis et contact.
- **Back-office admin** : gestion des sites, parcours, fiches historiques, avis et compte administrateur.

## 2. Fonctionnalités identifiées

### Front-office
- Page d’accueil avec statistiques et sites mis en avant.
- Liste des sites touristiques avec filtrage par catégorie.
- Détail d’un site :
  - informations générales,
  - image principale,
  - moyenne des avis,
  - fiches historiques,
  - audio-guides,
  - éléments de réalité augmentée en mode démonstration,
  - réservation de guide.
- Carte interactive des sites avec Leaflet.
- Parcours touristiques et détail de parcours.
- Recherche de sites.
- Consultation et ajout d’avis.
- Formulaire de contact avec enregistrement dans des fichiers log.

### Back-office
- Connexion administrateur.
- Tableau de bord avec statistiques.
- Gestion CRUD des sites.
- Gestion CRUD des parcours.
- Gestion des fiches historiques.
- Modération des avis.
- Mise à jour du compte administrateur.

## 3. Technologies utilisées

- **PHP**
- **MySQL / MariaDB**
- **PDO** pour la connexion base de données
- **HTML5 / CSS3 / JavaScript**
- **Leaflet.js** pour la carte
- **Google Fonts**

## 4. Structure du projet

```text
 touriste_djibouti/
 ├── admin/
 │   ├── index.php              # tableau de bord admin
 │   ├── login.php              # connexion admin
 │   ├── logout.php             # déconnexion admin
 │   ├── sites.php              # gestion des sites
 │   ├── parcours.php           # gestion des parcours
 │   ├── fiches.php             # gestion des fiches historiques
 │   ├── avis.php               # modération des avis
 │   ├── compte.php             # profil admin
 │   ├── admin_header.php
 │   ├── admin_footer.php
 │   ├── auth.php
 │   └── includes/              # doublon de certains fichiers admin
 ├── assets/
 │   ├── css/style.css
 │   └── js/main.js
 ├── config/
 │   └── db.php                 # connexion PDO
 ├── includes/
 │   ├── header.php
 │   └── footer.php
 ├── index.php                  # accueil
 ├── sites.php                  # liste des sites
 ├── site_detail.php            # détail d’un site
 ├── parcours.php               # liste des parcours
 ├── parcours_detail.php        # détail d’un parcours
 ├── carte.php                  # carte interactive
 ├── recherche.php              # moteur de recherche
 ├── avis.php                   # avis visiteurs
 ├── contact.php                # formulaire de contact
 ├── database.sql               # schéma + données de test
 ├── contact_messages.log       # messages contact
 ├── confirmations.log          # confirmations simulées
 └── README.md
```

## 5. Base de données

Le fichier `database.sql` crée la base `tourisme_djibouti` et contient des données de démonstration.

### Tables principales
- `sites`
- `fiches_historiques`
- `audio_guides`
- `realite_augmentee`
- `parcours`
- `parcours_sites`
- `touristes`
- `avis`
- `reservations_guides`
- `admins`

## 6. Installation locale

### Prérequis
- XAMPP, WAMP ou Laragon
- PHP 7.4+ ou 8.x
- MySQL / MariaDB
- Apache

### Étapes
1. Copier le dossier `tourisme_djibouti` dans le répertoire web local.
   - Exemple XAMPP : `htdocs/`
   - Exemple WAMP : `www/`

2. Créer la base et importer le SQL.
   - Ouvrir **phpMyAdmin**.
   - Créer une base nommée `tourisme_djibouti`.
   - Importer le fichier `database.sql`.

3. Vérifier la configuration dans `config/db.php`.

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tourisme_djibouti');
define('DB_USER', 'root');
define('DB_PASS', '');
```

4. Démarrer **Apache** et **MySQL**.

5. Ouvrir le projet dans le navigateur :

```text
http://localhost/tourisme_djibouti/
```

## 7. Accès administrateur

Le projet prévoit un compte administrateur de démonstration.

- **Login** : `admin`
- **Mot de passe** : `admin123`

URL d’accès :

```text
http://localhost/tourisme_djibouti/admin/login.php
```

## 8. Analyse du projet

### Points positifs
- Structure claire entre partie publique et partie administration.
- Utilisation de **PDO** avec requêtes préparées à plusieurs endroits.
- Base de données déjà fournie avec données de démonstration.
- Interface visuelle cohérente pour un projet académique.
- Intégration d’une carte interactive et d’une logique de réservation.
- Dashboard admin avec statistiques utiles.

### Limites ou incohérences repérées
1. **Dossiers parasites dans l’archive**
   - Présence de dossiers anormaux :
     - `{config,includes,assets/`
     - `{config,includes,assets/{css,js,images}}/`
   - Ils semblent provenir d’une mauvaise commande de création de dossier.
   - Ils peuvent être supprimés sans impacter l’application.

2. **Doublons dans l’espace admin**
   - Il existe à la fois :
     - `admin/admin_header.php`, `admin/admin_footer.php`, `admin/auth.php`
     - et aussi les mêmes fichiers dans `admin/includes/`
   - Cela crée une ambiguïté de maintenance.

3. **Logs stockés dans le projet**
   - `contact_messages.log` et `confirmations.log` sont inclus dans l’archive.
   - En production, ces fichiers devraient être placés hors du dossier public ou protégés.

4. **Compte admin de démonstration peu sécurisé**
   - Le code accepte explicitement `admin/admin123` pour la démo.
   - Ce comportement doit être retiré en production.

5. **Incohérence dans le SQL admin**
   - Le commentaire indique un accès `admin123`, mais le hash SQL mentionné correspond à `password`.
   - Le projet contourne ce problème dans `admin/login.php` avec une condition spéciale.

6. **Dépendance à des images externes**
   - Plusieurs pages utilisent des images Unsplash par défaut.
   - Sans connexion internet, ces images peuvent ne pas s’afficher.

7. **Architecture encore monolithique**
   - La logique métier, l’affichage et le traitement sont mélangés dans plusieurs pages.
   - Pour une version plus professionnelle, il faudrait aller vers une architecture MVC ou au moins séparer davantage les traitements.

## 9. Recommandations d’amélioration

- Supprimer les dossiers parasites et les fichiers dupliqués.
- Centraliser les includes admin dans un seul emplacement.
- Déplacer les logs dans un dossier non public.
- Remplacer le mode de connexion démo par un vrai hash unique.
- Ajouter une protection CSRF sur les formulaires.
- Renforcer la validation côté serveur.
- Ajouter la pagination sur les listes longues.
- Prévoir l’upload local des images au lieu d’URLs externes uniquement.
- Mettre en place des messages flash centralisés.
- Séparer davantage la logique SQL, la logique métier et les vues.

## 10. Utilisation recommandée

Ce projet convient bien pour :
- un **projet académique**,
- une **démonstration fonctionnelle**,
- une base de départ pour une future plateforme touristique locale.

Pour un usage réel en production, il faut renforcer :
- la sécurité,
- la gestion des fichiers,
- l’authentification,
- l’organisation du code.

## 11. Auteur / contexte

Projet web de valorisation touristique orienté vers la découverte du patrimoine naturel, culturel et historique de Djibouti.

---

## Résumé rapide

**Nom du projet** : Tourisme Local Djibouti  
**Type** : site web touristique + espace admin  
**Stack** : PHP, MySQL, JavaScript, Leaflet  
**Base de données** : fournie via `database.sql`  
**Niveau actuel** : bon prototype académique fonctionnel  
**Axes d’amélioration** : sécurité, nettoyage de structure, factorisation, robustesse
