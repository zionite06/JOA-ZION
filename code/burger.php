<?php
require 'config.php';
session_start();

// Controleer of gebruiker is ingelogd en een burger is
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'burger') {
    header("Location: login.php");
    exit;
}

// Haal actieve verkiezing op
$stmt = $pdo->query("SELECT * FROM elections WHERE is_open = TRUE LIMIT 1");
$election = $stmt->fetch();

if (!$election) {
    echo "<h2>Er is momenteel geen open verkiezing.</h2>";
    exit;
}

// Stem opslaan
if (isset($_POST['candidate_id'])) {
    $candidate_id = $_POST['candidate_id'];
    $user_id = $_SESSION['user_id'];
    $election_id = $election['id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, election_id, candidate_id) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $election_id, $candidate_id]);
        $message = "Uw stem is succesvol geregistreerd!";
    } catch (PDOException $e) {
        $message = "U heeft al gestemd in deze verkiezing.";
    }
}

// Haal partijen op
$parties = $pdo->query("SELECT * FROM parties WHERE approved = TRUE")->fetchAll();

// Als een partij geselecteerd is
$selected_party = isset($_GET['partij']) ? intval($_GET['partij']) : null;
$candidates = [];
if ($selected_party) {
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE party_id = ? ORDER BY position ASC");
    $stmt->execute([$selected_party]);
    $candidates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Stemmen - Electronisch Stemmen</title>
    <style>
        body { background-color: #f8f5ef; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        button, a { display: block; width: 100%; margin: 8px 0; padding: 10px; border: none; border-radius: 6px; background-color: #0077cc; color: white; text-decoration: none; cursor: pointer; }
        button:hover, a:hover { background-color: #005fa3; }
        h2 { color: #333; }
    </style>
</head>
<body>
<div class="container">
    <h2><?php echo htmlspecialchars($election['title']); ?></h2>
    <p><?php echo htmlspecialchars($election['description']); ?></p>

    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <?php if (!$selected_party): ?>
        <h3>Kies een partij</h3>
        <?php foreach ($parties as $party): ?>
            <a href="?partij=<?php echo $party['id']; ?>">
                <?php echo htmlspecialchars($party['name']); ?>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <h3>Kandidaten van deze partij</h3>
        <?php foreach ($candidates as $cand): ?>
            <form method="POST">
                <input type="hidden" name="candidate_id" value="<?php echo $cand['id']; ?>">
                <button type="submit">
                    <?php echo htmlspecialchars($cand['name']); ?>
                </button>
            </form>
        <?php endforeach; ?>
        <a href="burger.php">← Terug naar partijen</a>
    <?php endif; ?>
</div>
</body>
</html>
