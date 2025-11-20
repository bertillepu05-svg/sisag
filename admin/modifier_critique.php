<?php
header('Content-Type: text/html; charset=utf-8');
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
    die("Erreur de connexion: " . $e->getMessage());
}

// Récupérer l'ID du projet depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Aucun projet sélectionné";
    header("Location: liste_projet_admin.php");
    exit;
}

$id_projet = $_GET['id'];

// Récupérer les données du projet
try {
    $sql = "SELECT * FROM projet WHERE id_projet = :id_projet";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_projet', $id_projet);
    $stmt->execute();
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$projet) {
        $_SESSION['error_message'] = "Projet non trouvé";
        header("Location: liste_projet_admin.php");
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
    header("Location: liste_projet_admin.php");
    exit;
}


// Traitement du formulaire de mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    $nouveau_statut = $_POST['statut'];
    $nouvel_avancement = $_POST['avancement'];
    $aujourdhui = date('Y-m-d');
    $erreurs = [];
    
    // Vérifier la cohérence avancement/statut
    if ($nouveau_statut == 'Terminé' && $nouvel_avancement != 100) {
        $erreurs[] = "Un projet terminé doit avoir un avancement de 100%";
    }
    // Si pas d'erreurs, mettre à jour
    if (empty($erreurs)) {
        try {
            $sql = "UPDATE projet SET statut = :statut, avancement = :avancement WHERE id_projet = :id_projet";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':statut', $nouveau_statut);
            $stmt->bindParam(':avancement', $nouvel_avancement);
            $stmt->bindParam(':id_projet', $id_projet);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Projet mis à jour avec succès!";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la mise à jour";
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        }
    }else {
        $_SESSION['error_message'] = implode("<br>", $erreurs);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le projet</title>
    <link rel="stylesheet" href="destail_projet.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body{
             background-color : #f8f9fa;
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
        
        .alert-warning-custom {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 4px solid #ffc107;
            color: #856404;
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
        h3{
            font-family: 'Times New Roman', Times, serif;
        }
        .sidebar .icone{
            font-weight:bold;
            font-size:20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
            margin-left:0.25rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .photo {
        height: 250px;
        object-fit: cover;
        width: 90%;
        border-bottom: 3px solid var(--light);
        }
        label{
            font-weight:bold;
        }
        .project-info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .phase-info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .phase-item {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd !important;
        }

        .phase-item:hover {
            background-color: #e9ecef;
        }

        .img-thumbnail {
            transition: transform 0.2s;
        }

        .img-thumbnail:hover {
            transform: scale(1.1);
        }

        .form-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        /* Styles pour la pagination des phases */
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            font-size: 0.9rem;
        }

        .pagination .page-item.active .page-link {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            font-weight: bold;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
        }

        .phase-navigation {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

    </style>
</head>
<body>
     <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 p-0 sidebar bg-dark text-white">
                <div class="d-flex p-3 icone">
                    <i class="fas fa-chart-line me-2"></i>SISAG
                </div>
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard_admin.php" data-page="dashboard">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="liste_projet_admin.php" data-page="projects">
                                <i class="fas fa-list"></i> Liste des projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ajouter_projet.php" data-page="projects">
                                <i class="fas fa-plus-circle"></i> Ajouter un projet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="projet_critique.php" data-page="projects">
                                <i class="fas fa-list"></i> Projets critiques
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="projet_avenir.php" data-page="projects">
                                <i class="fas fa-list"></i> Projets à venir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_adm.php" data-page="projects">
                                <i class="fas fa-list"></i> Gestion des administrateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="commentaire.php" data-page="projects">
                                <i class="fas fa-list"></i> Commentaires
                            </a>
                        </li>
                        <li class="nav-item admin-only">
                            <a class="nav-link" href="deconnexion_admin.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Contenu droit -->
            <div class="col-lg-10 col-md-9 p-4">
                <!-- Messages d'alerte -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger-custom alert-custom alert-dismissible fade show mx-auto mt-4" style="max-width: 95%;" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                            <div>
                                <h5 class="alert-heading mb-2">Erreur</h5>
                                <div class="mb-0"><?php echo $_SESSION['error_message']; ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success-custom alert-custom alert-dismissible fade show mx-auto mt-4" style="max-width: 95%;" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill alert-icon"></i>
                            <div>
                                <h5 class="alert-heading mb-2">Succès</h5>
                                <div class="mb-0"><?php echo $_SESSION['success_message']; ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <!-- En tête -->
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>Modifier le projet</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="navbar-text" style="font-size : 20px;">
                        <i class="bi bi-person-gear"></i> Espace administrateur
                        </span>
                    </div>
                </div>
                <hr>

                <!-- Informations du projet -->
                <div class="project-info-card">
                    <h5 class="card-title">Informations du projet</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom :</strong> <?php echo htmlspecialchars($projet['nom_projet']); ?></p>
                            <p><strong>Ministère :</strong> <?php echo htmlspecialchars($projet['ministere']); ?></p>
                            <p><strong>Date début :</strong> <?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Localisation :</strong> <?php echo htmlspecialchars($projet['commune']); ?> - <?php echo htmlspecialchars($projet['quartier']); ?></p>
                            <p><strong>Date fin :</strong> <?php echo date('d/m/Y', strtotime($projet['date_fin'])); ?></p>
                            <p><strong>Statut actuel :</strong> 
                                <span class="badge 
                                    <?php 
                                    switch($projet['statut']) {
                                        case 'À venir': echo 'bg-secondary'; break;
                                        case 'En cours': echo 'bg-primary'; break;
                                        case 'Terminé': echo 'bg-success'; break;
                                        case 'En retard': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                    ?> status-badge">
                                    <?php echo $projet['statut']; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de modification -->
                <div class="card form-container">
                    <div class="card-body">
                        <form action="update.php?id=<?php echo $id_projet; ?>" method="POST" id="updateForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="statut" class="form-label">Nouveau statut</label>
                                    <select class="form-select" name="statut" id="statut" required onchange="gererChangementStatut()">
                                        <option value="Terminé" <?php echo ($projet['statut'] == 'Terminé') ? 'selected' : ''; ?>>Terminé</option>
                                    </select>
                                    <small class="form-text text-muted" id="aide-statut"></small>
                                </div>
                                <div class="col-md-6">
                                    <label for="avancement" class="form-label">Avancement (%)</label>
                                    <input type="number" class="form-control" name="avancement" id="avancement" 
                                           value="<?php echo $projet['avancement']; ?>" min="0" max="100" required>
                                    <small class="form-text text-muted" id="aide-avancement"></small>
                                </div>
                            </div>
                            
                            <div class="alert alert-info" id="message-automatique" style="display: none;">
                                <i class="bi bi-info-circle"></i>
                                <span id="texte-message"></span>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="modifier" class="btn btn-primary">Mettre à jour</button>
                                <a href="liste_projet_admin.php" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>

    <script>
        function gererChangementStatut() {
            const statutSelect = document.getElementById('statut');
            const avancementInput = document.getElementById('avancement');
            const messageDiv = document.getElementById('message-automatique');
            const texteMessage = document.getElementById('texte-message');
            const aideAvancement = document.getElementById('aide-avancement');
            const aideStatut = document.getElementById('aide-statut');
            
            const nouveauStatut = statutSelect.value;
            const ancienStatut = "<?php echo $projet['statut']; ?>";
            
            // Masquer le message par défaut
            messageDiv.style.display = 'none';
            
            // Logique selon les règles métier
            if (ancienStatut === 'En cours' && nouveauStatut === 'Terminé') {
                // Forcer l'avancement à 100%
                avancementInput.value = 100;
                avancementInput.readOnly = true;
                texteMessage.textContent = "Le statut passe à 'Terminé', l'avancement est automatiquement fixé à 100%";
                messageDiv.style.display = 'block';
                aideAvancement.textContent = "Avancement verrouillé à 100% pour un projet terminé";
            } else if (nouveauStatut === 'Terminé') {
                avancementInput.readOnly = true;
                avancementInput.value = 100;
                texteMessage.textContent = "Un projet terminé doit avoir un avancement de 100%";
                messageDiv.style.display = 'block';
                aideAvancement.textContent = "Avancement verrouillé à 100% pour un projet terminé";
            } else {
                avancementInput.readOnly = false;
                aideAvancement.textContent = "Saisir l'avancement entre 0% et 100%";
            }
            
            // Aide contextuelle pour le statut
            switch(nouveauStatut) {
                case 'À venir':
                    aideStatut.textContent = "Projet programmé pour le futur";
                    break;
                case 'En cours':
                    aideStatut.textContent = "Projet en cours d'exécution";
                    break;
                case 'Terminé':
                    aideStatut.textContent = "Projet achevé";
                    break;
                case 'En retard':
                    aideStatut.textContent = "Projet en retard sur le planning";
                    break;
                default:
                    aideStatut.textContent = "Sélectionnez le nouveau statut du projet";
            }
        }
        
        // Initialisation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            gererChangementStatut();
        });
        
        function closeAlert(element) {
            element.closest('.alert').remove();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>