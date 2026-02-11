<?php
header('Content-Type: text/html; charset=utf-8');
session_name('admin_session');
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sisag";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}
$id_adm = $_SESSION['id_adm'];

// Récupérer les paramètres de filtrage
$commune_filter = isset($_GET['commune']) ? $_GET['commune'] : '';
$date_order = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['recherche']) ? $_GET['recherche'] : '';

// Construire la requête SQL avec filtres
$sql_where = [];
$params = [];

if (!empty($commune_filter)) {
    $sql_where[] = "c.commune = ?";
    $params[] = $commune_filter;
}

if (!empty($search)) {
    $sql_where[] = "(c.nom LIKE ? OR c.prenom LIKE ? OR c.email LIKE ? OR c.profession LIKE ? OR c.commune LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Construire ORDER BY
$order_by = "ORDER BY ";
if ($date_order === 'croissant') {
    $order_by .= "c.date_signUp ASC";
} elseif ($date_order === 'decroissant') {
    $order_by .= "c.date_signUp DESC";
} else {
    $order_by .= "c.id_citoyen ASC";
}

// Requête principale pour les citoyens
$sql = "SELECT 
            c.id_citoyen,
            c.photo1,
            c.nom,
            c.prenom,
            c.sexe,
            c.email,
            c.telephone,
            c.commune,
            c.quartier,
            c.profession,
            c.statut,
            c.date_signUp,
            c.last_activity,
            -- État calculé en temps réel
            CASE 
                WHEN c.last_activity IS NULL THEN 'Jamais connecté'
                WHEN c.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'Actif'
                WHEN c.last_activity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 'Inactif récent'
                ELSE 'Inactif'
            END as etat_reel,
            -- Temps écoulé depuis dernière activité
            TIMESTAMPDIFF(MINUTE, c.last_activity, NOW()) as minutes_inactif
        FROM citoyen c";

if (!empty($sql_where)) {
    $sql .= " WHERE " . implode(" AND ", $sql_where);
}
$sql .= " " . $order_by;

// Préparation et exécution
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$citoyens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Requête pour les statistiques (avec les mêmes filtres)
$sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE 
                    WHEN last_activity IS NULL THEN 0
                    WHEN last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
                    ELSE 0
                END) as actif,
                SUM(CASE 
                    WHEN last_activity IS NULL THEN 1
                    WHEN last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
                    ELSE 0
                END) as inactif
              FROM citoyen c";

if (!empty($sql_where)) {
    $sql_stats .= " WHERE " . implode(" AND ", $sql_where);
}

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Récupérer les communes distinctes pour le filtre
$sql_communes = "SELECT DISTINCT commune FROM citoyen WHERE commune IS NOT NULL AND commune != '' ORDER BY commune";
$stmt_communes = $conn->prepare($sql_communes);
$stmt_communes->execute();
$communes = $stmt_communes->fetchAll(PDO::FETCH_COLUMN);

// Traitement du formulaire d'ajout

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $telephone = $_POST['telephone'];
    $commune = $_POST['commune'];
    $quartier = $_POST['quartier'];
    $profession = $_POST['profession'];
    $statut = $_POST['statut'];

    // Vérifier si l'email existe déjà
    $check_email = "SELECT id_citoyen FROM citoyen WHERE email = '$email'";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Un citoyen avec l'email <strong>$email</strong> existe déjà.";
        $_SESSION['form_data'] = $_POST;
        header("Location: gestion_citoyen.php");
        exit();
    }

     // Gestion de la photo 
     if (!empty($_FILES['photo1']['name'])) {
        $extension = pathinfo($_FILES['photo1']['name'], PATHINFO_EXTENSION);
        $nouveau_nom = "photo_" .time(). "." . $extension;
        $destination = "../photos/citoyen/" . $nouveau_nom;
        move_uploaded_file($_FILES['photo1']['tmp_name'], $destination);
    }


    // Insertion du nouveau citoyen
    $insert = "INSERT INTO citoyen (photo1,nom, prenom, sexe, email, mot_de_passe, telephone, commune, quartier, profession, statut, date_signUp)
               VALUES ('$destination','$nom', '$prenom', '$sexe', '$email', '$mot_de_passe', '$telephone', '$commune', '$quartier', '$profession', '$statut', NOW())";
    if ($conn->query($insert)) {
        $_SESSION['success_message'] = "citoyen ajouté avec succès !";
        header("Location: gestion_citoyen.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'ajout : " . $conn->error;
        $_SESSION['form_data'] = $_POST;
        header("Location: gestion_citoyen.php");
        exit();
    }
} 

// Récupérer le nom de l'admin
try {
    $sql_adm = "SELECT nom_adm, prenom_adm FROM administrateur WHERE id_adm = :id_adm";
    $stmt_adm = $conn->prepare($sql_adm);
    $stmt_adm->bindParam(':id_adm', $id_adm);
    $stmt_adm->execute();
    $adm = $stmt_adm->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Erreur: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion_citoyen</title>
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
        
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.total {
            border-left-color: var(--primary-color);
        }
        
        .stat-card.actif {
            border-left-color: var(--success-color);
        }
        
        .stat-card.inactif{
            border-left-color: var(--danger-color);
        }
        /* Tableau amélioré */
        .data-section {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .section-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
        }
        
        .section-header h3 {
            margin: 0;
            color: var(--primary);
        }
        
         
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding: 12px 15px;
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
         /* Styles pour la barre de défilement du tableau */
         .table-container {
            max-height: 400px; /* Hauteur fixe pour le conteneur du tableau */
            overflow-y: auto; /* Barre de défilement verticale */
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        
        .table-container table {
            margin-bottom: 0; /* Supprime la marge basse du tableau */
        }
        
        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Style personnalisé pour la barre de défilement */
        .table-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        .photo-candidate,
            .photo-placeholder {
                width: 40px;
                height: 40px;
                border-radius:50%;
                object-fit:cover;
        }
        
        .badge-actif { background-color: #28a745 !important; }
        .badge-inactif-recent { background-color: #ffc107 !important; color: #000 !important; }
        .badge-inactif { background-color: #6c757d !important; }
        .badge-jamais { background-color: #17a2b8 !important; }
        
        .statut-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .statut-indicator.actif { background-color: #28a745; }
        .statut-indicator.inactif-recent { background-color: #ffc107; }
        .statut-indicator.inactif { background-color: #6c757d; }
        .statut-indicator.jamais { background-color: #17a2b8; }
        
        .last-activity {
            font-size: 0.85rem;
            color: #6c757d;
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
                            <a class="nav-link active" href="gestion_citoyen.php">
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
                        <h3>Gestion des Citoyens</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="icone">
                            <i class="bi bi-person-gear"></i> 
                        </div>
                        <div class="nom ms-2">
                            <?php if ($adm): ?>
                                <?php echo htmlspecialchars($adm['nom_adm'] ?? ''); ?>
                                <?php echo htmlspecialchars($adm['prenom_adm'] ?? ''); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <hr>
                
                <!-- Cartes de statistiques AVEC FILTRES APPLIQUÉS -->
                <div class="row mb-2">
                    <div class="col-xl-2 col-md-4 mb-4">
                        <div class="card stat-card total">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p>Total Citoyens</p>
                                        <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                                        <?php if ($commune_filter): ?>
                                            <small class="text-muted">Commune: <?php echo htmlspecialchars($commune_filter); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people-fill fs-1 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 mb-4">
                        <div class="card stat-card actif">
                            <div class="card-body">
                                <p>Actifs (≤5 min)</p>
                                <div class="stat-number"><?php echo $stats['actif'] ?? 0; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 mb-4">
                        <div class="card stat-card inactif">
                            <div class="card-body">
                                <p>Inactifs</p>
                                <div class="stat-number"><?php echo $stats['inactif'] ?? 0; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FILTRES -->
                    <div class="col-xl-2 col-md-6 mb-3">
                        <div class="card stat-card card-region">
                            <div class="card-body">
                                <form method="GET" class="filter-form">
                                    <label for="communeFilter" class="form-label"><strong>Communes</strong></label>
                                    <select class="form-select" id="communeFilter" name="commune" onchange="this.form.submit()">
                                        <option value="">Toutes les communes</option>
                                        <?php foreach ($communes as $commune): ?>
                                            <option value="<?php echo htmlspecialchars($commune); ?>" 
                                                <?php echo ($commune_filter == $commune) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($commune); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-6 mb-3">
                        <div class="card stat-card card-region">
                            <div class="card-body">
                                <form method="GET" class="filter-form">
                                    <label for="dateTrier" class="form-label"><strong>Trier date</strong></label>
                                    <select class="form-select" id="dateTrier" name="date" onchange="this.form.submit()">
                                        <option value="">--Sélectionner--</option>
                                        <option value="croissant" <?php echo ($date_order == 'croissant') ? 'selected' : ''; ?>>Croissant</option>
                                        <option value="decroissant" <?php echo ($date_order == 'decroissant') ? 'selected' : ''; ?>>Décroissant</option>
                                    </select>
                                    <!-- Garder les autres filtres -->
                                    <?php if ($commune_filter): ?>
                                        <input type="hidden" name="commune" value="<?php echo htmlspecialchars($commune_filter); ?>">
                                    <?php endif; ?>
                                    <?php if ($search): ?>
                                        <input type="hidden" name="recherche" value="<?php echo htmlspecialchars($search); ?>">
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                            <label for="rechercheFilter" class="form-label"><strong>Recherche</strong></label>
                                <form method="GET" class="d-flex">
                                    <input type="text" class="form-control me-2" id="rechercheFilter" name="recherche" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Nom, prénom, email, profession...">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <!-- Garder les autres filtres -->
                                    <?php if ($commune_filter): ?>
                                        <input type="hidden" name="commune" value="<?php echo htmlspecialchars($commune_filter); ?>">
                                    <?php endif; ?>
                                    <?php if ($date_order): ?>
                                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date_order); ?>">
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des citoyens -->
                <div class="data-section">
                    <div class="section-header">
                        <h3><i class="fas fa-list me-1"></i>Liste des Citoyens</h3>
                        <?php if ($commune_filter || $search): ?>
                            <div class="mt-2">
                                <span class="badge bg-info">
                                    <?php 
                                    if ($commune_filter) echo "Commune: " . htmlspecialchars($commune_filter) . " ";
                                    if ($search) echo "Recherche: \"" . htmlspecialchars($search) . "\"";
                                    ?>
                                </span>
                                <a href="gestion_citoyen.php" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="fas fa-times"></i> Effacer filtres
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table table-hover" id="regionProjectsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Email</th>
                                        <th>Commune</th>
                                        <th>Quartier</th>
                                        <th>Profession</th>
                                        <th>État_réel</th>
                                        <th>Dernière_activité</th>
                                        <th>Date_inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <?php if (empty($citoyens)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <i class="fas fa-users-slash fa-2x text-muted mb-2"></i><br>
                                                Aucun citoyen trouvé avec ces critères
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($citoyens as $citoyen): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($citoyen['id_citoyen']); ?></td>
                                                <td>
                                                    <?php if (!empty($citoyen['photo1']) && file_exists($citoyen['photo1'])): ?>
                                                        <img src="<?php echo htmlspecialchars($citoyen['photo1']); ?>" class='photo-candidate' alt='Photo'>
                                                    <?php else: ?>
                                                        <img src='../photos/default/avatar.jpg' class='photo-candidate' alt='Photo_par_défaut'>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($citoyen['nom']); ?></td>
                                                <td><?php echo htmlspecialchars($citoyen['prenom']); ?></td>
                                                <td><?php echo htmlspecialchars($citoyen['email']); ?></td>
                                                <td><?php echo htmlspecialchars($citoyen['commune']); ?></td>
                                                <td><?php echo htmlspecialchars($citoyen['quartier']); ?></td>
                                                 <td><?php echo htmlspecialchars($citoyen['profession']); ?></td>
                                                <td>
                                                    <?php 
                                                    $etat = $citoyen['etat_reel'];
                                                    $badge_class = '';
                                                    $indicator_class = '';
                                                    
                                                    switch($etat) {
                                                        case 'Actif':
                                                            $badge_class = 'badge-actif';
                                                            $indicator_class = 'actif';
                                                            break;
                                                        case 'Inactif récent':
                                                            $badge_class = 'badge-inactif-recent';
                                                            $indicator_class = 'inactif-recent';
                                                            break;
                                                        case 'Inactif':
                                                            $badge_class = 'badge-inactif';
                                                            $indicator_class = 'inactif';
                                                            break;
                                                        case 'Jamais connecté':
                                                            $badge_class = 'badge-jamais';
                                                            $indicator_class = 'jamais';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="statut-indicator <?php echo $indicator_class; ?>"></span>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo $etat; ?>
                                                    </span>
                                                    <?php if ($citoyen['minutes_inactif'] !== null && $etat != 'Actif'): ?>
                                                        <br>
                                                        <small class="last-activity">
                                                            <?php 
                                                            if ($etat == 'Jamais connecté') {
                                                                echo 'Jamais';
                                                            } else {
                                                                echo 'Il y a ' . $citoyen['minutes_inactif'] . ' min';
                                                            }
                                                            ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($citoyen['last_activity']) {
                                                        echo date('d/m/Y H:i', strtotime($citoyen['last_activity']));
                                                    } else {
                                                        echo 'Jamais';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($citoyen['date_signUp'])); ?></td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href='modifier_citoyen.php?id=<?php echo htmlspecialchars($citoyen['id_citoyen']); ?>' class='btn btn-warning btn-sm'>
                                                            <i class='bi bi-pencil-square'></i>
                                                        </a>
                                                        <a href='#' 
                                                           onclick='return confirm("Voulez-vous vraiment supprimer ce citoyen ?");' 
                                                           class="btn btn-danger btn-sm ms-2">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
               <!-- Ajout admin-->
               <div class="row mb-3">
                    <div class="col-xl-12 col-lg-7">
                        <div class="card mb-4 mt-4">
                            <div class="card-header bg-dark"></div>
                            <div class="card-body">
                               <div class="card-title">
                                    <h3>Ajouter un citoyen</h3>
                               </div>
                                <form action="gestion_citoyen.php" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label">Photo de profil <small class="text-muted">(Optionnel)</small></label>
                                            <input type="file" name="photo1" class="form-control">
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Nom</strong></label>
                                            <input type="text" name="nom" placeholder="Nom*" value="<?php echo isset($_SESSION['form_data']['nom_adm']) ? htmlspecialchars($_SESSION['form_data']['nom_adm']) : ''; ?>" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Prenom</strong></label>
                                            <input type="text" name="prenom" value="<?php echo isset($_SESSION['form_data']['prenom_adm']) ? htmlspecialchars($_SESSION['form_data']['prenom_adm']) : ''; ?>" placeholder="Prenom*" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Sexe</strong></label><br>
                                            <select  name="sexe" class="form-select" required>
                                                <option value="">--Selectionner--</option>
                                                <option value="M" <?php echo (isset($_SESSION['form_data']['sexe']) && $_SESSION['form_data']['sexe'] == 'M') ? 'selected' : ''; ?>>Homme</option>
                                                <option value="F" <?php echo (isset($_SESSION['form_data']['sexe']) && $_SESSION['form_data']['sexe'] == 'F') ? 'selected' : ''; ?>>Femme</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Email</strong></label>
                                            <input type="email" name="email" value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>" placeholder="Email*" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Mot de passe</strong></label>
                                            <input type="password" name="mot_de_passe" placeholder="●●●●●●●●"  class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Téléphone</strong></label>
                                            <input type="number" name="telephone" value="<?php echo isset($_SESSION['form_data']['telephone']) ? htmlspecialchars($_SESSION['form_data']['telephone']) : ''; ?>" placeholder="+243 XX XXX XXXX" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Commune</strong></label>
                                            <input type="text" name="commune" value="<?php echo isset($_SESSION['form_data']['commune']) ? htmlspecialchars($_SESSION['form_data']['commune']) : ''; ?>" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Quartier</strong></label>
                                            <input type="text" name="quartier" value="<?php echo isset($_SESSION['form_data']['quartier']) ? htmlspecialchars($_SESSION['form_data']['quartier']) : ''; ?>" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Profession</strong></label>
                                            <input type="text" name="profession" value="<?php echo isset($_SESSION['form_data']['profession']) ? htmlspecialchars($_SESSION['form_data']['profession']) : ''; ?>" placeholder="Profession*" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Statut</strong></label>
                                            <select name="statut" required class="form-control"required>
                                                <option value="Inactif">Inactif</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-start">
                                            <button type="submit" class="btn btn-warning text-white mt-5">Soumettre <i class="bi bi-send-fill ms-1"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Mettre à jour les filtres sans recharger la page (optionnel)
        document.querySelectorAll('.filter-form select').forEach(select => {
            select.addEventListener('change', function() {
                // Garder les autres paramètres
                const form = this.closest('form');
                const urlParams = new URLSearchParams(window.location.search);
                
                // Ajouter les autres filtres comme inputs cachés
                urlParams.forEach((value, key) => {
                    if (key !== this.name && !form.querySelector([name="${key}"])) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = key;
                        hiddenInput.value = value;
                        form.appendChild(hiddenInput);
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>