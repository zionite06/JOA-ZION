<?php
require 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {
            case 'gemeente': header("Location: gemeente.php"); break;
            case 'partij': header("Location: partij.php"); break;
            default: header("Location: burger.php");
        }
        exit;
    } else {
        $error = "Onjuiste gebruikersnaam of wachtwoord";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen - Electronisch Stemmen</title>
    <style>
        body { background-color: #f8f5ef; font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; width: 300px; }
        input, button { width: 90%; padding: 8px; margin: 5px 0; }
    </style>
</head>
<body>
<div class="box">
    <h2>Inloggen</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($_GET['registered'])) echo "<p style='color:green;'>Registratie geslaagd! Log nu in.</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Gebruikersnaam" required><br>
        <input type="password" name="password" placeholder="Wachtwoord" required><br>
        <button type="submit">Inloggen</button>
    </form>
    <p><a href="register.php">Nog geen account? Registreer hier</a></p>
</div>
</body>
</html>
