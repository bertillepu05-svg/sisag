
<?php
header('Content-Type: text/html; charset=utf-8');
session_name('admin_session');
session_start();

if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}
$id_adm = $_SESSION['id_adm'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "sisag");
if($conn->connect_error){
    die("erreur".$conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Récupérer les projets à venir
$sql_projets_avenir = "SELECT COUNT(*) as total FROM projet WHERE statut = 'À venir'";
$result_count = $conn->query($sql_projets_avenir);
$total_avenir = $result_count->fetch_assoc()['total'];

// Vérifier les projets "À venir" dont la date de début est dépassée
$aujourdhui = date('Y-m-d');

$sql_alert = "SELECT COUNT(*) as total FROM projet WHERE statut = 'À venir' AND date_debut <= '$aujourdhui'";
$result_count = $conn->query($sql_alert);
$total = $result_count->fetch_assoc()['total'];



// Récupérer les projets avec pagination
$limit = 5; // Nombre de projets par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_projets = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, date_debut, date_fin, avancement, photo, descript, objectif 
                FROM projet 
                WHERE statut = 'À venir'
                ORDER BY id_projet ASC 
                LIMIT $limit OFFSET $offset";

$result_projets = $conn->query($sql_projets);

// Calculer le nombre total de pages
$sql_total_pages = "SELECT COUNT(*) as total FROM projet WHERE statut = 'À venir'";
$result_total_pages = $conn->query($sql_total_pages);
$total_projets = $result_total_pages->fetch_assoc()['total'];
$total_pages = ceil($total_projets / $limit);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets A venir</title>
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
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.termine {
            border-left-color: var(--success-color);
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
        
        .btn-modifier-alerte {
            background: #198754;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            text-decoration: none;
        }
        
        .btn-modifier-alerte:hover {
            background: #157347;
            color: white;
        }
        
        .progress {
            height: 20px;
        }
        /* Styles pour la pagination */
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: bold;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
        }

        /* Responsive pour mobile */
        @media (max-width: 768px) {
            .pagination .page-link {
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
            }
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
                            <a class="nav-link active" href="projet_avenir.php" data-page="projects">
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
                <!-- Alertes pour les projets "À venir" dont la date de début est dépassée -->
                <?php if ($total > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Projets nécessitant une mise à jour de statut</h5>
                        <p class="mb-0"><strong><?php echo $total; ?> projet(s)</strong> sont marqués comme "À venir" alors que leur date de début est déjà arrivée : Ces projets devraient être marqués comme "En cours".</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- En tête -->
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>Projets à venir</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="navbar-text" style="font-size : 20px;">
                        <i class="bi bi-person-gear"></i> Espace administrateur
                        </span>
                        <button type="button" class="btn btn-sm btn-warning text-white ms-2" id="btnExporter">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                    </div>
                </div>
                <hr>
                
                <!-- Statistique Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card termine h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Projets à venir</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_avenir; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- tableau-->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-check-circle me-1"></i>
                        <span>Liste des projets à venir</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="regionProjectsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom du projet</th>
                                        <th>Commune</th>
                                        <th>Quartier</th>
                                        <th>Ministère</th>
                                        <th>Statut</th>
                                        <th>Budget</th>
                                        <th>Date_début</th>
                                        <th>Date_fin</th>
                                        <th>Avancement</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupérer les projets à venir
                                    $sql = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, 
                                                date_debut, date_fin, avancement, descript, objectif 
                                            FROM projet 
                                            WHERE statut = 'À venir' 
                                            ORDER BY date_fin DESC";
                                    $result = $conn->query($sql);

                                    if($result->num_rows > 0){
                                        while($ligne = $result->fetch_assoc()){
                                            echo "<tr>"; 
                                            echo"<td>".$ligne["id_projet"]."</td>";
                                            echo "<td>" . htmlspecialchars($ligne["nom_projet"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($ligne["commune"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($ligne["quartier"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($ligne["ministere"]) . "</td>";
                                            
                                            // Statut avec badge
                                            echo "<td><span class='badge badge-statut badge-termine'>" . $ligne["statut"] . "</span></td>";
                                            
                                            echo "<td>" . number_format($ligne["budget"], 0, ',', ' ') . " $</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($ligne["date_debut"])) . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($ligne["date_fin"])) . "</td>";
                                            
                                            // Avancement avec barre de progression
                                            echo "<td>
                                            <div class='progress' style='height: 15px;'>
                                                <div class='progress-bar " . ($ligne['avancement'] == 100 ? 'bg-success' : 'bg-primary') . "' 
                                                    role='progressbar' 
                                                    style='width: " . $ligne['avancement'] . "%;' 
                                                    aria-valuenow='" . $ligne['avancement'] . "' 
                                                    aria-valuemin='0' 
                                                    aria-valuemax='100'>
                                                    " . $ligne['avancement'] . "%
                                                </div>
                                            </div>
                                        </td>";   
                                            
                                            // Actions
                                            echo "<td>
                                                    <div class='d-flex'>
                                                        <a href='modifier_avenir.php?id=".$ligne['id_projet']."' class='btn btn-warning btn-sm'>
                                                            <i class='bi bi-pencil-square'></i> 
                                                        </a>
                                                        <a href='?supprimer=".$ligne['id_projet']."' 
                                                        onclick='return confirm(\"Voulez-vous vraiment supprimer cet électeur ?\");' 
                                                        class='btn btn-danger btn-sm ms-1'>
                                                            <i class='bi bi-trash'></i> 
                                                        </a>
                                                        <a href='detail_projet.php?id=".$ligne['id_projet']."' class='btn btn-sm btn-outline-primary view-project btn-sm ms-1'>
                                                            <i class='fas fa-eye'></i> 
                                                        </a>
                                                    </div>
                                                </td>";
                                            echo "</tr>";
                                        }    
                                    } else {
                                        echo "<tr><td colspan='12' class='text-center text-muted'>Aucun projet terminé trouvé</td></tr>";
                                    }
                                    $conn->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination améliorée -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Navigation des projets">
                                <ul class="pagination justify-content-center">
                                    <!-- Bouton Précédent -->
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Précédent">
                                            <span aria-hidden="true">&laquo;</span>
                                            <span class="visually-hidden">Précédent</span>
                                        </a>
                                    </li>
                                    
                                    <!-- Première page -->
                                    <?php if ($page > 3): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                                        </li>
                                        <?php if ($page > 4): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <!-- Pages autour de la page actuelle -->
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Dernière page -->
                                    <?php if ($page < $total_pages - 2): ?>
                                        <?php if ($page < $total_pages - 3): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Bouton Suivant -->
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Suivant">
                                            <span aria-hidden="true">&raquo;</span>
                                            <span class="visually-hidden">Suivant</span>
                                        </a>
                                    </li>
                                </ul>
                                <!-- Informations de pagination -->
                                <div class="text-center text-muted mt-2">
                                    <small>
                                        Affichage des projets 
                                        <strong><?php echo min(($page - 1) * $limit + 1, $total_projets); ?></strong>
                                        à 
                                        <strong><?php echo min($page * $limit, $total_projets); ?></strong>
                                        sur 
                                        <strong><?php echo $total_projets; ?></strong>
                                        projet(s) au total
                                        
                                    </small>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>