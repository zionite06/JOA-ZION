<?php
session_start();

// Controleer of iemand is ingelogd
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Doorverwijzen op basis van rol
switch ($_SESSION['role']) {
    case 'burger':
        header("Location: burger.php");
        break;
    case 'gemeente':
        header("Location: gemeente.php");
        break;
    case 'partij':
        header("Location: partij.php");
        break;
    default:
        // Onbekende rol, uitloggen voor de zekerheid
        session_destroy();
        header("Location: login.php");
        break;
}
exit;
