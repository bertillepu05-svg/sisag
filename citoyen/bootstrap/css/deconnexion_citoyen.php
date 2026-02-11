<?php
// Démarrer la session citoyen
session_name('citoyen_session');
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sisag";

$conn = new mysqli($host, $user, $password, $dbname);
mysqli_set_charset($conn, "utf8");



// Détruire complètement la session admin
$_SESSION = array();

// Supprimer le cookie de session admin
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
$conn->close();

// Rediriger vers l'accueil
header("Location: accueil.php");
exit;
?>