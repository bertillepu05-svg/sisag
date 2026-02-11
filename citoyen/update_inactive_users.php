<?php
// update_inactive_users.php
$conn = new mysqli('localhost', 'root', '', 'sisag');

// Marquer comme inactif les utilisateurs sans activité depuis 5 minutes
$sql = "UPDATE citoyen 
        SET statut = 'Inactif' 
        WHERE statut = 'Actif' 
        AND (last_activity IS NULL OR last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE))";

$conn->query($sql);

// Nettoyer les tokens expirés
$sql2 = "UPDATE citoyen 
         SET remember_token = NULL, token_expiry = NULL 
         WHERE token_expiry IS NOT NULL 
         AND token_expiry < NOW()";
         
$conn->query($sql2);

$conn->close();
echo "Mise à jour des utilisateurs inactifs terminée.";
?>
