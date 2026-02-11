<?php
header('Content-Type: text/html; charset=utf-8');
session_name('admin_session');
session_start();

if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "sisag");
if($conn->connect_error){
    die("erreur".$conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Vérifier et mettre à jour automatiquement les statuts en retard
$aujourdhui = date('Y-m-d');
$projets_mis_a_jour = [];

// Mettre à jour automatiquement les projets "En cours" dont la date de fin est dépassée → "En retard"
$sql_update_retard = "UPDATE projet SET statut = 'En retard' 
                      WHERE statut = 'En cours' AND date_fin < '$aujourdhui'";
if ($conn->query($sql_update_retard)) {
    $projets_mis_a_jour_count = $conn->affected_rows;
}

// Récupérer les filtres depuis l'URL
$commune_filter = isset($_GET['commune']) ? $conn->real_escape_string($_GET['commune']) : '';
$statut_filter = isset($_GET['statut']) ? $conn->real_escape_string($_GET['statut']) : '';
$ministere_filter = isset($_GET['ministere']) ? $conn->real_escape_string($_GET['ministere']) : '';
$recherche_filter = isset($_GET['recherche']) ? $conn->real_escape_string($_GET['recherche']) : '';

// Construire la requête SQL avec les filtres
$sql_where = "WHERE 1=1";
$params = [];

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

// Récupérer les données uniques pour les filtres
$sql_communes = "SELECT DISTINCT commune FROM projet WHERE commune IS NOT NULL AND commune != '' ORDER BY commune";
$result_communes = $conn->query($sql_communes);

$sql_ministeres = "SELECT DISTINCT ministere FROM projet WHERE ministere IS NOT NULL AND ministere != '' ORDER BY ministere";
$result_ministeres = $conn->query($sql_ministeres);

// Récupérer les nombres pour les compteurs
$sql_total = "SELECT COUNT(*) as total FROM projet $sql_where";
$result_total = $conn->query($sql_total);
$total = $result_total->fetch_assoc()['total'];

// Compter par commune
$sql_count_commune = "SELECT COUNT(*) as count FROM projet WHERE commune = '$commune_filter'";
if (empty($commune_filter)) {
    $sql_count_commune = "SELECT COUNT(*) as count FROM projet";
}
$result_count_commune = $conn->query($sql_count_commune);
$count_commune = $result_count_commune->fetch_assoc()['count'];

// Compter par statut
$sql_count_statut = "SELECT COUNT(*) as count FROM projet WHERE statut = '$statut_filter'";
if (empty($statut_filter)) {
    $sql_count_statut = "SELECT COUNT(*) as count FROM projet";
}
$result_count_statut = $conn->query($sql_count_statut);
$count_statut = $result_count_statut->fetch_assoc()['count'];

// Compter par ministère
$sql_count_ministere = "SELECT COUNT(*) as count FROM projet WHERE ministere = '$ministere_filter'";
if (empty($ministere_filter)) {
    $sql_count_ministere = "SELECT COUNT(*) as count FROM projet";
}
$result_count_ministere = $conn->query($sql_count_ministere);
$count_ministere = $result_count_ministere->fetch_assoc()['count'];

// Récupérer les projets avec pagination
$limit = 5; // Nombre de projets par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_projets = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, date_debut, date_fin, avancement, photo, descript, objectif 
                FROM projet 
                $sql_where 
                ORDER BY id_projet ASC 
                LIMIT $limit OFFSET $offset";

$result_projets = $conn->query($sql_projets);

// Calculer le nombre total de pages
$sql_total_pages = "SELECT COUNT(*) as total FROM projet $sql_where";
$result_total_pages = $conn->query($sql_total_pages);
$total_projets = $result_total_pages->fetch_assoc()['total'];
$total_pages = ceil($total_projets / $limit);



// afficher le nom 
$sql = "SELECT nom_adm, prenom_adm FROM administrateur WHERE id_adm = ?"; 
$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $id_adm); 
$stmt->execute(); 
$result = $stmt->get_result(); 
$adm = $result->fetch_assoc(); 

$conn->close();
                            
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>liste_projet_admin</title>
    <link rel="stylesheet" href="liste_projet.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Ajout CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
        
        body{
             background-color : #f8f9fa;
        }
        .nom{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: italic;
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
            font-family: sans-serif;
            font-size : 20px;
            font-weight: bold;
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
        
        .btn-modifier-alerte {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            text-decoration: none;
        }
        
        .btn-modifier-alerte:hover {
            background: #c82333;
            color: white;
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
        .photo {
        height: 250px;
        object-fit: cover;
        width: 90%;
        border-bottom: 3px solid var(--light);
        }
        .photo {
            transition: transform 0.2s;
        }

        .photo:hover {
            transform: scale(1.05);
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
                            <a class="nav-link active" href="liste_projet_admin.php">
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
                            <a class="nav-link" href="projet_avenir.php">
                                <i class="fas fa-clock"></i> Projets à venir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_adm.php">
                                <i class="fas fa-users-cog"></i> Gestion des admins
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_citoyen.php">
                                <i class="fas fa-users-cog"></i> Gestion des citoyens
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
                <?php if (isset($_SESSION['warning_message'])): ?>
                    <div class="alert alert-warning-custom alert-custom alert-dismissible fade show mx-auto mt-4" style="max-width: 95%;" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                            <div>
                                <h5 class="alert-heading mb-2">Attention</h5>
                                <div class="mb-0"><?php echo $_SESSION['warning_message']; ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['warning_message']); ?>
                <?php endif; ?>
                <!-- En tête -->
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>Liste des projets</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="icone">
                            <i class="bi bi-person-gear"></i> 
                        </div>
                        <div class="nom ms-2">
                            <?php if ($adm): ?>
                                <?php echo $adm['nom_adm'] ?? 0; ?>
                                <?php echo $adm['prenom_adm'] ?? 0; ?>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-warning text-white ms-2 dropdown-toggle" 
                                    type="button" id="dropdownExport" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownExport">
                                <li><a class="dropdown-item" href="#" onclick="exporterPDF()">
                                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exporterExcel()">
                                    <i class="fas fa-file-excel me-2"></i>Export Excel
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <hr>
                <!-- Filters -->
                <div class="row mb-2">
                    <div class="col-xl-2 col-md-6 mb-3">
                        <div class="card stat-card card-total">
                            <div class="card-body text-center">
                                <div>
                                    <label class="form-label"><strong>Total Projets</strong></label>
                                    <div class="stat-number" id="total-projets"><strong><?php echo $total; ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-6 mb-3">
                        <div class="card stat-card card-region">
                            <div class="card-body">
                                <label for="communeFilter" class="form-label"><strong>Communes</strong></label>
                                <select class="form-select" id="communeFilter" name="commune">
                                    <option value="">Toutes les communes</option>
                                    <?php
                                    if ($result_communes->num_rows > 0) {
                                        while($commune = $result_communes->fetch_assoc()) {
                                            $selected = ($commune_filter == $commune['commune']) ? 'selected' : '';
                                            echo "<option value='".$commune['commune']."' $selected>".$commune['commune']."</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-6 mb-3">
                        <div class="card stat-card card-statut">
                            <div class="card-body">
                                <label for="statusFilter" class="form-label"><strong>Statut</strong></label>
                                <select class="form-select" id="statusFilter" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="À venir" <?php echo ($statut_filter == 'À venir') ? 'selected' : ''; ?>>À venir</option>
                                    <option value="En cours" <?php echo ($statut_filter == 'En cours') ? 'selected' : ''; ?>>En cours</option>
                                    <option value="Terminé" <?php echo ($statut_filter == 'Terminé') ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="En retard" <?php echo ($statut_filter == 'En retard') ? 'selected' : ''; ?>>En retard</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-6 mb-3">
                        <div class="card stat-card card-ministere">
                            <div class="card-body">
                                <label for="ministereFilter" class="form-label"><strong>Ministère</strong></label>
                                <select class="form-select" id="ministereFilter" name="ministere">
                                    <option value="">Tous les ministères</option>
                                    <?php
                                    if ($result_ministeres->num_rows > 0) {
                                        while($ministere = $result_ministeres->fetch_assoc()) {
                                            $selected = ($ministere_filter == $ministere['ministere']) ? 'selected' : '';
                                            echo "<option value='".$ministere['ministere']."' $selected>".$ministere['ministere']."</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-12 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <label for="rechercheFilter" class="form-label"><strong>Recherche</strong></label>
                                <form method="GET" class="d-flex">
                                    <input type="text" class="form-control me-2" id="rechercheFilter" name="recherche" 
                                        placeholder="Nom projet, commune, ministère..." value="<?php echo htmlspecialchars($recherche_filter); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                                <div class="mt-2">
                                    <a href="liste_projet_admin.php" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- carte -->
                <div id="regions" class="page-content">
                    <!-- Map -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-map me-1"></i>
                            Carte des régions de Kinshasa
                        </div>
                        <div class="card-body">
                            <div class="map-container">
                                <p class="text-muted">Carte interactive des communes de la province de Kinshasa</p>
                                <div class="row ">
                                    <div class="col-4">
                                        <img src="../photos/projet/kinshasa-map.jpg" alt="" width="auto" class="photo">
                                    </div>
                                    <div class="col-4">
                                        <img src="../photos/projet/carte-kinshasa.jpg" alt="" width="auto" class="photo">
                                    </div>
                                    <div class="col-4">
                                        <img src="../photos/projet/img2" alt="" width="auto" class="photo">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tableau -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-1"></i>
                            <span>Liste des projets (<?php echo $total; ?> résultat(s))</span>
                        </div>
                        <?php if (!empty($commune_filter) || !empty($statut_filter) || !empty($ministere_filter) || !empty($recherche_filter)): ?>
                        <div class="text-muted">
                            <small>Filtres actifs: 
                                <?php 
                                $filters_active = [];
                                if (!empty($commune_filter)) $filters_active[] = "Commune: $commune_filter";
                                if (!empty($statut_filter)) $filters_active[] = "Statut: $statut_filter";
                                if (!empty($ministere_filter)) $filters_active[] = "Ministère: $ministere_filter";
                                if (!empty($recherche_filter)) $filters_active[] = "Recherche: $recherche_filter";
                                echo implode(', ', $filters_active);
                                ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="regionProjectsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom_projet</th>
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
                                <tbody id="table-body">
                                    <?php
                                    if($result_projets->num_rows > 0){
                                        while($ligne = $result_projets->fetch_assoc()){
                                            echo "<tr>"; 
                                            echo"<td>".$ligne["id_projet"]."</td>";
                                            echo"<td>".htmlspecialchars($ligne["nom_projet"])."</td>";
                                            echo"<td>".htmlspecialchars($ligne["commune"])."</td>";
                                            echo"<td>".htmlspecialchars($ligne["quartier"])."</td>";
                                            echo"<td>".htmlspecialchars($ligne["ministere"])."</td>";
                                            
                                            // Statut avec badge coloré
                                            $badge_class = '';
                                            switch($ligne["statut"]){
                                                case 'À venir': $badge_class = 'badge-a-venir'; break;
                                                case 'En cours': $badge_class = 'badge-en-cours'; break;
                                                case 'Terminé': $badge_class = 'badge-termine'; break;
                                                case 'En retard': $badge_class = 'badge-en-retard'; break;
                                                default: $badge_class = 'badge-a-venir';
                                            }
                                            
                                            echo "<td><span class='badge badge-statut $badge_class'>" . $ligne["statut"] . "</span></td>";
                                            
                                            echo"<td>".number_format($ligne["budget"], 0, ',', ' ')." $</td>";
                                            echo"<td>".date('d/m/Y', strtotime($ligne["date_debut"]))."</td>";
                                            echo"<td>".date('d/m/Y', strtotime($ligne["date_fin"]))."</td>";
                                            
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
                                                        <a href='update.php?id=".$ligne['id_projet']."' class='btn btn-warning btn-sm'>
                                                            <i class='fas fa-edit'></i> 
                                                        </a>
                                                        <a href='detail_projet.php?id=".$ligne['id_projet']."' class='btn btn-sm btn-outline-primary view-project btn-sm ms-1'>
                                                            <i class='fas fa-eye'></i> 
                                                        </a>
                                                    </div>
                                                </td>";
                                            echo "</tr>";
                                        }    
                                    } else {
                                        echo "<tr><td colspan='13' class='text-center text-muted'>Aucun projet trouvé avec les filtres sélectionnés</td></tr>";
                                    }
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
                                        <?php if (!empty($commune_filter) || !empty($statut_filter) || !empty($ministere_filter) || !empty($recherche_filter)): ?>
                                            (filtres appliqués)
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
    // Fonction pour fermer les alertes
    function closeAlert(button) {
        const alert = button.closest('.alert');
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }

// Auto-fermeture des alertes après 8 secondes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            alert.remove();
        }, 300);
    });
}, 8000);

// Filtrer automatiquement quand les sélecteurs changent
document.addEventListener('DOMContentLoaded', function() {
    const communeFilter = document.getElementById('communeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const ministereFilter = document.getElementById('ministereFilter');
    
    function applyFilters() {
        const params = new URLSearchParams(window.location.search);
        
        if (communeFilter.value) params.set('commune', communeFilter.value);
        else params.delete('commune');
        
        if (statusFilter.value) params.set('statut', statusFilter.value);
        else params.delete('statut');
        
        if (ministereFilter.value) params.set('ministere', ministereFilter.value);
        else params.delete('ministere');
        
        // Retour à la page 1 quand on change de filtre
        params.set('page', '1');
        
        window.location.href = 'liste_projet_admin.php?' + params.toString();
    }
    
    communeFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    ministereFilter.addEventListener('change', applyFilters);
});

// Fonction pour exporter en PDF
function exporterPDF() {
    const btn = event.target.closest('.dropdown-item');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
    
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        // Titre du document
        const dateExport = new Date().toLocaleDateString('fr-FR');
        const totalProjets = <?php echo $total; ?>;
        
        // En-tête
        doc.setFontSize(16);
        doc.setTextColor(40, 40, 40);
        doc.text('LISTE DES PROJETS - SISAG', 105, 15, { align: 'center' });
        
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Export du ${dateExport} - ${totalProjets} projet(s) trouvé(s)`, 105, 22, { align: 'center' });
        
        // Informations sur les filtres
        let filtresText = 'Filtres appliqués: ';
        const filtres = [];
        
        <?php if (!empty($commune_filter)): ?>
            filtres.push('Commune: <?php echo $commune_filter; ?>');
        <?php endif; ?>
        
        <?php if (!empty($statut_filter)): ?>
            filtres.push('Statut: <?php echo $statut_filter; ?>');
        <?php endif; ?>
        
        <?php if (!empty($ministere_filter)): ?>
            filtres.push('Ministère: <?php echo $ministere_filter; ?>');
        <?php endif; ?>
        
        <?php if (!empty($recherche_filter)): ?>
            filtres.push('Recherche: <?php echo $recherche_filter; ?>');
        <?php endif; ?>
        
        if (filtres.length === 0) {
            filtresText = 'Aucun filtre appliqué';
        } else {
            filtresText += filtres.join(', ');
        }
        
        doc.text(filtresText, 14, 32);
        
        // Préparer les données du tableau
        const headers = [
            'ID', 
            'Nom Projet', 
            'Commune', 
            'Quartier', 
            'Ministère', 
            'Statut', 
            'Budget ($)', 
            'Date Début', 
            'Date Fin', 
            'Avancement'
        ];
        
        const data = [];
        
        // Récupérer les données du tableau HTML
        const rows = document.querySelectorAll('#regionProjectsTable tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const rowData = [
                    cells[0].textContent.trim(),
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim(),
                    cells[3].textContent.trim(),
                    cells[4].textContent.trim(),
                    cells[5].textContent.trim(),
                    cells[6].textContent.trim(),
                    cells[7].textContent.trim(),
                    cells[8].textContent.trim(),
                    cells[9].textContent.trim()
                ];
                data.push(rowData);
            }
        });
        
        // Créer le tableau avec autoTable
        doc.autoTable({
            startY: 40,
            head: [headers],
            body: data,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 2,
                overflow: 'linebreak'
            },
            headStyles: {
                fillColor: [13, 110, 253],
                textColor: 255,
                fontStyle: 'bold'
            },
            alternateRowStyles: {
                fillColor: [240, 240, 240]
            },
            columnStyles: {
                0: { cellWidth: 8 }, // ID
                1: { cellWidth: 30 }, // Nom Projet
                2: { cellWidth: 18 }, // Commune
                3: { cellWidth: 25 }, // Quartier
                4: { cellWidth: 23 }, // Ministère
                5: { cellWidth: 16 }, // Statut
                6: { cellWidth: 23 }, // Budget
                7: { cellWidth: 20 }, // Date Début
                8: { cellWidth: 20 }, // Date Fin
                9: { cellWidth: 15 }  // Avancement
            },
            margin: { left: 5 }
        });
        
        // Pied de page
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(100, 100, 100);
            doc.text(`Page ${i} / ${pageCount} - SISAG - ${dateExport}`, 105, doc.internal.pageSize.height - 10, { align: 'center' });
        }
        
        // Sauvegarder le PDF
        doc.save(`projets_sisag_${dateExport}.pdf`);
        
        btn.innerHTML = originalText;
        showCustomAlert('Liste des projets exportée en PDF avec succès!', 'success');
        
    } catch (error) {
        console.error('Erreur export PDF:', error);
        btn.innerHTML = originalText;
        showCustomAlert('Erreur lors de l\'export PDF', 'danger');
    }
}

// Fonction pour exporter en Excel (CSV)
function exporterExcel() {
    const btn = event.target.closest('.dropdown-item');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
    
    try {
        // Préparer les en-têtes CSV
        const headers = [
            'ID', 
            'Nom Projet', 
            'Commune', 
            'Quartier', 
            'Ministère', 
            'Statut', 
            'Budget ($)', 
            'Date Début', 
            'Date Fin', 
            'Avancement (%)'
        ];
        
        let csvContent = headers.join(';') + '\n';
        
        // Récupérer les données du tableau HTML
        const rows = document.querySelectorAll('#regionProjectsTable tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const rowData = [
                    cells[0].textContent.trim(),
                    `"${cells[1].textContent.trim()}"`,
                    `"${cells[2].textContent.trim()}"`,
                    `"${cells[3].textContent.trim()}"`,
                    `"${cells[4].textContent.trim()}"`,
                    cells[5].textContent.trim(),
                    cells[6].textContent.trim().replace(/\s/g, ''),
                    cells[7].textContent.trim(),
                    cells[8].textContent.trim(),
                    cells[9].textContent.trim().replace('%', '')
                ];
                csvContent += rowData.join(';') + '\n';
            }
        });
        
        // Créer et télécharger le fichier CSV
        const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const dateExport = new Date().toLocaleDateString('fr-FR');
        link.download = `projets_sisag_${dateExport}.csv`;
        link.href = URL.createObjectURL(blob);
        link.click();
        URL.revokeObjectURL(link.href);
        
        btn.innerHTML = originalText;
        showCustomAlert('Liste des projets exportée en CSV avec succès!', 'success');
        
    } catch (error) {
        console.error('Erreur export CSV:', error);
        btn.innerHTML = originalText;
        showCustomAlert('Erreur lors de l\'export CSV', 'danger');
    }
}

// Fonction pour afficher les messages d'alerte personnalisés
function showCustomAlert(message, type) {
    const alertClass = {
        'success': 'alert-success-custom',
        'danger': 'alert-danger-custom',
        'warning': 'alert-warning-custom'
    }[type] || 'alert-success-custom';

    const alertIcon = {
        'success': 'fas fa-check-circle',
        'danger': 'fas fa-exclamation-triangle',
        'warning': 'fas fa-exclamation-triangle'
    }[type] || 'fas fa-info-circle';

    const alertTitle = {
        'success': 'Succès',
        'danger': 'Erreur',
        'warning': 'Attention'
    }[type] || 'Information';

    const alertHtml = `
        <div class="alert ${alertClass} alert-custom alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;">
            <div class="d-flex align-items-center">
                <i class="${alertIcon} alert-icon"></i>
                <div>
                    <h5 class="alert-heading mb-2">${alertTitle}</h5>
                    <div class="mb-0">${message}</div>
                </div>
            </div>
            <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-supprimer après 5 secondes
    setTimeout(() => {
        const alert = document.querySelector('.position-fixed.alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
