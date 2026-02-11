<?php
header('Content-Type: application/json; charset=utf-8');

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "sisag");
if($conn->connect_error){
    die(json_encode(['error' => 'Erreur de connexion']));
}
$conn->set_charset("utf8mb4");

// Récupérer les filtres
$commune_filter = isset($_GET['commune']) ? $conn->real_escape_string($_GET['commune']) : '';
$statut_filter = isset($_GET['statut']) ? $conn->real_escape_string($_GET['statut']) : '';
$ministere_filter = isset($_GET['ministere']) ? $conn->real_escape_string($_GET['ministere']) : '';
$recherche_filter = isset($_GET['recherche']) ? $conn->real_escape_string($_GET['recherche']) : '';

// Construire la requête SQL avec les filtres
$sql_where = "WHERE 1=1";

if (!empty($commune_filter)) {
    $sql_where .= " AND commune = '$commune_filter'";
}

if (!empty($statut_filter)) {
    $sql_where .= " AND statut = '$statut_filter'";
}

if (!empty($ministere_filter)) {
    $sql_where .= " AND ministere = '$ministere_filter'";
}

if (!empty($recherche_filter)) {
    $sql_where .= " AND (nom_projet LIKE '%$recherche_filter%' OR commune LIKE '%$recherche_filter%' OR ministere LIKE '%$recherche_filter%')";
}

// Récupérer TOUS les projets
$sql_projets = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, date_debut, date_fin, avancement
                FROM projet
                $sql_where
                ORDER BY id_projet ASC";

$result_projets = $conn->query($sql_projets);

$projets = [];
while($ligne = $result_projets->fetch_assoc()) {
    $projets[] = $ligne;
}

echo json_encode($projets);
$conn->close();
?>

