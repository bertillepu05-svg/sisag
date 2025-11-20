<?php
// Démarrer la session admin
session_name('admin_session');
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sisag";

$conn = new mysqli($host, $user, $password, $dbname);
mysqli_set_charset($conn, "utf8");

if (isset($_SESSION['id_adm'])) {
    // Mettre à jour le statut
    $update_status = "UPDATE administrateur SET statut = 'Inactif' WHERE id_adm = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $_SESSION['id_adm']);
    $stmt->execute();
}

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

// Rediriger vers la page de connexion
header("Location: login.php");
exit;
?>