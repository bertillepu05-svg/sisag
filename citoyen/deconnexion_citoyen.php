<?php
session_name('citoyen_session');
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sisag";
$conn = new mysqli($host, $user, $password, $dbname);
mysqli_set_charset($conn, "utf8");

if (isset($_SESSION['id_citoyen'])) {
    // Mettre à jour le statut et supprimer le token
    $update_status = "UPDATE citoyen SET statut = 'Inactif', remember_token = NULL, token_expiry = NULL WHERE id_citoyen = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $_SESSION['id_citoyen']);
    $stmt->execute();
}

// Supprimer le cookie "remember"
if (isset($_COOKIE['remember_citoyen'])) {
    setcookie('remember_citoyen', '', time() - 3600, "/");
}

// Détruire la session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
$conn->close();

header("Location: accueil.php");
exit;
?>

