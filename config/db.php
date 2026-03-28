<?php
// ============================================================
//  config/db.php — Connexion à la base de données
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'tourisme_djibouti');
define('DB_USER', 'root');       // Modifie selon ton XAMPP
define('DB_PASS', '');           // Modifie selon ton XAMPP
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:monospace;background:#1a0000;color:#ff6b6b;padding:20px;margin:20px;border-radius:8px;">
        <strong>Erreur de connexion à la base de données</strong><br><br>
        ' . htmlspecialchars($e->getMessage()) . '<br><br>
        Vérifiez que XAMPP est démarré (Apache + MySQL) et que la base <em>tourisme_djibouti</em> existe.
    </div>');
}
?>
