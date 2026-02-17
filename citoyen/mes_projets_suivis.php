<?php
header('Content-Type: text/html; charset=utf-8');


require_once 'check_session.php';
// Vérifier que l'utilisateur est connecté
verifierConnexion();

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

// Traitement de l'arrêt du suivi
if (isset($_GET['arreter_suivi']) && !empty($_GET['arreter_suivi'])) {
    $id_projet = $_GET['arreter_suivi'];
    
    $sql_delete = "DELETE FROM projets_suivis WHERE id_citoyen = ? AND id_projet = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $id_citoyen, $id_projet);
    
    if ($stmt_delete->execute()) {
        $_SESSION['success_message'] = "Projet retiré de vos suivis avec succès!";
    } else {
        $_SESSION['error_message'] = "Erreur lors du retrait du projet";
    }
    
    header("Location: mes_projets_suivis.php");
    exit;
}

// Récupérer les projets suivis par l'utilisateur
$sql_suivis = "SELECT * FROM projets_suivis WHERE id_citoyen = ? ORDER BY date_suivi DESC";
$stmt_suivis = $conn->prepare($sql_suivis);
$stmt_suivis->bind_param("i", $id_citoyen);
$stmt_suivis->execute();
$result_suivis = $stmt_suivis->get_result();

$total_suivis = $result_suivis->num_rows;

// Récupérer les infos 
$sql = "SELECT * FROM citoyen WHERE id_citoyen = ?"; 
$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $id_citoyen); 
$stmt->execute(); $result = $stmt->get_result(); 
$citoyen = $result->fetch_assoc(); 


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets Suivis</title>
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
          h3{
            font-family: sans-serif;
            font-size : 20px;
            font-weight: bold;
        }
        .nom{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: italic;
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
        
        /* Styles pour les badges de statut */
        .badge-statut {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-a-venir {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-en-cours {
            background-color: #0d6efd;
            color: white;
        }
        
        .badge-termine {
            background-color: #198754;
            color: white;
        }
        
        .badge-en-retard {
            background-color: #dc3545;
            color: white;
        }
        
        .progress {
            height: 20px;
        }
        
        .project-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            border-left: 4px solid #198754;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .project-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .btn-unsuivre {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-unsuivre:hover {
            background: linear-gradient(135deg, #c82333, #d63031);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .date-suivi {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.suivis {
            border-left-color: var(--success-color);
        }
    </style>
    <link rel="stylesheet" href="../shared/sidebar-drawer.css">
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
                            <a class="nav-link" href="accueil.php" data-page="dashboard">
                                <i class="fas fa-house"></i> Accueil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php" data-page="dashboard">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="liste_projet.php" data-page="projects">
                                <i class="fas fa-list"></i> Liste des projets
                            </a>
                        </li>
                        <?php if (!isset($_SESSION['id_citoyen'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">
                                    <i class="fas fa-sign-in-alt"></i> Se connecter
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="mes_projets_suivis.php" data-page="suivis">
                                <i class="fas fa-bookmark"></i> Mes projets suivis
                            </a>
                        </li>
                        <?php if (isset($_SESSION['id_citoyen'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="deconnexion_citoyen.php">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <!-- Contenu droit -->
            <div class="col-lg-10 col-md-9 p-4">
                <!-- Messages d'alerte -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Succès !</h5>
                                <div class="mb-0"><?php echo $_SESSION['success_message']; ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Erreur</h5>
                                <div class="mb-0"><?php echo $_SESSION['error_message']; ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Mes Projets Suivis</h3>
                        <p class="text-muted">Retrouvez tous les projets que vous suivez</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="icone">
                            <i class="bi bi-person"></i> 
                        </div>
                        <div class="nom ms-2">
                            <?php if ($citoyen): ?>
                                <?php echo $citoyen['nom'] ?? 'Utilisateur'; ?> <?php echo $citoyen['prenom']  ?? 'Utilisateur'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <hr>

                <!-- Statistique Card -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card suivis h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Projets suivis</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_suivis; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bookmark fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projets suivis -->
                <?php if ($total_suivis > 0): ?>
                    <div class="row" id="projects-container">
                        <?php while($projet = $result_suivis->fetch_assoc()): ?>
                            <?php
                            // Déterminer la classe du badge selon le statut
                            $badge_class = '';
                            switch($projet["statut"]){
                                case 'À venir': $badge_class = 'badge-a-venir'; break;
                                case 'En cours': $badge_class = 'badge-en-cours'; break;
                                case 'Terminé': $badge_class = 'badge-termine'; break;
                                case 'En retard': $badge_class = 'badge-en-retard'; break;
                                default: $badge_class = 'badge-a-venir';
                            }
                            ?>
                            
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card project-card h-100">
                                    <img src="<?php echo $projet['photo'] ?:0 ; ?>" 
                                         class="project-image" 
                                         alt="<?php echo htmlspecialchars($projet['nom_projet']); ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge badge-statut <?php echo $badge_class; ?>">
                                                <?php echo $projet['statut']; ?>
                                            </span>
                                            <small class="date-suivi">
                                                <i class="bi bi-calendar"></i> 
                                                <?php echo date('d/m/Y', strtotime($projet['date_suivi'])); ?>
                                            </small>
                                        </div>
                                        
                                        <h5 class="card-title"><?php echo htmlspecialchars($projet['nom_projet']); ?></h5>
                                        <p class="card-text text-muted small">
                                            <i class="bi bi-geo-alt"></i> <?php echo $projet['commune']; ?> - <?php echo $projet['quartier']; ?>
                                        </p>
                                        <p class="card-text">
                                            <?php echo strlen($projet['descript']) > 100 ? substr($projet['descript'], 0, 100) . '...' : $projet['descript']; ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Avancement</small>
                                                <small class="fw-bold"><?php echo $projet['avancement']; ?>%</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar <?php echo $projet['avancement'] == 100 ? 'bg-success' : 'bg-primary'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $projet['avancement']; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-cash-coin"></i> <?php echo number_format($projet['budget'], 0, ',', ' '); ?> $
                                            </small>
                                            <div class="btn-group">
                                                <a href="detail_projet.php?id=<?php echo $projet['id_projet']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> Détails
                                                </a>
                                                <a href="mes_projets_suivis.php?arreter_suivi=<?php echo $projet['id_projet']; ?>" 
                                                   class="btn btn-unsuivre btn-sm"
                                                   onclick="return confirm('Voulez-vous vraiment arrêter de suivre ce projet ?')">
                                                    <i class="bi bi-bookmark-x"></i> Arrêter
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- État vide -->
                    <div class="card">
                        <div class="card-body empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-bookmark"></i>
                            </div>
                            <h4 class="text-muted">Aucun projet suivi</h4>
                            <p class="text-muted mb-4">Vous ne suivez aucun projet pour le moment.</p>
                            <a href="liste_projet.php" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Découvrir les projets
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total_suivis > 0): ?>
                    <nav aria-label="Projects pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Précédent</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="../shared/sidebar-drawer.js"></script>
</body>
</html>