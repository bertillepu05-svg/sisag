<?php
header('Content-Type: text/html; charset=utf-8');
session_name('admin_session');
session_start();

// Déterminer l'onglet actif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';


if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}
$id_adm = $_SESSION['id_adm'];



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

// Traitement des actions de modération
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $id_commentaire = $_POST['id_commentaire'];
    $action = $_POST['action'];
    
    try {
        if ($action == 'approuver') {
            $sql = "UPDATE commentaire SET statut = 'approuve' WHERE id_commentaire = :id_commentaire";
            $message = "Commentaire approuvé avec succès!";
        } elseif ($action == 'rejeter') {
            $sql = "UPDATE commentaire SET statut = 'rejete' WHERE id_commentaire = :id_commentaire";
            $message = "Commentaire rejeté.";
        } elseif ($action == 'supprimer') {
            $sql = "DELETE FROM commentaire WHERE id_commentaire = :id_commentaire";
            $message = "Commentaire supprimé.";
        } elseif ($action == 'reouvrir') {
            $sql = "UPDATE commentaire SET statut = 'en_attente' WHERE id_commentaire = :id_commentaire";
            $message = "Commentaire remis en attente.";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_commentaire', $id_commentaire);
        $stmt->execute();
        
        $_SESSION['success_message'] = $message;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
    }
    
    header("Location: commentaire.php");
    exit;
}

// Paramètres de pagination PAR ONGLET
$comments_per_page = 6;


// Réinitialiser les paramètres de page en fonction de l'onglet actif
$current_page_pending = 1;
$current_page_approved = 1;
$current_page_rejected = 1;

// Charger la page correcte pour chaque onglet
switch ($active_tab) {
    case 'pending':
        $current_page_pending = isset($_GET['page_pending']) ? max(1, intval($_GET['page_pending'])) : 1;
        break;
    case 'approved':
        $current_page_approved = isset($_GET['page_approved']) ? max(1, intval($_GET['page_approved'])) : 1;
        break;
    case 'rejected':
        $current_page_rejected = isset($_GET['page_rejected']) ? max(1, intval($_GET['page_rejected'])) : 1;
        break;
}

// Calculer les offsets pour chaque onglet
$offset_pending = ($current_page_pending - 1) * $comments_per_page;
$offset_approved = ($current_page_approved - 1) * $comments_per_page;
$offset_rejected = ($current_page_rejected - 1) * $comments_per_page;



try {

    // Commentaires en attente
    $sql_pending = "SELECT c.*, ci.prenom, ci.nom, ci.sexe, ci.profession, p.*
                    FROM commentaire c
                    INNER JOIN projet p ON c.id_projet = p.id_projet
                    INNER JOIN citoyen ci ON c.id_citoyen = ci.id_citoyen
                    WHERE c.statut = 'en_attente'
                    ORDER BY c.date_commentaire DESC
                    LIMIT :limit OFFSET :offset";

    $stmt_pending = $conn->prepare($sql_pending);
    $stmt_pending->bindValue(':limit', $comments_per_page, PDO::PARAM_INT);
    $stmt_pending->bindValue(':offset', $offset_pending, PDO::PARAM_INT);
    $stmt_pending->execute();
    $commentaires_attente = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

    // Commentaires approuvés
    $sql_approved = "SELECT c.*, ci.prenom, ci.nom, ci.sexe, ci.profession, p.*
                    FROM commentaire c
                    INNER JOIN projet p ON c.id_projet = p.id_projet
                    INNER JOIN citoyen ci ON c.id_citoyen = ci.id_citoyen
                    WHERE c.statut = 'approuve'
                    ORDER BY c.date_commentaire DESC
                    LIMIT :limit OFFSET :offset";

    $stmt_approved = $conn->prepare($sql_approved);
    $stmt_approved->bindValue(':limit', $comments_per_page, PDO::PARAM_INT);
    $stmt_approved->bindValue(':offset', $offset_approved, PDO::PARAM_INT);
    $stmt_approved->execute();
    $commentaires_approuves = $stmt_approved->fetchAll(PDO::FETCH_ASSOC);

    // Commentaires rejetés
    $sql_rejected = "SELECT c.*, ci.prenom, ci.nom, ci.sexe, ci.profession, p.nom_projet, p.commune
                    FROM commentaire c
                    INNER JOIN projet p ON c.id_projet = p.id_projet
                    INNER JOIN citoyen ci ON c.id_citoyen = ci.id_citoyen
                    WHERE c.statut = 'rejete'
                    ORDER BY c.date_commentaire DESC
                    LIMIT :limit OFFSET :offset";

    $stmt_rejected = $conn->prepare($sql_rejected);
    $stmt_rejected->bindValue(':limit', $comments_per_page, PDO::PARAM_INT);
    $stmt_rejected->bindValue(':offset', $offset_rejected, PDO::PARAM_INT);
    $stmt_rejected->execute();
    $commentaires_rejetes = $stmt_rejected->fetchAll(PDO::FETCH_ASSOC);

    // Total commentaires approuvés pour la pagination
    $sql_approved_total = "SELECT COUNT(*) as total FROM commentaire WHERE statut = 'approuve'";
    $stmt_approved_total = $conn->prepare($sql_approved_total);
    $stmt_approved_total->execute();
    $total_approved = $stmt_approved_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages_approved = ceil($total_approved / $comments_per_page);

    // Total commentaires en attente pour la pagination
    $sql_pending_total = "SELECT COUNT(*) as total FROM commentaire WHERE statut = 'en_attente'";
    $stmt_pending_total = $conn->prepare($sql_pending_total);
    $stmt_pending_total->execute();
    $total_pending = $stmt_pending_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages_pending = ceil($total_pending / $comments_per_page);


    // Total commentaires rejetés pour la pagination
    $sql_rejected_total = "SELECT COUNT(*) as total FROM commentaire WHERE statut = 'rejete'";
    $stmt_rejected_total = $conn->prepare($sql_rejected_total);
    $stmt_rejected_total->execute();
    $total_rejected = $stmt_rejected_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages_rejected = ceil($total_rejected / $comments_per_page);

} catch(PDOException $e) {
    $commentaires_attente = [];
    $commentaires_approuves = [];
    $commentaires_rejetes = [];
    $total_pages_pending = 1;
    $total_pages_approved = 1;
    $total_pages_rejected = 1;
}

// Récupérer les statistiques
try {
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
    SUM(CASE WHEN statut = 'approuve' THEN 1 ELSE 0 END) as approuves,
    SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejetes
    FROM commentaire";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
$stats = ['total' => 0, 'en_attente' => 0, 'approuves' => 0, 'rejetes' => 0];
}


// Fonction pour générer les liens de pagination par onglet
function genererPagination($page_courante, $total_pages, $statut) {
    $params = $_GET;
    $html = '';
    
    // Nettoyer tous les paramètres de pagination existants
    unset($params['page_pending']);
    unset($params['page_approved']);
    unset($params['page_rejected']);
    
    // Définir le bon paramètre de pagination pour cet onglet
    $page_param_name = 'page_' . $statut;
    
    if ($total_pages > 1) {
        $html .= '<nav aria-label="Pagination commentaires ' . $statut . '">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Bouton Précédent
        if ($page_courante > 1) {
            $params[$page_param_name] = $page_courante - 1;
            $params['tab'] = $statut; // Forcer l'onglet correspondant
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="?' . http_build_query($params) . '" aria-label="Précédent">';
            $html .= '<span aria-hidden="true">&laquo;</span>';
            $html .= '</a></li>';
        }
        
        // Pages
        for ($i = 1; $i <= $total_pages; $i++) {
            $params[$page_param_name] = $i;
            $params['tab'] = $statut; // Forcer l'onglet correspondant
            $active = ($i == $page_courante) ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '">';
            $html .= '<a class="page-link" href="?' . http_build_query($params) . '">' . $i . '</a>';
            $html .= '</li>';
        }
        
        // Bouton Suivant
        if ($page_courante < $total_pages) {
            $params[$page_param_name] = $page_courante + 1;
            $params['tab'] = $statut; // Forcer l'onglet correspondant
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="?' . http_build_query($params) . '" aria-label="Suivant">';
            $html .= '<span aria-hidden="true">&raquo;</span>';
            $html .= '</a></li>';
        }
        
        $html .= '</ul></nav>';
    }
    
    return $html;
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaire</title>
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
        .comment-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .comment-card.en-attente {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .comment-card.approuve {
            border-left-color: #198754;
            background: #f0fff4;
        }
        .comment-card.rejete {
            border-left-color: #dc3545;
            background: #fff0f0;
        }
        .comment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid;
        }
        .nav-tabs .nav-link[data-statut="en_attente"].active {
            border-bottom-color: #ffc107;
            color: #856404;
        }
        .nav-tabs .nav-link[data-statut="approuve"].active {
            border-bottom-color: #198754;
            color: #155724;
        }
        .nav-tabs .nav-link[data-statut="rejete"].active {
            border-bottom-color: #dc3545;
            color: #721c24;
        }
        .badge-statut {
            font-size: 0.7em;
        }
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
        .pagination .page-link {
            color: #495057;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
    <link rel="stylesheet" href="../shared/sidebar-drawer.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 p-0 sidebar bg-dark text-white ">
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
                            <a class="nav-link" href="gestion_citoyen.php">
                                <i class="fas fa-users-cog"></i> Gestion des citoyens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="commentaire.php">
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
                <!-- en tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">
                        <i class="fas fa-comments me-2"></i>Modération des commentaires
                    </h3>
                    <div class="text-muted">
                        <small>Dernière mise à jour: <?php echo date('H:i'); ?></small>
                    </div>
                </div>
                <hr>
                <!-- Messages d'alerte -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <!-- Cartes de statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><?php echo $stats['total']; ?></h3>
                                <p class="mb-0 text-muted">Total commentaires</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-warning text-dark">
                            <div class="card-body text-center">
                                <h3><?php echo $stats['en_attente']; ?></h3>
                                <p class="mb-0">En attente</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-success text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $stats['approuves']; ?></h3>
                                <p class="mb-0">Approuvés</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-danger text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $stats['rejetes']; ?></h3>
                                <p class="mb-0">Rejetés</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs mb-4" id="commentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $active_tab === 'pending' ? 'active' : ''; ?>" data-statut="en_attente" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                            <i class="fas fa-clock me-1"></i>
                            En attente
                            <span class="badge bg-warning ms-1"><?php echo $total_pending; ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $active_tab === 'approved' ? 'active' : ''; ?>" data-statut="approuve" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                            <i class="fas fa-check-circle me-1"></i>
                            Approuvés
                            <span class="badge bg-success ms-1"><?php echo $total_approved; ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $active_tab === 'rejected' ? 'active' : ''; ?>" data-statut="rejete" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                            <i class="fas fa-times-circle me-1"></i>
                            Rejetés
                            <span class="badge bg-danger ms-1"><?php echo $total_rejected; ?></span>
                        </button>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content" id="commentTabsContent">
                    <!-- Onglet En attente -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <?php if (count($commentaires_attente) > 0): ?>
                            <div class="row">
                                <?php foreach ($commentaires_attente as $comment): ?>
                                    <div class="col-lg-6 col-xl-4 mb-3">
                                        <div class="comment-card en-attente p-3 rounded">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="user-avatar me-3">
                                                    <?php echo strtoupper(substr($comment['prenom'], 0, 1) . substr($comment['nom'], 0, 1)); ?>
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
                                                <span class="badge bg-warning badge-statut">En attente</span>
                                            </div>
                                            
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['commentaire'])); ?></p>
                                            
                                            <div class="small text-muted mb-3">
                                                <div><i class="fas fa-project-diagram me-1"></i> <?php echo htmlspecialchars($comment['nom_projet']); ?></div>
                                                <div><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($comment['commune']); ?></div>
                                                <div><i class="fas fa-clock me-1"></i> <?php echo date('d/m/Y à H:i', strtotime($comment['date_commentaire'])); ?></div>
                                                <?php if (!empty($comment['email'])): ?>
                                                    <div><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($comment['email']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="d-grid gap-2">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_commentaire" value="<?php echo $comment['id_commentaire']; ?>">
                                                    <button type="submit" name="action" value="approuver" class="btn btn-success btn-sm w-100 mb-1">
                                                        <i class="fas fa-check me-1"></i> Approuver
                                                    </button>
                                                    <button type="submit" name="action" value="rejeter" class="btn btn-danger btn-sm w-100">
                                                        <i class="fas fa-times me-1"></i> Rejeter
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>                            
                            <!-- Pagination pour les commentaires en attente -->
                            <?php
                                $pagination_pending = genererPagination($current_page_pending, $total_pages_pending, 'pending');
                                echo $pagination_pending;
                            ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="text-muted">Aucun commentaire en attente</h5>
                                <p class="text-muted">Tous les commentaires sont modérés</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Onglet Approuvés -->
                    <div class="tab-pane fade" id="approved" role="tabpanel">
                        <?php if (count($commentaires_approuves) > 0): ?>
                            <div class="row">
                                <?php foreach ($commentaires_approuves as $comment): ?>
                                    <div class="col-lg-6 col-xl-4 mb-3">
                                        <div class="comment-card approuve p-3 rounded">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="user-avatar me-3">
                                                    <?php echo strtoupper(substr($comment['prenom'], 0, 1) . substr($comment['nom'], 0, 1)); ?>
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
                                                <span class="badge bg-success badge-statut">Approuvé</span>
                                            </div>
                                            
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['commentaire'])); ?></p>
                                            
                                            <div class="small text-muted">
                                                <div><i class="fas fa-project-diagram me-1"></i> <?php echo htmlspecialchars($comment['nom_projet']); ?></div>
                                                <div><i class="fas fa-clock me-1"></i> <?php echo date('d/m/Y à H:i', strtotime($comment['date_commentaire'])); ?></div>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_commentaire" value="<?php echo $comment['id_commentaire']; ?>">
                                                    <button type="submit" name="action" value="reouvrir" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-undo me-1"></i> Remettre en attente
                                                    </button>
                                                    <button type="submit" name="action" value="supprimer" class="btn btn-outline-danger btn-sm" onclick="return confirm('Supprimer ce commentaire ?')">
                                                        <i class="fas fa-trash me-1"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination pour les commentaires approuvés -->
                            <?php
                                $pagination_approved = genererPagination($current_page_approved, $total_pages_approved, 'approved');
                                echo $pagination_approved;
                            ?>
 
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun commentaire approuvé</h5>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Onglet Rejetés -->
                    <div class="tab-pane fade" id="rejected" role="tabpanel">
                        <?php if (count($commentaires_rejetes) > 0): ?>
                            <div class="row">
                                <?php foreach ($commentaires_rejetes as $comment): ?>
                                    <div class="col-lg-6 col-xl-4 mb-3">
                                        <div class="comment-card rejete p-3 rounded">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="user-avatar me-3">
                                                    <?php echo strtoupper(substr($comment['prenom'], 0, 1) . substr($comment['nom'], 0, 1)); ?>
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
                                                <span class="badge bg-danger badge-statut">Rejeté</span>
                                            </div>
                                            
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['commentaire'])); ?></p>
                                            
                                            <div class="small text-muted">
                                                <div><i class="fas fa-project-diagram me-1"></i> <?php echo htmlspecialchars($comment['nom_projet']); ?></div>
                                                <div><i class="fas fa-clock me-1"></i> <?php echo date('d/m/Y à H:i', strtotime($comment['date_commentaire'])); ?></div>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_commentaire" value="<?php echo $comment['id_commentaire']; ?>">
                                                    <button type="submit" name="action" value="reouvrir" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-undo me-1"></i> Remettre en attente
                                                    </button>
                                                    <button type="submit" name="action" value="supprimer" class="btn btn-outline-danger btn-sm" onclick="return confirm('Supprimer définitivement ce commentaire ?')">
                                                        <i class="fas fa-trash me-1"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination pour les commentaires rejetés -->
                            <?php
                                $pagination_rejected = genererPagination($current_page_rejected, $total_pages_rejected, 'rejected');
                                echo $pagination_rejected;
                            ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun commentaire rejeté</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sauvegarder l'onglet actif et restaurer au rechargement
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem('activeCommentTab');
            if (activeTab) {
                const tab = document.querySelector(`[data-bs-target="${activeTab}"]`);
                if (tab) {
                    new bootstrap.Tab(tab).show();
                }
            }
            
            // Sauvegarder l'onglet actif quand on change
            const tabs = document.querySelectorAll('#commentTabs .nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    const target = e.target.getAttribute('data-bs-target');
                    localStorage.setItem('activeCommentTab', target);
                });
            });
            
            // Mettre à jour les URLs des paginations pour garder l'onglet actif
            function updatePaginationLinks() {
                const activeTab = document.querySelector('#commentTabs .nav-link.active');
                const tabTarget = activeTab ? activeTab.getAttribute('data-bs-target') : '#pending';
                
                document.querySelectorAll('.pagination .page-link').forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && href.includes('?')) {
                        // Supprimer les anciens paramètres d'onglet
                        let url = new URL(href, window.location.origin);
                        url.searchParams.delete('tab');
                        
                        // Ajouter le paramètre d'onglet actif
                        if (tabTarget === '#approved') {
                            url.searchParams.set('tab', 'approved');
                        } else if (tabTarget === '#rejected') {
                            url.searchParams.set('tab', 'rejected');
                        } else {
                            url.searchParams.set('tab', 'pending');
                        }
                        
                        link.setAttribute('href', url.search);
                    }
                });
            }
            
            // Mettre à jour les liens quand on change d'onglet
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', updatePaginationLinks);
            });
            
            // Initialiseri
            updatePaginationLinks();
        });
    </script>
    <script src="../shared/sidebar-drawer.js"></script>
</body>
</html>