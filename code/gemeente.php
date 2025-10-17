<?php
session_start();
require 'config.php'; // hier staat je databaseverbinding

// Controleer of de gebruiker is ingelogd als 'gemeente'
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'gemeente') {
    header("Location: login.php");
    exit;
}

// Haal verkiezingen op
$elections = $pdo->query("SELECT * FROM elections ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);

// Als gemeente op 'open/sluit' klikt
if (isset($_POST['toggle'])) {
    $id = $_POST['election_id'];
    $is_open = $_POST['is_open'] == 1 ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE elections SET is_open = ? WHERE id = ?");
    $stmt->execute([$is_open, $id]);
    header("Location: gemeente.php");
    exit;
}

// Partijen goedkeuren
if (isset($_POST['approve_party'])) {
    $party_id = $_POST['party_id'];
    $stmt = $pdo->prepare("UPDATE parties SET approved = 1 WHERE id = ?");
    $stmt->execute([$party_id]);
    header("Location: gemeente.php");
    exit;
}

// Haal partijen op
$parties = $pdo->query("SELECT * FROM parties ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Gemeente Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f2ec;
            color: #333;
            text-align: center;
            margin: 0;
            padding: 20px;
        }
        h1 { color: #005b96; }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 80%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        form { display: inline; }
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            background-color: #005b96;
        }
        button:hover {
            background-color: #003f6c;
        }
    </style>
</head>
<body>

<h1>Gemeente Dashboard</h1>

<h2>Verkiezingen beheren</h2>
<table>
    <tr><th>Titel</th><th>Start</th><th>Einde</th><th>Status</th><th>Actie</th></tr>
    <?php foreach ($elections as $e): ?>
    <tr>
        <td><?= htmlspecialchars($e['title']) ?></td>
        <td><?= $e['start_date'] ?></td>
        <td><?= $e['end_date'] ?></td>
        <td><?= $e['is_open'] ? 'Open' : 'Gesloten' ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="election_id" value="<?= $e['id'] ?>">
                <input type="hidden" name="is_open" value="<?= $e['is_open'] ?>">
                <button type="submit" name="toggle">
                    <?= $e['is_open'] ? 'Sluit' : 'Open' ?>
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Partijen goedkeuren</h2>
<table>
    <tr><th>Naam</th><th>Contactpersoon</th><th>Email</th><th>Status</th><th>Actie</th></tr>
    <?php foreach ($parties as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['contact_name']) ?></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
        <td><?= $p['approved'] ? 'Goedgekeurd' : 'In afwachting' ?></td>
        <td>
            <?php if (!$p['approved']): ?>
            <form method="post">
                <input type="hidden" name="party_id" value="<?= $p['id'] ?>">
                <button type="submit" name="approve_party">Goedkeuren</button>
            </form>
            <?php else: ?>
            ✔️
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<h2>Uitslagen bekijken</h2>
<table>
    <tr><th>Verkiezing</th><th>Partij</th><th>Aantal stemmen</th></tr>

    <?php
    // Haal gesloten verkiezingen op
    $closedElections = $pdo->query("
        SELECT id, title FROM elections WHERE is_open = 0
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($closedElections as $election) {
        // Haal stemmen per partij op
        $stmt = $pdo->prepare("
            SELECT pa.name AS partij, COUNT(v.id) AS stemmen
            FROM votes v
            JOIN candidates c ON v.candidate_id = c.id
            JOIN parties pa ON c.party_id = pa.id
            WHERE v.election_id = ?
            GROUP BY pa.id
            ORDER BY stemmen DESC
        ");
        $stmt->execute([$election['id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0): ?>
            <tr>
                <td colspan='3' style='background:#eee; font-weight:bold; text-align:left;'>
                    <?= htmlspecialchars($election['title']) ?>
                </td>
            </tr>
            <?php foreach ($results as $r): ?>
                <tr>
                    <td></td>
                    <td><?= htmlspecialchars($r['partij']) ?></td>
                    <td><?= $r['stemmen'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td><?= htmlspecialchars($election['title']) ?></td>
                <td colspan="2">Nog geen stemmen geregistreerd</td>
            </tr>
        <?php endif;
    }
    ?>
</table>

</body>
</html>
