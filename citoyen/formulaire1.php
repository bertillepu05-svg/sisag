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
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $commune = $_POST['commune'] ?? '';
    $quartier = $_POST['quartier'] ?? '';
    $profession = $_POST['profession'] ?? '';
    
    $erreurs = [];
    
    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($telephone)) {
        $erreurs[] = "Tous les champs obligatoires doivent être remplis";
    }
    
    if ($mot_de_passe != $mot_de_passe_confirm) {
        $erreurs[] = "Erreur: le mot de passe est différent du mot de passe confirmé";
    }
    
    // Vérifier si l'email est déjà utilisé
    if (empty($erreurs)) {
        $sql_check_citoyen = "SELECT * FROM citoyen WHERE email = ?";
        $stmt = $conn->prepare($sql_check_citoyen);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result_citoyen = $stmt->get_result();
        
        if ($result_citoyen->num_rows > 0) {
            $erreurs[] = "Vous êtes déjà inscrit. Connectez-vous avec vos identifiants.";
        }
    }
    
    if (empty($erreurs)) {
        $photo_path = null;
        
        // Gestion de la photo
        if (!empty($_FILES['photo1']['name'])) {
            $extension = pathinfo($_FILES['photo1']['name'], PATHINFO_EXTENSION);
            $nouveau_nom = "photo_" . time() . "." . $extension;
            $destination = "../photos/citoyen/" . $nouveau_nom;
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists("../photos/citoyen/")) {
                mkdir("../photos/citoyen/", 0777, true);
            }
            
            if (move_uploaded_file($_FILES['photo1']['tmp_name'], $destination)) {
                $photo_path = $destination;
            } else {
                $photo_path = null; // Photo optionnelle, on continue sans
            }
        }
        
        // Vérifier la structure de la table citoyen
        // Supposons que votre table a ces colonnes (ajustez selon votre structure réelle) :
        // photo1, nom, prenom, sexe, email, mot_de_passe, telephone, commune, quartier, profession, statut, date_signUp
        
        // Si la colonne statut existe, définissez une valeur par défaut
        $statut = 'Inactif'; // ou la valeur par défaut de votre table
        
        // REQUÊTE SQL CORRIGÉE :
        // 1. Comptez le nombre de paramètres dans VALUES
        // 2. Assurez-vous que le nombre de "?" correspond au nombre de variables bindées
        
        if ($photo_path) {
            $sql_insert = "INSERT INTO citoyen (photo1, nom, prenom, sexe, email, mot_de_passe, telephone, commune, quartier, profession, statut, date_signUp) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        } else {
            $sql_insert = "INSERT INTO citoyen (nom, prenom, sexe, email, mot_de_passe, telephone, commune, quartier, profession, statut, date_signUp) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        }
        
        $stmt = $conn->prepare($sql_insert);
        
        if ($stmt) {
            // Hasher le mot de passe pour la sécurité

            
            if ($photo_path) {
                $stmt->bind_param("sssssssssss", 
                    $photo_path,    // photo1
                    $nom,           // nom
                    $prenom,        // prenom
                    $sexe,          // sexe
                    $email,         // email
                    $mot_de_passe, // mot_de_passe
                    $telephone,     // telephone
                    $commune,       // commune
                    $quartier,      // quartier
                    $profession,    // profession
                    $statut         // statut
                );
            } else {
                $stmt->bind_param("ssssssssss", 
                    $nom,           // nom
                    $prenom,        // prenom
                    $sexe,          // sexe
                    $email,         // email
                    $mot_de_passe, // mot_de_passe
                    $telephone,     // telephone
                    $commune,       // commune
                    $quartier,      // quartier
                    $profession,    // profession
                    $statut         // statut
                );
            }
            
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
        
        // Stocker les données du formulaire pour les réafficher
        $_SESSION['form_data'] = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'commune' => $commune,
            'quartier' => $quartier,
            'profession' => $profession
        ];
        
        header("Location: formulaire.php");
        exit();
    }
} else {
    // Si quelqu'un accède directement à formulaire1.php sans POST
    header("Location: formulaire.php");
    exit();
}
?>

