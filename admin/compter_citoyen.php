<?php
    header('Content-Type: application/json');
    
    $conn = new mysqli("localhost", "root", "", "sisag");
    if($conn->connect_error){
        die(json_encode(["error" => "Connection failed"]));
    }
    
    // Compter le total général
    $sqlTotal = "SELECT COUNT(*) as total FROM citoyen";
    $resultTotal = $conn->query($sqlTotal);
    $total = $resultTotal->fetch_assoc()['total'];
    
    // Compter les actifs
    $sqlActif = "SELECT COUNT(*) as count FROM citoyen WHERE statut = 'Actif'";
    $resultActif = $conn->query($sqlActif);
    $actif = $resultActif->fetch_assoc()['count'];
    
    // Compter les inactifs
    $sqlInactif = "SELECT COUNT(*) as count FROM citoyen WHERE statut = 'Inactif'";
    $resultInactif = $conn->query($sqlInactif);
    $inactif = $resultInactif->fetch_assoc()['count'];
    
    echo json_encode([
        "total" => $total,
        "actif" => $actif,
        "inactif" => $inactif
    ]);
    
    $conn->close();
    ?>