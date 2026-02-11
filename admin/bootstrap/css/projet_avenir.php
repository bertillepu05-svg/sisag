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
$result_alert = $conn->query($sql_alert);
$total_alert = $result_alert->fetch_assoc()['total'];

// Récupérer les projets avec pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_projets = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, 
                       date_debut, date_fin, avancement, photo, descript, objectif 
                FROM projet 
                WHERE statut = 'À venir'
                ORDER BY date_debut ASC 
                LIMIT $limit OFFSET $offset";

$result_projets = $conn->query($sql_projets);

// Calculer le nombre total de pages
$sql_total_pages = "SELECT COUNT(*) as total FROM projet WHERE statut = 'À venir'";
$result_total_pages = $conn->query($sql_total_pages);
$total_projets = $result_total_pages->fetch_assoc()['total'];
$total_pages = ceil($total_projets / $limit);

// Récupérer tous les projets pour l'export
$sql_export = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, 
                      date_debut, date_fin, avancement, descript, objectif 
               FROM projet 
               WHERE statut = 'À venir'
               ORDER BY date_debut ASC";
$result_export = $conn->query($sql_export);
$projets_export = [];
while($row = $result_export->fetch_assoc()) {
    $projets_export[] = $row;
}

// afficher le nom 
$sql = "SELECT nom_adm, prenom_adm FROM administrateur WHERE id_adm = ?"; 
$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $id_adm); 
$stmt->execute(); 
$result = $stmt->get_result(); 
$adm = $result->fetch_assoc(); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets À Venir</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Bibliothèques pour l'export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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
        
        body {
            background-color: #f8f9fa;
        }
        h3 {
            font-family: 'Times New Roman', Times, serif;
        }
        .nom{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: italic;
        }
        .sidebar .icone {
            font-weight: bold;
            font-size: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
            margin-left: 0.25rem;
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
        .stat-card.avenir {
            border-left-color: var(--warning-color);
        }
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
            height: 15px;
        }
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
        .alert-avenir {
            border-left: 4px solid #ffc107;
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
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
                            <a class="nav-link" href="dashboard_admin.php">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="liste_projet_admin.php">
                                <i class="fas fa-list"></i> Liste des projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ajouter_projet.php">
                                <i class="fas fa-plus-circle"></i> Ajouter un projet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="projet_critique.php">
                                <i class="fas fa-exclamation-triangle"></i> Projets critiques
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="projet_avenir.php">
                                <i class="fas fa-clock"></i> Projets à venir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_adm.php">
                                <i class="fas fa-users-cog"></i> Gestion des administrateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="commentaire.php">
                                <i class="fas fa-comments"></i> Commentaires
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="deconnexion_admin.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Contenu principal -->
            <div class="col-lg-10 col-md-9 p-4">
                <!-- Alertes pour les projets "À venir" dont la date de début est dépassée -->
                <?php if ($total_alert > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Projets nécessitant une mise à jour de statut</h5>
                        <p class="mb-0"><strong><?php echo $total_alert; ?> projet(s)</strong> sont marqués comme "À venir" alors que leur date de début est déjà arrivée. Ces projets devraient être marqués comme "En cours".</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Projets à Venir</h3>
                    <div class="d-flex align-items-center">
                        <div class="icone">
                            <i class="bi bi-person-gear"></i> 
                        </div>
                        <div class="nom ms-2 me-2">
                            <?php if ($adm): ?>
                                <?php echo $adm['nom_adm'] ?? 0; ?>
                                <?php echo $adm['prenom_adm'] ?? 0; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <hr>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card avenir h-100">
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
                    
                    <!-- Statistique : Projets nécessitant mise à jour -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100" style="border-left-color: #fd7e14;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            À mettre à jour</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_alert; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sync-alt fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-clock me-1"></i>
                        Liste des projets à venir
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="projetsAvenirTable">
                                <thead class="table-warning">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom du projet</th>
                                        <th>Commune</th>
                                        <th>Quartier</th>
                                        <th>Ministère</th>
                                        <th>Statut</th>
                                        <th>Budget($)</th>
                                        <th>Date_début</th>
                                        <th>Date_fin_prévue</th>
                                        <th>Avancement</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($result_projets->num_rows > 0): ?>
                                        <?php while($ligne = $result_projets->fetch_assoc()): ?>
                                            <?php
                                            $date_debut = strtotime($ligne['date_debut']);
                                            $aujourdhui = strtotime(date('Y-m-d'));
                                            $classe_alerte = $date_debut <= $aujourdhui ? 'table-warning' : '';
                                            $couleur_progress = 'bg-info';
                                            ?>
                                            <tr class="<?php echo $classe_alerte; ?>">
                                                <td><?php echo $ligne["id_projet"]; ?></td>
                                                <td><?php echo htmlspecialchars($ligne["nom_projet"]); ?></td>
                                                <td><?php echo htmlspecialchars($ligne["commune"]); ?></td>
                                                <td><?php echo htmlspecialchars($ligne["quartier"]); ?></td>
                                                <td><?php echo htmlspecialchars($ligne["ministere"]); ?></td>
                                                <td>
                                                    <span class="badge badge-statut badge-a-venir">
                                                        <?php echo $ligne["statut"]; ?>
                                                        <?php if($date_debut <= $aujourdhui): ?>
                                                            <i class="fas fa-exclamation-circle ms-1" title="Date de début dépassée"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo number_format($ligne["budget"], 0, ',', ' '); ?> $</td>
                                                <td>
                                                    <?php if($date_debut <= $aujourdhui): ?>
                                                        <span class="text-danger fw-bold"><?php echo date('d/m/Y', $date_debut); ?></span>
                                                    <?php else: ?>
                                                        <?php echo date('d/m/Y', $date_debut); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($ligne["date_fin"])); ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar <?php echo $couleur_progress; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $ligne['avancement']; ?>%" 
                                                             aria-valuenow="<?php echo $ligne['avancement']; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo $ligne['avancement']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="modifier_avenir.php?id=<?php echo $ligne['id_projet']; ?>" class="btn btn-warning btn-sm">
                                                            <i class="bi bi-pencil-square"></i> 
                                                        </a>
                                                        <a href="?supprimer=<?php echo $ligne['id_projet']; ?>" 
                                                           onclick="return confirm('Voulez-vous vraiment supprimer ce projet ?');" 
                                                           class="btn btn-danger btn-sm ms-1">
                                                            <i class="bi bi-trash"></i> 
                                                        </a>
                                                        <a href="detail_projet.php?id=<?php echo $ligne['id_projet']; ?>" class="btn btn-outline-primary btn-sm ms-1">
                                                            <i class="fas fa-eye"></i> 
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox display-4 text-secondary"></i><br>
                                                Aucun projet à venir
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination fonctionnelle -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Navigation des projets">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Précédent">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Suivant">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="text-center text-muted mt-2">
                                    <small>
                                        Affichage des projets <strong><?php echo min(($page - 1) * $limit + 1, $total_projets); ?></strong>
                                        à <strong><?php echo min($page * $limit, $total_projets); ?></strong>
                                        sur <strong><?php echo $total_projets; ?></strong> projet(s)
                                    </small>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>