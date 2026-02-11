<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DÉTERMINER LE NOM DE SESSION 

session_name('citoyen_session');
session_start(); 

$conn = new mysqli('localhost', 'root', '', 'sisag'); 
if ($conn->connect_error) { 
    die("Erreur de connexion : " . $conn->connect_error);
} 

if (isset($_POST['login'])) 
    { 
        $email = $_POST['email']; 
        $mot_de_passe = $_POST['mot_de_passe'];
        $sql = "SELECT * FROM citoyen WHERE email = ?"; 
        $stmt = $conn->prepare($sql); 
        $stmt->bind_param("s", $email); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 

        if ($result->num_rows >= 1) { 
        $user = $result->fetch_assoc(); 
            
            if ($mot_de_passe == $user['mot_de_passe']) { 
                // FORCER le statut à "Actif" à la connexion
                $update_status = "UPDATE citoyen SET statut = 'Actif' WHERE id_citoyen = ?";
                $stmt_update = $conn->prepare($update_status);
                $stmt_update->bind_param("i", $user['id_citoyen']);
                $stmt_update->execute();

                $_SESSION['id_citoyen'] = $user['id_citoyen'];
                header("refresh: 1; url=dashboard.php");
                exit; 
            } else { 
                $_SESSION['error_message'] = ('Mot de passe incorrect !');
                header('Location: login.php');
                exit;
            } 
        } else {
            $_SESSION['error_message'] = ("Inscrivez-vous d'abord.");
            header('Location: login.php');
        exit;
        }
    }
?> 