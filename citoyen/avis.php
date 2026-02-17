
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
    header("Location: detail_projet.php");
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
        header("Location: detail_projet.php");
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
            header("Location: avis.php?id=" . $id_projet);
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
            header("Location: avis.php?id=" . $id_projet);
            exit;

        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement du vote";
            header("Location: avis.php?id=" . $id_projet);
            exit;
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
                        ORDER BY c.date_commentaire DESC";
        
        $stmt_comments = $conn->prepare($sql_comments);
        $stmt_comments->bindParam(':id_projet', $id_projet);
        $stmt_comments->execute();
        $approuved = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $approuved = [];
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis</title>
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
                        <h3>Avis</h3>
                    </div>
                    <a href="detail_projet.php?id=<?php echo $id_projet; ?>" class="btn btn-outline-warning border rounded-2 ps-2 pe-2" typ="submit" style="background-color : white; height : 33px;"><i class="fas fa-arrow-left"></i> Retour au detail</a>
                </div>
                <hr>
                <!--commentaire-->
                <div class="row ">
                    <div class="col-xl-12 col-lg-7">
                        <div class="card stat-card comment mt-4">
                            <!-- Onglet Approuvés -->
                            <div class="">
                                <h5 class="p-3">Tous les avis</h5>
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
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                       <h5 class="text-muted">Pas encore des commentaires, soyez le premier à commenter</h5>
                                    </div>
                                <?php endif; ?>
                            </div>
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