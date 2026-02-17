
<?php
header('Content-Type: text/html; charset=utf-8');

// DÉBUT: Gestion de session SANS connexion DB
session_name('citoyen_session');
session_start();

// Fonctions de session (sans DB)
function estConnecte() {
    return isset($_SESSION['id_citoyen']);
}

function verifierConnexion($page_redirection = 'login.php', $message = "Veuillez vous connecter.") {
    if (!estConnecte()) {
        $_SESSION['error_message'] = $message;
        header("Location: $page_redirection");
        exit;
    }
}
// FIN: Gestion de session

// Connexion à la base de données (SEULE connexion dans ce fichier)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sisag";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Erreur de connexion DB: " . $e->getMessage());
}

// Récupérer l'ID du projet depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: liste_projet.php");
    exit;
}

$id_projet = intval($_GET['id']); // Sécurisation

// Récupérer les données du projet (VERSION CORRIGÉE)
try {
    $sql_projet = "SELECT * FROM projet WHERE id_projet = :id_projet";
    $stmt_projet = $conn->prepare($sql_projet);
    
    if (!$stmt_projet) {
        $errorInfo = $conn->errorInfo();
        die("Erreur préparation requête: " . $errorInfo[2]);
    }
    
    $stmt_projet->bindParam(':id_projet', $id_projet, PDO::PARAM_INT);
    
    if (!$stmt_projet->execute()) {
        $errorInfo = $stmt_projet->errorInfo();
        die("Erreur exécution requête: " . $errorInfo[2]);
    }
    
    $projet = $stmt_projet->fetch(PDO::FETCH_ASSOC);
    
    if (!$projet) {
        header("Location: liste_projet.php");
        exit;
    }
    
} catch(PDOException $e) {
    die("Erreur récupération projet: " . $e->getMessage());
}



    // Fonction pour récupérer les votes d'un commentaire
    function getVoteStats($conn, $id_commentaire) {
        try {
            $sql = "SELECT 
                    COUNT(CASE WHEN vote = 'oui' THEN 1 END) as oui_count,
                    COUNT(CASE WHEN vote = 'non' THEN 1 END) as non_count,
                    COUNT(*) as total_votes
                    FROM commentaire_votes 
                    WHERE id_commentaire = :id_commentaire";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_commentaire', $id_commentaire);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return ['oui_count' => 0, 'non_count' => 0, 'total_votes' => 0];
        }
    }

    // Fonction pour vérifier le vote de l'utilisateur courant
    function getUserVote($conn, $id_commentaire, $id_citoyen) {
        if (!isset($id_citoyen)) return null;
        
        try {
            $sql = "SELECT vote FROM commentaire_votes 
                    WHERE id_commentaire = :id_commentaire 
                    AND id_citoyen = :id_citoyen";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_commentaire', $id_commentaire);
            $stmt->bindParam(':id_citoyen', $id_citoyen);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['vote'] : null;
        } catch(PDOException $e) {
            return null;
        }
    }


    // TRAITEMENT DU VOTE
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vote'])) {
        if (!isset($_SESSION['id_citoyen'])) {
            $_SESSION['error_message'] = "Connectez-vous pour pouvoir voter";
            header("Location: detail_projet.php?id=" . $id_projet);
            exit;
        }

        $id_citoyen = $_SESSION['id_citoyen'];
        $id_commentaire = $_POST['id_commentaire'];
        $vote_type = $_POST['vote_type'];

        try {
            // Vérifier si l'utilisateur a déjà voté
            $sql_check = "SELECT id_vote, vote FROM commentaire_votes 
                        WHERE id_commentaire = :id_commentaire 
                        AND id_citoyen = :id_citoyen";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bindParam(':id_commentaire', $id_commentaire);
            $stmt_check->bindParam(':id_citoyen', $id_citoyen);
            $stmt_check->execute();
            $existing_vote = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existing_vote) {
                // Mettre à jour le vote existant
                if ($existing_vote['vote'] != $vote_type) {
                    $sql_update = "UPDATE commentaire_votes 
                                SET vote = :vote_type, date_vote = NOW()
                                WHERE id_vote = :id_vote";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bindParam(':vote_type', $vote_type);
                    $stmt_update->bindParam(':id_vote', $existing_vote['id_vote']);
                    $stmt_update->execute();
                }
            } else {
                // Nouveau vote
                $sql_insert = "INSERT INTO commentaire_votes (id_commentaire, id_citoyen, vote)
                            VALUES (:id_commentaire, :id_citoyen, :vote_type)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bindParam(':id_commentaire', $id_commentaire);
                $stmt_insert->bindParam(':id_citoyen', $id_citoyen);
                $stmt_insert->bindParam(':vote_type', $vote_type);
                $stmt_insert->execute();
            }

            $_SESSION['success_message'] = "Votre vote a été enregistré";
            header("Location: detail_projet.php?id=" . $id_projet);
            exit;

        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement du vote";
            header("Location: detail_projet.php?id=" . $id_projet);
            exit;
        }
    }



    // TRAITEMENT DU FORMULAIRE DE COMMENTAIRE 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['commentaire'])) {
        
        // Vérifier si l'utilisateur est connecté
        if (!estConnecte()) {
            $_SESSION['error_message'] = "Vous devez être connecté pour ajouter un commentaire.";
            header("Location: detail_projet.php?id=" . $id_projet . "");
            exit;
        }
    
        
        $id_citoyen = $_SESSION['id_citoyen'];
        $commentaire_text = trim($_POST['commentaire_text']);
        
        // Validation du commentaire
        if (!empty($commentaire_text)) {
            try { 
                // INSERT CORRIGÉ : ajout de id_citoyen
                $sql_comment = "INSERT INTO commentaire (id_citoyen,id_projet, commentaire, date_commentaire)
                            VALUES (:id_citoyen, :id_projet, :commentaire, NOW())";
                $stmt_comment = $conn->prepare($sql_comment);
                $stmt_comment->bindParam(':id_citoyen', $id_citoyen);
                $stmt_comment->bindParam(':id_projet', $id_projet);
                $stmt_comment->bindParam(':commentaire', $commentaire_text);
                $stmt_comment->execute();

                if ($stmt_comment) {
                     $success_message = "Votre commentaire a été envoyé avec succès! Il sera visible après modération.";
                    // Redirection pour éviter le re-soumission du formulaire
                    header("Location: detail_projet.php?id=" . $id_projet . "");
                    exit;
                }
                
            } catch(PDOException $e) {
                $error_message = "Erreur lors de l'envoi du commentaire: " . $e->getMessage();
                header("Location: detail_projet.php?id=" . $id_projet . "");
            }
        } else {
            $error_message = "Veuillez écrire un commentaire avant de soumettre.";
            header("Location: detail_projet.php?id=<?php echo $id_projet; ?>");
        }
    }

    // RÉCUPÉRATION DES COMMENTAIRES 
    try {
        $sql_comments = "SELECT c.*, ci.prenom, ci.nom, ci.sexe, ci.profession, ci.photo1, p.*
                        FROM commentaire c
                        INNER JOIN projet p ON c.id_projet = p.id_projet
                        INNER JOIN citoyen ci ON c.id_citoyen = ci.id_citoyen
                        WHERE c.statut = 'approuve'
                        AND c.id_projet = :id_projet  
                        ORDER BY c.date_commentaire DESC 
                        LIMIT 2";
        
        $stmt_comments = $conn->prepare($sql_comments);
        $stmt_comments->bindParam(':id_projet', $id_projet);
        $stmt_comments->execute();
        $approuved = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $approuved = [];
    }


    // Récupérer les phases du projet
    try {
        $sql_phases = "SELECT p.*, 
                    (SELECT COUNT(*) FROM phase_images pi WHERE pi.id_phase = p.id_phase) as nb_images
                    FROM phase p 
                    WHERE p.id_projet = :id_projet 
                    ORDER BY p.date_mise_a_jour DESC";
        $stmt_phases = $conn->prepare($sql_phases);
        $stmt_phases->bindParam(':id_projet', $id_projet);
        $stmt_phases->execute();
        $phases = $stmt_phases->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $phases = [];
    }

  
    // PAGINATION DES PHASES - 2 phases par page
    $phases_par_page = 2; // 2 phases par page comme demandé
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail_projet</title>
     <link rel="stylesheet" href="destail_projet.css">
     <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        
        .stat-card.info {
            border-left-color: var(--primary-color);
        }
        .stat-card.object {
            border-left-color: var(--danger-color);
        }
        .stat-card.comment {
            border-left-color: var(--success-color);
        }
        .photo {
        height: 250px;
        object-fit: cover;
        width: 90%;
        border-bottom: 3px solid var(--light);
        }
        .photo_histo{
            height: 110px;
            object-fit: cover;
            width: 30%;
            border-bottom: 3px solid var(--light);
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #0d6efd;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid #0d6efd;
        }

        .required:after {
            content: " *";
            color: red;
        }

        .photo_histo {
            transition: transform 0.2s;
        }

        .photo_histo:hover {
            transform: scale(1.05);
        }
        /* Styles pour la pagination des phases */
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            font-size: 0.9rem;
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

        /* Amélioration visuelle des marqueurs de timeline */
        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #0d6efd;
        }

        .timeline-marker.bg-primary { box-shadow: 0 0 0 3px #0d6efd; }
        .timeline-marker.bg-success { box-shadow: 0 0 0 3px #198754; }
        .timeline-marker.bg-warning { box-shadow: 0 0 0 3px #ffc107; }
        .timeline-marker.bg-info { box-shadow: 0 0 0 3px #0dcaf0; }
        .timeline-marker.bg-danger { box-shadow: 0 0 0 3px #dc3545; }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .btn-vote {
            transition: all 0.3s ease;
            border-radius: 40px;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-vote:hover {
            transform: translateY(-2px);
        }

        .btn-vote.active {
            font-weight: bold;
        }

        .avis a{
            text-decoration:none;
        }
        .avis a:hover{
            color : white;
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
                            <a class="nav-link active" href="liste_projet.php" data-page="projects">
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
                            <a class="nav-link" href="mes_projets_suivis.php" data-page="suivis">
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
                        <h3>Détail du projet</h3>
                    </div>
                    <a href="liste_projet.php" class="btn btn-outline-warning border rounded-2 ps-2 pe-2" typ="submit" style="background-color : white; height : 33px;"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
                </div>
                <hr>
                <!--detais & image-->
                <div class="row">
                    <!--detais-->
                    <div class="col-xl-6 col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Description du projet
                            </div>
                            <div class="card-body">
                                <div class="card-title">
                                    <h3><?php echo htmlspecialchars($projet['nom_projet']); ?></h3>
                                </div>
                                <p class="text-muted pt-3">
                                    <?php echo nl2br(htmlspecialchars($projet['descript'] ?? 'Aucune description disponible.')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!--image-->
                    <div class="col-xl-6 col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-1"></i>
                                Image du projet
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($projet['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($projet['photo']); ?>" alt="<?php echo htmlspecialchars($projet['nom_projet']); ?>" class="photo rounded-2">
                                <?php else: ?>
                                    <img src="../photos/default/resto.jpg" alt="Image par défaut" class="photo rounded-2">
                                    <p class="text-muted mt-2">Image par défaut</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!--info & objectif-->
                <div class="row ">
                    <!--info generale-->
                    <div class="col-xl-6 col-lg-7">
                        <div class="card stat-card info mt-4">
                            <div class="card-body">
                                <div class="card-title">
                                    <h3>Informations générales</h3>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Région:
                                        <span><?php echo htmlspecialchars($projet['commune']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Quartier:
                                        <span><?php echo htmlspecialchars($projet['quartier']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Ministère:
                                        <span><?php echo htmlspecialchars($projet['ministere']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Statut:
                                        <span class="badge 
                                            <?php 
                                            switch($projet['statut']) {
                                                case 'À venir': echo 'bg-secondary'; break;
                                                case 'En cours': echo 'bg-primary'; break;
                                                case 'Terminé': echo 'bg-success'; break;
                                                case 'En retard': echo 'bg-danger'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?php echo $projet['statut']; ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Date début:
                                        <span><?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Date fin prévue:
                                        <span><?php echo date('d/m/Y', strtotime($projet['date_fin'])); ?></span>
                                    </li>
                                </ul>
                                <hr>
                            </div>
                        </div>
                    </div>
                    <!--objectif-->
                    <div class="col-xl-6 col-lg-7">
                        <div class="card stat-card object mt-4">
                        <div class="card-body">
                                <div class="card-title">
                                    <h3>Objectifs</h3>
                                </div>
                                <div class="text-muted">
                                    <?php echo nl2br(htmlspecialchars($projet['objectif'] ?? 'Aucun objectif défini.')); ?>
                                </div>
                                <p class="mt-3"><strong>Avancement</strong></p>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Progression:</span>
                                        <span id="detailProgressText"><?php echo $projet['avancement']; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar 
                                            <?php 
                                            if ($projet['avancement'] >= 80) echo 'bg-success';
                                            elseif ($projet['avancement'] >= 50) echo 'bg-warning';
                                            else echo 'bg-danger';
                                            ?>" 
                                            role="progressbar" 
                                            style="width: <?php echo $projet['avancement']; ?>%" 
                                            id="detailProgressBar">
                                            <?php echo $projet['avancement']; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--historique & commentaire-->
                <div class="row ">
                    <!--historique-->
                    <div class="col-xl-6 col-lg-7">
                        <div class="card mb-4 mt-4">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-history me-1"></i>
                                    Historique d'avancement
                                    <span class="badge bg-light text-dark ms-2">
                                        Page <?php echo $page_phase; ?> sur <?php echo $total_pages_phase; ?>
                                    </span>
                                </div>
                                <?php if ($total_phases > 0): ?>
                                    <small class="text-light">
                                        <?php echo $total_phases; ?> phase(s) au total
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="card-title">
                                    <h3>Historique d'avancement</h3>
                                </div>
                                
                                <?php if (count($phases) > 0): ?>
                                    <div class="timeline">
                                        <?php foreach ($phases as $index => $phase): ?>
                                            <div class="timeline-item mb-4">
                                                <div class="timeline-marker 
                                                    <?php 
                                                    // Couleurs différentes pour chaque phase
                                                    $colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger'];
                                                    echo $colors[$index % count($colors)];
                                                    ?>">
                                                </div>
                                                <div class="timeline-content">
                                                    <h6 class="text-primary"><?php echo htmlspecialchars($phase['nom_phase']); ?></h6>
                                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($phase['descript_phase'])); ?></p>
                                                    <small class="text-muted">Mise à jour: <?php echo date('d/m/Y H:i', strtotime($phase['date_mise_a_jour'])); ?></small>
                                                    
                                                    <?php
                                                    // Récupérer les images de cette phase
                                                    try {
                                                        $sql_images = "SELECT * FROM phase_images WHERE id_phase = :id_phase";
                                                        $stmt_images = $conn->prepare($sql_images);
                                                        $stmt_images->bindParam(':id_phase', $phase['id_phase']);
                                                        $stmt_images->execute();
                                                        $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
                                                        
                                                        if (count($images) > 0): ?>
                                                            <div class="d-flex flex-wrap gap-2 my-3">
                                                                <?php foreach ($images as $image): 
                                                                    $chemin_affichage = '/sisag/photos/phases/' . basename($image['chemin_image']);
                                                                ?>
                                                                    <a href="<?php echo $chemin_affichage; ?>" target="_blank" class="text-decoration-none">
                                                                        <img src="<?php echo $chemin_affichage; ?>" 
                                                                            alt="<?php echo htmlspecialchars($image['nom_image']); ?>" 
                                                                            class="photo_histo rounded-2"
                                                                            style="height: 130px; width: 180px; object-fit: cover;"
                                                                            title="<?php echo htmlspecialchars($image['nom_image']); ?>">
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif;
                                                    } catch(PDOException $e) {
                                                        // Ignorer les erreurs d'images
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- PAGINATION DES PHASES -->
                                    <?php if ($total_pages_phase > 1): ?>
                                    <nav aria-label="Navigation des phases" class="mt-4">
                                        <ul class="pagination justify-content-center mb-0">
                                            <!-- Bouton Précédent -->
                                            <li class="page-item <?php echo $page_phase <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?id=<?php echo $id_projet; ?>&page_phase=<?php echo $page_phase - 1; ?>#historique" aria-label="Phases précédentes">
                                                    <span aria-hidden="true">&laquo;</span>
                                                    <span class="visually-hidden">Phases précédentes</span>
                                                </a>
                                            </li>
                                            
                                            <!-- Pages -->
                                            <?php for ($i = 1; $i <= $total_pages_phase; $i++): ?>
                                                <li class="page-item <?php echo $i == $page_phase ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?id=<?php echo $id_projet; ?>&page_phase=<?php echo $i; ?>#historique">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Bouton Suivant -->
                                            <li class="page-item <?php echo $page_phase >= $total_pages_phase ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?id=<?php echo $id_projet; ?>&page_phase=<?php echo $page_phase + 1; ?>#historique" aria-label="Phases suivantes">
                                                    <span aria-hidden="true">&raquo;</span>
                                                    <span class="visually-hidden">Phases suivantes</span>
                                                </a>
                                            </li>
                                        </ul>
                                        
                                        <!-- Informations de pagination -->
                                        <div class="text-center text-muted mt-2">
                                            <small>
                                                Affichage des phases 
                                                <strong><?php echo min(($page_phase - 1) * $phases_par_page + 1, $total_phases); ?></strong>
                                                à 
                                                <strong><?php echo min($page_phase * $phases_par_page, $total_phases); ?></strong>
                                                sur 
                                                <strong><?php echo $total_phases; ?></strong>
                                                phase(s) au total
                                            </small>
                                        </div>
                                    </nav>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Aucune phase d'avancement enregistrée pour ce projet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!--commentaire-->
                    <div class="col-xl-6 col-lg-7">
                        <div class="card stat-card comment mt-4">
                            <!-- Onglet Approuvés -->
                            <div class="">
                                <h3 class="p-3">Quelques avis</h3>
                                <?php if (count($approuved) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($approuved as $comment): ?>
                                            <div class="">
                                                <div class="comment-card approuve ps-4 pt-1 rounded">
                                                    <div class="d-flex align-items-start">
                                                        <div class="photo-candidate me-3">
                                                            <?php
                                                                if (!empty($comment["photo1"]) && file_exists($comment["photo1"])) {
                                                                    echo "<img src='".htmlspecialchars($comment["photo1"])."' class='user-avatar' alt='Photo candidat'>";
                                                                } else {
                                                                    echo "<img src='../photos/default/avatar.jpg'class='user-avatar' alt='Photo candidat'>";
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']); ?></h6>
                                                            <small class="text-muted">
                                                                <?php echo ($comment['sexe'] == 'M') ? 'Homme' : 'Femme'; ?>
                                                                <?php if (!empty($comment['profession'])): ?>
                                                                    • <?php echo htmlspecialchars($comment['profession']); ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['commentaire'])); ?></p>
                                                    
                                                    <div class="small text-muted">
                                                        <div><i class="fas fa-project-diagram me-1"></i> <?php echo htmlspecialchars($comment['nom_projet']); ?></div>
                                                        <div><i class="fas fa-clock me-1"></i> <?php echo date('d/m/Y à H:i', strtotime($comment['date_commentaire'])); ?></div>
                                                        <div class="d-flex mt-1">
                                                            <p>Êtes-vous de cet avis ?</p>
                                                            
                                                            <?php
                                                            $vote_stats = getVoteStats($conn, $comment['id_commentaire']);
                                                            $user_vote = getUserVote($conn, $comment['id_commentaire'], $_SESSION['id_citoyen'] ?? null);
                                                            ?>
                                                            
                                                            <form method="POST" class="d-flex ms-2">
                                                                <input type="hidden" name="id_commentaire" value="<?php echo $comment['id_commentaire']; ?>">
                                                                <input type="hidden" name="vote" value="1">
                                                                
                                                                <button type="submit" name="vote_type" value="oui" 
                                                                        class="btn btn-sm <?php echo ($user_vote == 'oui') ? 'btn-success' : 'btn-outline-success'; ?> ms-1">
                                                                    OUI 
                                                                </button>
                                                                
                                                                <button type="submit" name="vote_type" value="non" 
                                                                        class="btn btn-sm <?php echo ($user_vote == 'non') ? 'btn-danger' : 'btn-outline-danger'; ?> ms-1">
                                                                    NON 
                                                                </button>
                                                            </form>
                                                        </div>

                                                        <p id="total_avis_<?php echo $comment['id_commentaire']; ?>">
                                                            <?php echo $vote_stats['oui_count']; ?> personnes sont de cet avis
                                                        </p>
                                                    </div>
                                                    <hr>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="mt-2 d-flex justify-content-center mb-4 avis">
                                               <a href='avis.php?id=<?php echo $id_projet; ?>' class="btn btn-outline-success btn-sm"><i class="fas fa-undo me-1"></i> Voir tous les avis</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                       <h5 class="text-muted">Pas encore des commentaires, soyez le premier à commenter</h5>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- comment -->
                            <form action="detail_projet.php?id=<?php echo $id_projet; ?>" method="POST">
                                <div class="ps-3 pe-3">
                                    <textarea class="form-control" name="commentaire_text" rows="4" placeholder="Laissez-nous un commentaire sur ce que vous pensez du projet" required></textarea>
                                </div>
                                <div class="text-center mb-3">
                                    <button type="submit" name="commentaire" class="btn btn-warning w-75 text-white mt-3">
                                        Soumettre <i class="fas fa-paper-plane ms-1"></i>
                                    </button>
                                </div>
                            </form>
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
   </script>
    <script src="../shared/sidebar-drawer.js"></script>
</body>
</html>
<?php
// Fermer la connexion seulement à la fin
if (isset($conn) && $conn instanceof PDO) {
    $conn=null;
}
?>