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
        $ancien_avancement = $projet['avancement'];
        
        
        $erreurs = [];
        

        
        if ($projet['statut'] == 'En cours' && $nouveau_statut == 'Terminé') {
            // Forcer l'avancement à 100%
            $nouvel_avancement = 100;
        }
        
        // Empêcher les transitions interdites
        $transitions_interdites = [
            'Terminé' => ['À venir', 'En cours'],
            'En cours' => ['À venir'],
            'En retard' => ['À venir', 'En cours']
        ];
        
        if (isset($transitions_interdites[$projet['statut']]) && 
            in_array($nouveau_statut, $transitions_interdites[$projet['statut']])) {
            $erreurs[] = "Transition interdite: impossible de passer de '{$projet['statut']}' à '$nouveau_statut'";
        }
        
        // Vérifier la cohérence avancement/statut
        if ($projet['statut'] == 'À venir' && $nouvel_avancement >= 0) {
            $erreurs[] = "Pour toutes modifications, changez d'abord le statut à 'En cours'";
        }
 
        // Vérifier la cohérence avancement/statut
        if ($nouveau_statut == 'En cours' && $nouvel_avancement <= $ancien_avancement) {
            $erreurs[] = "L'avancement doit crôitre et non decrôtre";
        }

        // Vérifier la cohérence avancement/statut
        if ($nouveau_statut == 'Terminé' && $nouvel_avancement != 100) {
            $erreurs[] = "Un projet terminé doit avoir un avancement de 100%";
        }
        
        if ($nouveau_statut != 'Terminé' && $nouvel_avancement == 100) {
            $erreurs[] = "L'avancement ne peut être à 100% que pour un projet terminé";
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
        } else {
            $_SESSION['error_message'] = implode("<br>", $erreurs);
        }
    }

    // Traitement de l'ajout de phase avec images
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phase'])) { 
        $nom_phase = $_POST['nom_phase']; 
        $descript_phase = $_POST['descript_phase'];
        
        $erreurs = [];

        if ($projet['statut'] == 'Terminé') {
            $erreurs[] = "Un projet terminé ne peut plus avoir des phases d'avancements";
        }

        if ($projet['statut'] == 'À venir') {
            $erreurs[] = "Un projet à venir ne peut pas avoir des phases d'avancements tant qu'il n'est pas en cours, changez d'abord le statut !";
        }

        if (empty($erreurs)){
            try {
                // Commencer une transaction
                $conn->beginTransaction();
    
                // Insertion de la phase dans la base de données
                $sql_phase = "INSERT INTO phase (id_projet, nom_phase, descript_phase) 
                            VALUES (:id_projet, :nom_phase, :descript_phase)";
                $stmt_phase = $conn->prepare($sql_phase);
                $stmt_phase->bindParam(':id_projet', $id_projet);
                $stmt_phase->bindParam(':nom_phase', $nom_phase);
                $stmt_phase->bindParam(':descript_phase', $descript_phase);
                $stmt_phase->execute();
                
                $id_phase = $conn->lastInsertId();
    
                // Traitement des images - CORRECTION
                $images_uploaded = 0;
                $max_files = 2;
                
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $dossier_upload = "../photos/phases/";
                    
                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($dossier_upload)) {
                        mkdir($dossier_upload, 0777, true);
                    }
                    
                    $files = $_FILES['images'];
                    $file_count = count($files['name']);
                    
                    // CORRECTION: Vérification du nombre de fichiers
                    if ($file_count > $max_files) {
                        throw new Exception("Vous ne pouvez uploader que $max_files images maximum. Vous avez sélectionné $file_count images.");
                    }
                    
                    // CORRECTION: Boucle corrigée
                    for ($i = 0; $i < $file_count; $i++) {
                        // Vérifier s'il y a une erreur d'upload
                        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                            $error_message = "Erreur lors de l'upload de l'image " . ($i + 1) . ": ";
                            switch ($files['error'][$i]) {
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $error_message .= "Fichier trop volumineux";
                                    break;
                                case UPLOAD_ERR_PARTIAL:
                                    $error_message .= "Upload partiel";
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    continue 2; // Passer au fichier suivant
                                default:
                                    $error_message .= "Erreur inconnue";
                            }
                            throw new Exception($error_message);
                        }
                        
                        // Vérifier la taille du fichier (2MB max)
                        if ($files['size'][$i] > 2 * 1024 * 1024) {
                            throw new Exception("L'image " . ($i + 1) . " est trop volumineuse. Maximum 2MB autorisé.");
                        }
                        
                        // Vérifier le type de fichier
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        $file_type = mime_content_type($files['tmp_name'][$i]);
                        
                        if (!in_array($file_type, $allowed_types)) {
                            throw new Exception("Type de fichier non autorisé pour l'image " . ($i + 1) . ". Types acceptés: JPG, PNG, GIF, WEBP");
                        }
                        
                        // Générer un nom unique
                        $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $new_filename = uniqid('phase_', true) . '_' . ($i + 1) . '.' . $file_extension;
                        $upload_path = $dossier_upload . $new_filename;
                        
                        // Déplacer le fichier
                        if (move_uploaded_file($files['tmp_name'][$i], $upload_path)) {
                            // CORRECTION: Chemin pour la base de données (relatif)
                            $chemin_bd = 'photos/phases/' . $new_filename;
                            
                            // Insérer dans la base de données
                            $sql_image = "INSERT INTO phase_images (id_phase, nom_image, chemin_image) 
                                        VALUES (:id_phase, :nom_image, :chemin_image)";
                            $stmt_image = $conn->prepare($sql_image);
                            $stmt_image->bindParam(':id_phase', $id_phase);
                            $stmt_image->bindParam(':nom_image', $files['name'][$i]);
                            $stmt_image->bindParam(':chemin_image', $chemin_bd);
                            
                            if ($stmt_image->execute()) {
                                $images_uploaded++;
                            } else {
                                // Supprimer le fichier si l'insertion échoue
                                unlink($upload_path);
                                throw new Exception("Erreur lors de l'enregistrement de l'image " . ($i + 1) . " dans la base de données");
                            }
                        } else {
                            throw new Exception("Erreur lors du déplacement de l'image " . ($i + 1));
                        }
                    }
                }
                
                $conn->commit();
                
                $message = "Phase ajoutée avec succès!";
                if ($images_uploaded > 0) {
                    $message .= " $images_uploaded image(s) uploadée(s).";
                }
                $_SESSION['success_message'] = $message;
                
            } catch(PDOException $e) {
                $conn->rollBack();
                $_SESSION['error_message'] = "Erreur base de données: " . $e->getMessage();
            } catch(Exception $e) {
                $conn->rollBack();
                $_SESSION['error_message'] = $e->getMessage();
            }
            
            // Rediriger pour éviter la resoumission du formulaire
            header("Location: update.php?id=" . $id_projet);
            exit;
        }else {
            $_SESSION['error_message'] = implode("<br>", $erreurs);
        }
    }

        // PAGINATION DES PHASES
        $phases_par_page = 1; // 1 phase par page comme demandé
        $page_phase = isset($_GET['page_phase']) ? (int)$_GET['page_phase'] : 1;
        $offset_phase = ($page_phase - 1) * $phases_par_page;

        // Récupérer le nombre total de phases
        try {
            $sql_count_phases = "SELECT COUNT(*) as total FROM phase WHERE id_projet = :id_projet";
            $stmt_count_phases = $conn->prepare($sql_count_phases);
            $stmt_count_phases->bindParam(':id_projet', $id_projet);
            $stmt_count_phases->execute();
            $total_phases = $stmt_count_phases->fetch(PDO::FETCH_ASSOC)['total'];
            $total_pages_phase = ceil($total_phases / $phases_par_page);
        } catch(PDOException $e) {
            $total_phases = 0;
            $total_pages_phase = 1;
        }

        // Récupérer les phases avec pagination
        try {
            $sql_phases = "SELECT p.*, 
                        (SELECT COUNT(*) FROM phase_images pi WHERE pi.id_phase = p.id_phase) as nb_images
                        FROM phase p 
                        WHERE p.id_projet = :id_projet 
                        ORDER BY p.date_mise_a_jour DESC 
                        LIMIT :limit OFFSET :offset";
            
            $stmt_phases = $conn->prepare($sql_phases);
            $stmt_phases->bindParam(':id_projet', $id_projet);
            $stmt_phases->bindParam(':limit', $phases_par_page, PDO::PARAM_INT);
            $stmt_phases->bindParam(':offset', $offset_phase, PDO::PARAM_INT);
            $stmt_phases->execute();
            $phases = $stmt_phases->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $phases = [];
            echo '<p class="text-danger">Erreur lors du chargement des phases: ' . $e->getMessage() . '</p>';
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
                    <a href="liste_projet_admin.php" class="btn btn-outline-warning border rounded-2 ps-2 pe-2" typ="submit" style="background-color : white; height : 33px;"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
                </div>
                <hr>

                <!-- Informations du projet -->
                <div class="project-info-card">
                    <h5 class="card-title">Informations du projet</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom :</strong> <?php echo htmlspecialchars($projet['nom_projet']); ?></p>
                            <p><strong>Ministère :</strong> <?php echo htmlspecialchars($projet['ministere']); ?></p>
                            <p><strong>Date début prévue :</strong> <?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Localisation :</strong> <?php echo htmlspecialchars($projet['commune']); ?> - <?php echo htmlspecialchars($projet['quartier']); ?></p>
                            <p><strong>Date fin prévue :</strong> <?php echo date('d/m/Y', strtotime($projet['date_fin'])); ?></p>
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
                                        <option value="">Sélectionner un statut</option>
                                        <option value="À venir" <?php echo ($projet['statut'] == 'À venir') ? 'selected' : ''; ?>>À venir</option>
                                        <option value="En cours" <?php echo ($projet['statut'] == 'En cours') ? 'selected' : ''; ?>>En cours</option>
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

                <!-- Gestion des phases d'avancement -->
                <div class="phase-info-card">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <strong>Ajouter des phases d'avancement du projet</strong>
                        </div>
                        <div class="card-body"> 
                            <form action="update.php?id=<?php echo $id_projet; ?>" method="POST" enctype="multipart/form-data">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="nom_phase" class="form-label">Nom de la phase</label>
                                        <textarea name="nom_phase" rows="3" class="rounded-2 form-control" 
                                                placeholder="Ex: Phase initiale, Construction, Finalisation..." required></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="descript_phase" class="form-label">Description de la phase</label>
                                        <textarea name="descript_phase" rows="3" class="rounded-2 form-control" 
                                                placeholder="Décrivez les travaux effectués durant cette phase..." required></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="images" class="form-label">Images de la phase</label>
                                        <input class="form-control" name="images[]" type="file" accept="image/*" multiple>
                                        <small class="form-text text-muted">
                                            Formats acceptés: JPG, PNG, GIF, WEBP. Maximum 2 images. 
                                            Taille max: 2MB par image.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="phase" class="btn btn-danger">
                                        <i class="fas fa-save"></i> Enregistrer la phase
                                    </button>
                                    <a href="liste_projet_admin.php" class="btn btn-outline-secondary">Retour à la liste</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Affichage des phases existantes -->
                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <strong>Historique des phases</strong>
                            <span class="badge bg-light text-dark">
                                Phase <?php echo $page_phase; ?> sur <?php echo $total_pages_phase; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <?php
                            if (count($phases) > 0) {
                                foreach ($phases as $phase) {
                                    echo '<div class="phase-item mb-4 p-3 border rounded">';
                                        echo '<div class="d-flex justify-content-between align-items-start">';
                                            echo '<div class="flex-grow-1 mt-2">';
                                                echo '<h6 class="text-primary">' . htmlspecialchars($phase['nom_phase']) . '</h6>';
                                                echo '<p class="mb-2">' . nl2br(htmlspecialchars($phase['descript_phase'])) . '</p>';
                                                echo '<small class="text-muted">Ajouté le: ' . date('d/m/Y H:i', strtotime($phase['date_mise_a_jour'])) . '</small>';
                                            echo '</div>';
                                            echo '<div class="d-flex">';
                                                // Afficher les images de la phase
                                                $sql_images = "SELECT * FROM phase_images WHERE id_phase = :id_phase";
                                                $stmt_images = $conn->prepare($sql_images);
                                                $stmt_images->bindParam(':id_phase', $phase['id_phase']);
                                                $stmt_images->execute();
                                                $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                if (count($images) > 0) {
                                                    echo '<div class="ms-3">';
                                                    echo '<span class="badge bg-secondary mb-1 ms-2" style="height:24px;">' . $phase['nb_images'] . ' image(s)</span>';
                                                    echo '<div class="d-flex flex-wrap gap-2 ">';
                                                    foreach ($images as $image) {
                                                        $chemin_affichage = '/sisag/photos/phases/' . basename($image['chemin_image']);
                                                        echo '<a href="' . $chemin_affichage . '" target="_blank">';
                                                        echo '<img src="' . $chemin_affichage . '" class="img-thumbnail" style="width: 180px; height: 100px; object-fit: cover;">';
                                                        echo '</a>';
                                                    }                                        
                                                    echo '</div>';
                                                    echo '</div>';
                                                }
                                               
                                            echo '</div>';
                                        echo '</div>';
                                    echo '</div>';                 
                                }
                            } else {
                                echo '<p class="text-muted text-center">Aucune phase enregistrée pour ce projet.</p>';
                            }
                            ?>
                            
                            <!-- PAGINATION DES PHASES -->
                            <?php if ($total_pages_phase > 1): ?>
                            <nav aria-label="Navigation des phases">
                                <ul class="pagination justify-content-center mb-0">
                                    <!-- Bouton Précédent -->
                                    <li class="page-item <?php echo $page_phase <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $id_projet; ?>&page_phase=<?php echo $page_phase - 1; ?>" aria-label="Phase précédente">
                                            <span aria-hidden="true">&laquo;</span>
                                            <span class="visually-hidden">Phase précédente</span>
                                        </a>
                                    </li>
                                    
                                    <!-- Pages -->
                                    <?php for ($i = 1; $i <= $total_pages_phase; $i++): ?>
                                        <li class="page-item <?php echo $i == $page_phase ? 'active' : ''; ?>">
                                            <a class="page-link" href="?id=<?php echo $id_projet; ?>&page_phase=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Bouton Suivant -->
                                    <li class="page-item <?php echo $page_phase >= $total_pages_phase ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $id_projet; ?>&page_phase=<?php echo $page_phase + 1; ?>" aria-label="Phase suivante">
                                            <span aria-hidden="true">&raquo;</span>
                                            <span class="visually-hidden">Phase suivante</span>
                                        </a>
                                    </li>
                                </ul>
                                
                                <!-- Informations de pagination -->
                                <div class="text-center text-muted mt-2">
                                    <small>
                                        Affichage de la phase <strong><?php echo $page_phase; ?></strong> 
                                        sur <strong><?php echo $total_pages_phase; ?></strong> 
                                        phase(s) au total
                                    </small>
                                </div>
                            </nav>
                            <?php endif; ?>
                        </div>
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
        // Validation côté client pour les images
        document.querySelector('input[name="images[]"]').addEventListener('change', function(e) {
            const files = e.target.files;
            const maxFiles = 2;
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            // Vérifier le nombre de fichiers
            if (files.length > maxFiles) {
                alert(`Vous ne pouvez sélectionner que ${maxFiles} images maximum. Vous avez sélectionné ${files.length} images.`);
                e.target.value = '';
                return;
            }
            
            // Vérifier chaque fichier
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Vérifier la taille
                if (file.size > maxSize) {
                    alert(`L'image "${file.name}" est trop volumineuse. Maximum 2MB autorisé.`);
                    e.target.value = '';
                    return;
                }
                
                // Vérifier le type
                if (!allowedTypes.includes(file.type)) {
                    alert(`Le type de fichier "${file.name}" n'est pas autorisé. Formats acceptés: JPG, PNG, GIF, WEBP.`);
                    e.target.value = '';
                    return;
                }
            }
            
            // Afficher un message de confirmation
            if (files.length > 0) {
                const message = files.length === 1 ? '1 image sélectionnée' : `${files.length} images sélectionnées`;
                console.log(message + ' - Validation OK');
            }
        });

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