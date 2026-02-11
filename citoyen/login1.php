<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('citoyen_session');
session_start();

$conn = new mysqli('localhost', 'root', '', 'sisag');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $remember = isset($_POST['remember']) ? 1 : 0;
    
    $sql = "SELECT * FROM citoyen WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows >= 1) {
        $user = $result->fetch_assoc();
        
        // COMPARAISON DU MOT DE PASSE (en clair dans ton code)
        if ($mot_de_passe == $user['mot_de_passe']) {
            
            // Mettre à jour le statut et la dernière activité
            $update_status = "UPDATE citoyen SET statut = 'Actif', last_activity = NOW() WHERE id_citoyen = ?";
            $stmt_update = $conn->prepare($update_status);
            $stmt_update->bind_param("i", $user['id_citoyen']);
            $stmt_update->execute();
            
            // Stocker l'ID en session
            $_SESSION['id_citoyen'] = $user['id_citoyen'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
           // Gestion du "Se souvenir de moi"
            if ($remember == 1) {
                // Créer un token unique
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
                
                // Préparer les variables pour bind_param
                $id_citoyen = $user['id_citoyen']; // Variable pour l'ID
                $token_expiry_date = date('Y-m-d H:i:s', $expiry); // Variable pour la date
                
                // Stocker le token dans la base
                $sql_token = "UPDATE citoyen SET remember_token = ?, token_expiry = ? WHERE id_citoyen = ?";
                $stmt_token = $conn->prepare($sql_token);
                $stmt_token->bind_param("ssi", $token, $token_expiry_date, $id_citoyen);
                $stmt_token->execute();
                
                // Créer le cookie
                setcookie('remember_citoyen', $token, $expiry, "/", "", false, true);
            }

            
            header("refresh: 1; url=dashboard.php");
            exit;
            
        } else {
            $_SESSION['error_message'] = 'Mot de passe incorrect !';
            header('Location: login.php');
            exit;
        }
        
    } else {
        $_SESSION['error_message'] = "Inscrivez-vous d'abord.";
        header('Location: login.php');
        exit;
    }
}
?>
