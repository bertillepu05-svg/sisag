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

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_adm = trim($_POST["nom_adm"]);
    $prenom_adm = trim($_POST["prenom_adm"]);
    $sexe = trim($_POST["sexe"]);
    $email = trim($_POST["email"]);
    $mot_de_passe = trim($_POST["mot_de_passe"]);
    $telephone = trim($_POST["telephone"]);
    $statut = trim($_POST["statut"]);

    // Vérifier si l'email existe déjà
    $check_email = "SELECT id_adm FROM administrateur WHERE email = '$email'";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Un administrateur avec l'email <strong>$email</strong> existe déjà.";
        $_SESSION['form_data'] = $_POST;
        header("Location: gestion_adm.php");
        exit();
    }

    // Insertion du nouvel administrateur
    $insert = "INSERT INTO administrateur (nom_adm, prenom_adm, sexe, email, mot_de_passe, telephone, statut) 
               VALUES ('$nom_adm', '$prenom_adm', '$sexe', '$email', '$mot_de_passe', '$telephone', '$statut')";
    
    if ($conn->query($insert)) {
        $_SESSION['success_message'] = "Administrateur ajouté avec succès !";
        header("Location: gestion_adm.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'ajout : " . $conn->error;
        $_SESSION['form_data'] = $_POST;
        header("Location: gestion_adm.php");
        exit();
    }
} else {
    // Si accès direct sans POST
    header("Location: gestion_adm.php");
    exit();
}

$conn->close();
?>