<?php

header('Content-Type: text/html; charset=utf-8');

session_name('citoyen_session');
session_start();

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "sisag");
if($conn->connect_error){
    die("erreur".$conn->connect_error);
}
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['id_citoyen'])) {
    $_SESSION['error_message'] = "Connectez-vous pour pouvoir utiliser cette fonctionnalité";
    header("Location: liste_projet.php");
    exit;
}

$id_citoyen = $_SESSION['id_citoyen'];

// Récupérer l'ID du projet depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: liste_projet.php");
    exit;
}

$id_projet = $_GET['id'];

// VÉRIFICATION : Vérifier si le projet est déjà suivi
$sql_check = "SELECT * FROM projets_suivis WHERE id_citoyen = ? AND id_projet = ?";
$stmt_check = $conn->prepare($sql_check);
if ($stmt_check === false) {
    $_SESSION['error_message'] = "Échec de prepare() CHECK : " . $conn->error;
    header("Location: liste_projet.php");
    exit;
}
$stmt_check->bind_param("ii", $id_citoyen, $id_projet);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Le projet est déjà suivi
    $stmt_check->close();
    $conn->close();
    $_SESSION['error_message'] = "Vous suivez déjà ce projet !";
    header("Location: liste_projet.php");
    exit;
}
$stmt_check->close();


// Vérifier si le projet existe
$sqlSelect = "SELECT * FROM projet WHERE id_projet = ?";
$stmt = $conn->prepare($sqlSelect);
if ($stmt === false) {
    $_SESSION['error_message'] = "Échec de prepare() SELECT projet : " . $conn->error;
    header("Location: liste_projet.php");
    exit;
}

$stmt->bind_param("i", $id_projet);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $stmt->close();
    $conn->close();
    $_SESSION['error_message'] = "Projet introuvable.";
    header("Location: liste_projet.php");
    exit;
}

$projet = $res->fetch_assoc(); 
$stmt->close();

// Insérer dans projets suivis
$sqlInsert = "INSERT INTO projets_suivis (id_projet, id_citoyen, nom_projet, commune, quartier, ministere, budget, date_debut, date_fin, descript, objectif, statut, avancement, photo, date_suivi)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$ins = $conn->prepare($sqlInsert);
if ($ins === false) {
    $_SESSION['error_message'] = "Échec de prepare() INSERT : " . $conn->error;
    header("Location: liste_projet.php");
    exit;
}

$ins->bind_param(
    "iissssisssssss",
    $projet['id_projet'],    // ID du projet
    $id_citoyen,             // ID du citoyen 
    $projet['nom_projet'],
    $projet['commune'],
    $projet['quartier'],
    $projet['ministere'],
    $projet['budget'],
    $projet['date_debut'],
    $projet['date_fin'],
    $projet['descript'],
    $projet['objectif'],
    $projet['statut'],
    $projet['avancement'],
    $projet['photo']
);

if (!$ins->execute()) {
    $_SESSION['error_message'] = "Échec execute() INSERT : " . $ins->error;
    header("Location: liste_projet.php");
} else {
    // Redirection avec message de succès
    $_SESSION['success_message'] = "Projet ajouté avec succès";
    header("Location: liste_projet.php");
    exit();
}

$ins->close();
$conn->close();
?>

