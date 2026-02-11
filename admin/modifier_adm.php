<?php

header('Content-Type: text/html; charset=utf-8');

session_name('admin_session');
session_start();

if (!isset($_SESSION['id_adm'])) {
    header("Location: accueil.php");
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

// Vérifier si un ID est passé
if (!isset($_GET['id'])) {
    
    $_SESSION['error_message'] = ("Aucun adm sélectionné !");
    header("Location: gestion_adm.php");
}

$id = intval($_GET['id']);


// Récupération des données de l'electeur
$sql = "SELECT * FROM administrateur WHERE id_adm = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = ("Administrateur introuvable !");
    header("Location: gestion_adm.php");
}

$adm = $result->fetch_assoc();
$stmt->close();


/// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_adm = trim($_POST["nom_adm"]);
    $prenom_adm = trim($_POST["prenom_adm"]);
    $sexe = trim($_POST["sexe"]);
    $email = trim($_POST["email"]);
    $mot_de_passe = trim($_POST["mot_de_passe"]); 
    $telephone = trim($_POST["telephone"]);
    $statut = trim($_POST["statut"]);

    // EMPÊCHER UN ADMINISTRATEUR DE SE METTRE LUI-MÊME EN "INACTIF"
    if ($id == $id_adm && $statut == "Inactif") {
        $_SESSION['error_message'] =  "Vous ne pouvez pas désactiver votre propre compte !";
    } else {
        // Si le mot de passe n'est pas modifié, garder l'ancien
        if (empty($mot_de_passe)) {
            $mot_de_passe = $adm['mot_de_passe'];
        }

        // Utiliser prepared statements pour éviter les injections SQL
        $update = "UPDATE administrateur 
                   SET nom_adm = ?, prenom_adm = ?, sexe = ?, email = ?, mot_de_passe = ?, telephone = ?, statut = ?
                   WHERE id_adm = ?";
        
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sssssssi", $nom_adm, $prenom_adm, $sexe, $email, $mot_de_passe, $telephone, $statut, $id);

        if ($stmt->execute()) {
            $_SESSION['success_message']  = "Administrateur mis à jour avec succès !";
            
            // Actualiser les données affichées
            $sql = "SELECT * FROM administrateur WHERE id_adm = ?";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $adm = $result->fetch_assoc();
            $stmt2->close();
        } else {
            $_SESSION['error_message'] =  "Erreur lors de la mise à jour : " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Modifier adm</title>
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        
        /* Styles pour les messages d'alerte personnalisés */
        .alert-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: slideInDown 0.5s ease-out;
        }
        
        .alert-success-custom {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .alert-danger-custom {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .alert-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        
        .btn-close-custom {
            background: transparent;
            border: none;
            font-size: 1.2rem;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .btn-close-custom:hover {
            opacity: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .form-container {
                width: 70% !important;
            }
        }
        
        @media (max-width: 992px) {
            .form-container {
                width: 80% !important;
            }
        }
        
        @media (max-width: 768px) {
            .form-container {
                width: 90% !important;
            }
            
            .form-body .row {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            .form-group {
                width: 100%;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            .col-xl-6, .col-md-12 {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            .d-flex.justify-content-center {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .form-container {
                width: 95% !important;
                margin-top: 2rem !important;
            }
            
            .form-header {
                font-size: 0.9rem;
                padding: 0.5rem !important;
            }
            
            .form-body {
                padding: 0.5rem;
            }
            
            .form-label {
                font-size: 0.9rem;
            }
            
            .form-control, .form-select {
                font-size: 0.9rem;
                padding: 0.375rem 0.75rem;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid px-3 px-md-4 px-lg-5">
        <!-- Messages d'alerte -->
        <?php
        // Afficher les messages d'erreur
        if (isset($_SESSION['error_message'])):
        ?>
            <div class="alert alert-danger-custom alert-custom alert-dismissible fade show mx-auto mt-3 mt-md-4" style="max-width: 100%;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                    <div class="w-100">
                        <h5 class="alert-heading mb-2">Erreur d'inscription</h5>
                        <div class="mb-0"><?php echo $_SESSION['error_message']; ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-2 m-md-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <?php
            // Supprimer le message après affichage
            unset($_SESSION['error_message']);
        endif;
        
        // Afficher les messages de succès
        if (isset($_SESSION['success_message'])):
        ?>
            <div class="alert alert-success-custom alert-custom alert-dismissible fade show mx-auto mt-3 mt-md-4" style="max-width: 100%;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill alert-icon"></i>
                    <div class="w-100">
                        <h5 class="alert-heading mb-2">Inscription réussie !</h5>
                        <div class="mb-0"><?php echo $_SESSION['success_message']; ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-2 m-md-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <?php
            // Supprimer le message après affichage
            unset($_SESSION['success_message']);
        endif;
        ?>
        
        <div class="form-container pb-4 w-75 w-lg-50 mx-auto rounded-3 mt-4 mt-md-5" style="box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
            <div class="form-header rounded-top pt-2 pb-1 ps-3 bg-dark text-white">
                <p><i class="bi bi-person-plus-fill me-2"></i>Modier un administrateur</p>
            </div>
            
            <div class="form-body px-3 px-md-4">
            <form action="modifier_adm.php?id=<?php echo $id; ?>" method="POST" id="inscriptionForm">
                    <!-- Informations personnelles -->
                    <div class="section-title">
                        <i class="bi bi-person-vcard me-2"></i>Informations Personnelles
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 form-group mt-3">
                            <label class="form-label required">Nom</label>
                            <input type="text" name="nom_adm" class="form-control" value="<?= htmlspecialchars($adm['nom_adm']) ?>" required>
                        </div>
                        <div class="col-md-4 form-group mt-3">
                            <label class="form-label required">Prénom</label>
                            <input type="text" name="prenom_adm" class="form-control" value="<?= htmlspecialchars($adm['prenom_adm']) ?>" required>
                        </div>
                        <div class="col-md-4 form-group mt-3">
                            <label class="form-label required">Sexe</label>
                            <select name="sexe" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="M" <?= $adm['sexe'] == 'M' ? 'selected' : '' ?>>Masculin</option>
                                <option value="F" <?= $adm['sexe'] == 'F' ? 'selected' : '' ?>>Féminin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mt-3">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($adm['email']) ?>" required>
                        </div>
                        <div class="col-md-4 form-group mt-3">
                            <label class="form-label">Mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" name="mot_de_passe" class="form-control" placeholder="Nouveau mot de passe">
                        </div>
                        <div class="col-md-4 form-group mt-3">
                            <label class="form-label required">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($adm['telephone'])?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mt-3">
                            <label class="form-label">Statut (automatique)</label>
                            <input type="text" class="form-control" 
                                value="<?= $adm['statut'] ?> (géré automatiquement)" readonly>
                            <input type="hidden" name="statut" value="<?= $adm['statut'] ?>">
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                   <div class="d-flex justify-content-between mt-4">
                        <a href="gestion_adm.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left-circle"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Validation basique du formulaire
        document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
            const telephone = document.querySelector('input[name="telephone"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const mot_de_passe = document.querySelector('input[name="mot_de_passe"]').value;
            const mot_de_passe_confirm = document.querySelector('input[name="mot_de_passe_confirm"]').value;
            
            // Validation basique du téléphone
            if (!telephone.match(/^\+?[\d\s-]{10,}$/)) {
                alert('Veuillez entrer un numéro de téléphone valide');
                e.preventDefault();
                return;
            }
            
            // Validation basique de l'email
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                alert('Veuillez entrer une adresse email valide');
                e.preventDefault();
                return;
            }
            
            // Validation des mots de passe
            if (mot_de_passe !== mot_de_passe_confirm) {
                alert('Les mots de passe ne correspondent pas');
                e.preventDefault();
                return;
            }
        });
        
        // Fonction pour fermer les alertes
        function closeAlert(button) {
            const alert = button.closest('.alert');
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
        
        // Auto-fermeture des alertes après 5 secondes
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>