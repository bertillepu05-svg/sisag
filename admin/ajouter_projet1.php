<?php
session_name('admin_session');
session_start();

if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sisag";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur de connexion: " . $e->getMessage();
    header("Location: ajouter_projet.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajout'])) {
    // Récupération des données du formulaire
    $nom_projet = $_POST['nom_projet'];
    $commune = $_POST['commune'];
    $quartier = $_POST['quartier'];
    $ministere = $_POST['ministere'];
    $budget = $_POST['budget'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $photo = $_POST['date_fin'];
    $descript = $_POST['descript'];
    $objectif = $_POST['objectif'];
    $id_adm = $_SESSION['id_adm'];

    
    // Calcul automatique du statut et de l'avancement
    $aujourdhui = date('Y-m-d');
    $statut = '';
    $avancement = 0;
    
    if ($aujourdhui < $date_debut) {
        $statut = 'À venir';
        $avancement = 0;
    } elseif ($aujourdhui >= $date_debut && $aujourdhui <= $date_fin) {
        $statut = 'En cours';
        $avancement = 0;
    } elseif ($aujourdhui > $date_fin) {
        $statut = 'Terminé';
        $avancement = 100;
    }
    
    try {
        // Gestion de la photo 
        if (!empty($_FILES['photo']['name'])) {
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $nouveau_nom = "photo_" .time(). "." . $extension;
            $destination = "../photos/projet/" . $nouveau_nom;
            move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
        }
        // Insertion dans la base de données
        $sql = "INSERT INTO projet (nom_projet, commune, quartier, ministere, budget, date_debut, date_fin, descript, objectif, statut, avancement, photo, id_adm) 
                VALUES (:nom_projet, :commune, :quartier, :ministere, :budget, :date_debut, :date_fin, :descript, :objectif, :statut, :avancement, :photo, :id_adm)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nom_projet', $nom_projet);
        $stmt->bindParam(':commune', $commune);
        $stmt->bindParam(':quartier', $quartier);
        $stmt->bindParam(':ministere', $ministere);
        $stmt->bindParam(':budget', $budget);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);
        $stmt->bindParam(':descript', $descript);
        $stmt->bindParam(':objectif', $objectif);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':avancement', $avancement);
        $stmt->bindParam(':photo', $destination);

        $stmt->bindParam(':id_adm', $id_adm);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Projet ajouté avec succès! Statut automatique: <strong>$statut</strong>";
            
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'ajout du projet";
        }
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
    }
    
    header("Location: ajouter_projet.php");
    exit;
}
?>