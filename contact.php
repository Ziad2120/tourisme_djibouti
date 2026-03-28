<?php
require_once 'config/db.php';
$page_title = 'Contact Agente Touristique';

// Soumission du formulaire
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $type_demande = trim(htmlspecialchars($_POST['type_demande'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));

    if (!$nom || !$email || !$type_demande || !$message) {
        $msg = '<div class="alert alert-error">⚠️ Tous les champs sont obligatoires.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = '<div class="alert alert-error">⚠️ Adresse email invalide.</div>';
    } else {
        // Envoi de l'email (simulation pour test local)
        $log_file = __DIR__ . '/contact_messages.log';
        $log_entry = date('Y-m-d H:i:s') . " - Nom: $nom, Email: $email, Type: $type_demande\nMessage:\n$message\n\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        // Simulation d'envoi de confirmation (pour test local)
        $confirmation_log = __DIR__ . '/confirmations.log';
        $confirmation_entry = date('Y-m-d H:i:s') . " - Confirmation envoyée à $email pour $type_demande\n";
        file_put_contents($confirmation_log, $confirmation_entry, FILE_APPEND);

        // Simulation de succès
        $msg = '<div class="alert alert-success">✅ Votre message a été envoyé avec succès ! Une confirmation vous a été envoyée. (Test local - messages enregistrés)</div>';
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="container">
    <h1>Contactez notre Agente Touristique</h1>
    <p>Pour toute question concernant vos visites à Djibouti, n'hésitez pas à nous contacter via ce formulaire ou directement à <strong>agente@tourisme-djibouti.dj</strong>.</p>

    <?= $msg ?>

    <form method="post" class="contact-form">
        <div class="form-group">
            <label for="nom">Nom complet *</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div class="form-group">
            <label for="email">Votre email *</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="type_demande">Type de demande *</label>
            <select id="type_demande" name="type_demande" required>
                <option value="">Sélectionnez...</option>
                <option value="Demande d'information">Demande d'information</option>
                <option value="Réservation de guide">Réservation de guide</option>
                <option value="Conseil touristique">Conseil touristique</option>
            </select>
        </div>
        <div class="form-group">
            <label for="message">Message *</label>
            <textarea id="message" name="message" rows="6" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer le message</button>
    </form>
</main>

<?php include 'includes/footer.php'; ?>