<?php
// check_session.php - Version corrigée
session_name('citoyen_session');
session_start();

$conn = new mysqli('localhost', 'root', '', 'sisag');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if (isset($_SESSION['id_citoyen'])) {
    // Mettre à jour la dernière activité à CHAQUE page
    $update_activity = "UPDATE citoyen SET last_activity = NOW() WHERE id_citoyen = ?";
    $stmt = $conn->prepare($update_activity);
    $stmt->bind_param("i", $_SESSION['id_citoyen']);
    $stmt->execute();
    
    // Tu peux garder le statut 'Actif' aussi si tu veux
    $update_status = "UPDATE citoyen SET statut = 'Actif' WHERE id_citoyen = ?";
    $stmt2 = $conn->prepare($update_status);
    $stmt2->bind_param("i", $_SESSION['id_citoyen']);
    $stmt2->execute();
}


// Vérifier si l'utilisateur a une session
if (isset($_SESSION['id_citoyen'])) {
    // Mettre à jour la dernière activité
    $update = "UPDATE citoyen SET last_activity = NOW() WHERE id_citoyen = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("i", $_SESSION['id_citoyen']);
    $stmt->execute();
    
    // Vérifier si inactif depuis plus de 30 minutes (timeout automatique)
    $check = "SELECT TIMESTAMPDIFF(MINUTE, last_activity, NOW()) as diff FROM citoyen WHERE id_citoyen = ?";
    $stmt2 = $conn->prepare($check);
    $stmt2->bind_param("i", $_SESSION['id_citoyen']);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['diff'] > 30) { // 30 minutes d'inactivité
        // Marquer comme inactif et détruire la session
        $update_status = "UPDATE citoyen SET statut = 'Inactif', remember_token = NULL, token_expiry = NULL WHERE id_citoyen = ?";
        $stmt3 = $conn->prepare($update_status);
        $stmt3->bind_param("i", $_SESSION['id_citoyen']);
        $stmt3->execute();
        
        // Supprimer le cookie "remember"
        if (isset($_COOKIE['remember_citoyen'])) {
            setcookie('remember_citoyen', '', time() - 3600, "/");
        }
        
        session_destroy();
    }
}
// Vérifier le cookie "remember" si pas de session
elseif (isset($_COOKIE['remember_citoyen'])) {
    $token = $_COOKIE['remember_citoyen'];
    
    $sql = "SELECT id_citoyen, email, token_expiry FROM citoyen WHERE remember_token = ? AND token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Recréer la session
        $_SESSION['id_citoyen'] = $user['id_citoyen'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['last_activity'] = time();
        
        // Mettre à jour le statut
        $update = "UPDATE citoyen SET statut = 'Actif', last_activity = NOW() WHERE id_citoyen = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("i", $user['id_citoyen']);
        $stmt2->execute();
    } else {
        // Token invalide, supprimer le cookie
        setcookie('remember_citoyen', '', time() - 3600, "/");
    }
}

$conn->close();

// Fonction pour vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['id_citoyen']);
}

// Fonction pour rediriger vers login si non connecté (à appeler dans les pages qui nécessitent connexion)
function verifierConnexion($page_redirection = 'login.php') {
    if (!estConnecte()) {
        $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette fonctionnalité.";
        header("Location: $page_redirection");
        exit;
    }
}
?>