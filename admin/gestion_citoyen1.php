<?php
header('Content-Type: text/html; charset=utf-8');
session_name('admin_session');
session_start();

if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}

$id_adm = $_SESSION['id_adm'];

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sisag";

$conn = new mysqli($host, $user, $password, $dbname);
mysqli_set_charset($conn, "utf8");

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");

$conn->set_charset("utf8mb4");

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $telephone = $_POST['telephone'];
    $commune = $_POST['commune'];
    $quartier = $_POST['quartier'];
    $profession = $_POST['profession'];
    $statut = $_POST['statut'];

    // Vérifier si l'email existe déjà
    $check_email = "SELECT id_citoyen FROM citoyen WHERE email = '$email'";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Un citoyen avec l'email <strong>$email</strong> existe déjà.";
        $_SESSION['form_data'] = $_POST;
        header("Location: gestion_citoyen.php");
        exit();
    }

     // Gestion de la photo 
     if (!empty($_FILES['photo1']['name'])) {
        $extension = pathinfo($_FILES['photo1']['name'], PATHINFO_EXTENSION);
        $nouveau_nom = "photo_" .time(). "." . $extension;
        $destination = "../photos/citoyen/" . $nouveau_nom;
        move_uploaded_file($_FILES['photo1']['tmp_name'], $destination);
    }


    // Insertion du nouveau citoyen
    $insert = "INSERT INTO citoyen (photo1,nom, prenom, sexe, email, mot_de_passe, telephone, commune, quartier, profession, statut, date_signUp)
               VALUES ('$destination','$nom', '$prenom', '$sexe', '$email', '$mot_de_passe', '$telephone', '$commune', '$quartier', '$profession', '$statut', NOW())";
    if ($conn->query($insert)) {
        $_SESSION['success_message'] = "citoyen ajouté avec succès !";
        header("Location: gestion_citoyen.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'ajout : " . $conn->error;
        $_SESSION['form_data'] = $_POST;
        header("Location: gestion_citoyen.php");
        exit();
    }
} else {
    // Si accès direct sans POST
    header("Location: gestion_citoyen.php");
    exit();
}

$conn->close();
?>