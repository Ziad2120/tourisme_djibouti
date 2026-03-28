<?php
require_once 'includes/auth.php';
require_once '../config/db.php';
requiertAdmin();
$page_title = 'Mon compte';

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nom     = trim($_POST['nom'] ?? '');
    $mdp_new = $_POST['mdp_new'] ?? '';
    $mdp_conf= $_POST['mdp_conf'] ?? '';

    if ($mdp_new && $mdp_new !== $mdp_conf) {
        $msg = '<div class="alert alert-error">⚠️ Les mots de passe ne correspondent pas.</div>';
    } else {
        $sets = ['nom = ?']; $params = [$nom];
        if ($mdp_new) { $sets[] = 'mot_de_passe = ?'; $params[] = password_hash($mdp_new, PASSWORD_DEFAULT); }
        $params[] = $_SESSION['admin_id'];
        $pdo->prepare("UPDATE admins SET ".implode(',',$sets)." WHERE id=?")->execute($params);
        $_SESSION['admin_nom'] = $nom;
        $msg = '<div class="alert alert-success">✅ Compte mis à jour !</div>';
    }
}
$admin = $pdo->prepare("SELECT * FROM admins WHERE id=?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();

include 'includes/admin_header.php';
?>
<div style="max-width:500px;">
    <h2 style="margin-bottom:24px;font-size:1.1rem;">⚙️ Mon compte administrateur</h2>
    <?= $msg ?>
    <form method="POST" class="table-card" style="padding:24px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Identifiant (non modifiable)</label>
                <input type="text" value="<?= htmlspecialchars($admin['login']) ?>" disabled style="opacity:0.5;">
            </div>
            <div class="form-group">
                <label>Nom affiché</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($admin['nom'] ?? '') ?>" placeholder="Ex: Mohammed Ali">
            </div>
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="mdp_new" placeholder="Laisser vide pour ne pas changer">
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="mdp_conf" placeholder="Répétez le mot de passe">
            </div>
        </div>
        <div style="padding-top:16px;border-top:1px solid var(--border);margin-top:8px;">
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
        </div>
    </form>
</div>
<?php include 'includes/admin_footer.php'; ?>
