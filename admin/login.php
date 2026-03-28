<?php
session_start();
require_once '../config/db.php';

// Déjà connecté → redirect
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if ($login && $mdp) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ? LIMIT 1");
        $stmt->execute([$login]);
        $admin = $stmt->fetch();

        // Accepte le hash bcrypt OU le mot de passe en clair "admin123" pour la démo
        $ok = false;
        if ($admin) {
            if (password_verify($mdp, $admin['mot_de_passe'])) {
                $ok = true;
            } elseif ($mdp === 'admin123' && $admin['login'] === 'admin') {
                $ok = true; // mode démo
            }
        }

        if ($ok) {
            $_SESSION['admin_id']  = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'] ?? $admin['login'];
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect); exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Admin — Tourisme Djibouti</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Cormorant+Garamond:wght@600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:#0F1923;color:#E8E0D0;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.login-wrap{width:100%;max-width:420px;padding:20px;}
.login-card{background:#172130;border:1px solid #253650;border-radius:12px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,0.5);}
.login-logo{text-align:center;margin-bottom:28px;}
.login-logo h1{font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:#E8D5A3;}
.login-logo p{font-size:0.72rem;color:#7A8FA6;letter-spacing:2px;text-transform:uppercase;margin-top:4px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:0.75rem;font-weight:500;color:#7A8FA6;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;}
.form-group input{width:100%;padding:11px 14px;background:#1E2D40;border:1.5px solid #253650;color:#E8E0D0;border-radius:4px;font-size:0.9rem;font-family:'Inter',sans-serif;transition:border-color 0.15s;}
.form-group input:focus{outline:none;border-color:#2471A3;box-shadow:0 0 0 3px rgba(36,113,163,0.15);}
.btn-login{width:100%;padding:12px;background:#1A5276;color:white;border:none;border-radius:4px;font-size:0.9rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:background 0.15s;margin-top:8px;}
.btn-login:hover{background:#2471A3;}
.error{background:rgba(192,57,43,0.1);border-left:4px solid #C0392B;color:#F08070;padding:10px 14px;border-radius:4px;font-size:0.85rem;margin-bottom:16px;}
.demo-info{background:rgba(200,155,60,0.1);border:1px solid rgba(200,155,60,0.3);color:#C89B3C;padding:12px 14px;border-radius:4px;font-size:0.78rem;margin-top:16px;text-align:center;line-height:1.6;}
.back-link{display:block;text-align:center;margin-top:20px;color:#7A8FA6;font-size:0.8rem;text-decoration:none;}
.back-link:hover{color:#E8E0D0;}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <div style="font-size:2.5rem;margin-bottom:10px;">🌍</div>
            <h1>Tourisme Djibouti</h1>
            <p>Espace Administrateur</p>
        </div>
        <?php if ($error): ?>
        <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Identifiant</label>
                <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" placeholder="admin" autofocus required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Se connecter →</button>
        </form>
        <div class="demo-info">
            🔑 <strong>Accès démo</strong><br>
            Login : <strong>admin</strong> · Mot de passe : <strong>admin123</strong>
        </div>
    </div>
    <a href="/tourisme_djibouti/index.php" class="back-link">← Retour au site public</a>
</div>
</body>
</html>
