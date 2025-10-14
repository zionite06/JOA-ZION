<?php
require 'config.php';
session_start();

// Controleer of gebruiker is ingelogd en een partij-account is
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'partij') {
    header("Location: login.php");
    exit;
}

// Ophalen van verkiezing
$stmt = $pdo->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1");
$election = $stmt->fetch();

// Ophalen van partij-ID (verondersteld dat de partijnaam in gebruikersnaam zit, bv: partij_pvdt)
$stmt = $pdo->prepare("SELECT * FROM parties WHERE email = ? OR name LIKE ?");
$stmt->execute([$_SESSION['username'], '%' . $_SESSION['username'] . '%']);
$party = $stmt->fetch();

if (!$party) {
    echo "<h2>Geen partij gevonden voor dit account.</h2>";
    exit;
}

// Kandidaat toevoegen
if (isset($_POST['add']) && !$election['is_open']) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO candidates (party_id, name, position) VALUES (?, ?, ?)");
        $stmt->execute([$party['id'], $name, 0]);
    }
    header("Location: partij.php");
    exit;
}

// Kandidaat verwijderen
if (isset($_POST['delete']) && !$election['is_open']) {
    $cid = intval($_POST['candidate_id']);
    $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ? AND party_id = ?");
    $stmt->execute([$cid, $party['id']]);
    header("Location: partij.php");
    exit;
}

// Ophalen van kandidaten
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE party_id = ? ORDER BY position ASC");
$stmt->execute([$party['id']]);
$candidates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Partijdashboard - Electronisch Stemmen</title>
    <style>
        body { background-color: #f8f5ef; font-family: Arial; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 500px; }
        h2, h3 { text-align: center; color: #333; }
        form { margin: 10px 0; text-align: center; }
        input[type=text] { width: 80%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; }
        button { background-color: #0077cc; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        button:hover { background-color: #005fa3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #0077cc; color: white; }
        .closed { color: red; font-weight: bold; }
        .open { color: green; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2><?php echo htmlspecialchars($party['name']); ?> - Dashboard</h2>
    <p>Status verkiezing: 
        <?php echo $election['is_open'] 
            ? '<span class="open">OPEN</span>' 
            : '<span class="closed">GESLOTEN</span>'; ?>
    </p>

    <h3>Kandidatenlijst</h3>
    <table>
        <tr>
            <th>Naam</th>
            <?php if (!$election['is_open']): ?><th>Actie</th><?php endif; ?>
        </tr>
        <?php foreach ($candidates as $cand): ?>
            <tr>
                <td><?php echo htmlspecialchars($cand['name']); ?></td>
                <?php if (!$election['is_open']): ?>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="candidate_id" value="<?php echo $cand['id']; ?>">
                        <button type="submit" name="delete">Verwijder</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if (!$election['is_open']): ?>
    <h3>Nieuwe kandidaat toevoegen</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Naam kandidaat" required>
        <button type="submit" name="add">Toevoegen</button>
    </form>
    <?php else: ?>
        <p style="color:#888; text-align:center;">Toevoegen of verwijderen is niet meer mogelijk zodra de verkiezing is geopend.</p>
    <?php endif; ?>

    <p style="text-align:center; margin-top:20px;">
        <a href="logout.php" style="color:#0077cc;">Uitloggen</a>
    </p>
</div>
</body>
</html>
