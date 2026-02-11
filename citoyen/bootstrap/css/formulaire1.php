<?php

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

session_start();

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'sisag');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'];
    $telephone = $_POST['telephone'];
    $profession = $_POST['profession'];
    
    $erreurs = [];
    
    if ($mot_de_passe != $mot_de_passe_confirm) {
        $erreurs[] = "Erreur: le mot de passe est différent du mot de passe confirmé";
    }

    // Vérifier si le citoyen n'est pas déjà inscrit
    $sql_check_citoyen = "SELECT * FROM citoyen WHERE email = ?";
    $stmt = $conn->prepare($sql_check_citoyen);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_citoyen = $stmt->get_result();
    
    if ($result_citoyen->num_rows > 0) {
        $erreurs[] = "Vous êtes déjà inscrit. Connectez-vous avec vos identifiants.";
    }

    if (empty($erreurs)) {
        // Utilisation de requêtes préparées pour la sécurité
        $sql_insert = "INSERT INTO citoyen (nom, prenom, sexe, email, mot_de_passe, telephone, profession)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql_insert);
        if ($stmt) {
            $stmt->bind_param("sssssss", $nom, $prenom, $sexe, $email, $mot_de_passe, $telephone, $profession);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Vous êtes inscrit avec succès!";
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                header("Location: formulaire.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'insertion: " . $stmt->error;
                header("Location: formulaire.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Erreur de préparation de la requête: " . $conn->error;
            header("Location: formulaire.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $erreurs);
        header("Location: formulaire.php");
        exit();
    }
} else {
    // Si quelqu'un accède directement à formulaire1.php sans POST
    header("Location: formulaire.php");
    exit();
}
?>
