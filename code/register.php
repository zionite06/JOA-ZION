<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'burger')");
        $stmt->execute([$username, $password]);
        header("Location: login.php?registered=1");
        exit;
    } catch (PDOException $e) {
        echo "Fout bij registratie: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registratie - Electronisch Stemmen</title>
    <style>
        body { background-color: #f8f5ef; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; width: 300px; }
        input, button { width: 90%; padding: 8px; margin: 5px 0; }
    </style>
</head>
<body>
<div class="box">
    <h2>Registreren</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Gebruikersnaam" required><br>
        <input type="password" name="password" placeholder="Wachtwoord" required><br>
        <button type="submit">Registreren</button>
    </form>
    <p><a href="login.php">Al een account? Log in</a></p>
</div>
</body>
</html>
