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

$aujourdhui = date('Y-m-d');

// Statistiques générales
$stats = [];
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'En cours' THEN 1 ELSE 0 END) as en_cours,
    SUM(CASE WHEN statut = 'Terminé' THEN 1 ELSE 0 END) as termines,
    SUM(CASE WHEN statut = 'En retard' THEN 1 ELSE 0 END) as retard,
    SUM(CASE WHEN statut = 'À venir' THEN 1 ELSE 0 END) as a_venir
FROM projet";

$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Top 3 projets les plus avancés (statut "En cours" avec avancement élevé et date fin > aujourd'hui)
$sql_top_avances = "SELECT id_projet, nom_projet, avancement, date_fin, photo 
                   FROM projet 
                   WHERE statut = 'En cours' 
                   AND date_fin > '$aujourdhui'
                   ORDER BY avancement DESC, date_fin ASC 
                   LIMIT 3";
$result_top_avances = $conn->query($sql_top_avances);
$top_avances = [];
while($row = $result_top_avances->fetch_assoc()) {
    $top_avances[] = $row;
}

// Top 3 projets critiques (statut "En retard" avec date fin < aujourd'hui)
$sql_top_critiques = "SELECT id_projet, nom_projet, date_fin, DATEDIFF('$aujourdhui', date_fin) as jours_retard, photo 
                     FROM projet 
                     WHERE statut = 'En retard' 
                     AND date_fin < '$aujourdhui'
                     ORDER BY jours_retard DESC 
                     LIMIT 3";
$result_top_critiques = $conn->query($sql_top_critiques);
$top_critiques = [];
while($row = $result_top_critiques->fetch_assoc()) {
    $top_critiques[] = $row;
}

// Données pour le graphique par région 
$sql_regions = "SELECT commune, COUNT(*) as count FROM projet WHERE commune IS NOT NULL AND commune != '' GROUP BY commune";
$result_regions = $conn->query($sql_regions);
$regions_labels = [];
$regions_counts = [];
while($row = $result_regions->fetch_assoc()) {
    $regions_labels[] = $row['commune'];
    $regions_counts[] = $row['count'];
}

// Données pour le graphique par statut   
$sql_statuts = "SELECT statut, COUNT(*) as count FROM projet GROUP BY statut";
$result_statuts = $conn->query($sql_statuts);
$statuts_labels = [];
$statuts_counts = [];
while($row = $result_statuts->fetch_assoc()) {
    $statuts_labels[] = $row['statut'];
    $statuts_counts[] = $row['count'];
}

$regions_labels_json = json_encode($regions_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($regions_labels_json === false) { $regions_labels_json = '[]'; }

$regions_counts_json = json_encode($regions_counts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($regions_counts_json === false) { $regions_counts_json = '[]'; }

$statuts_labels_json = json_encode($statuts_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($statuts_labels_json === false) { $statuts_labels_json = '[]'; }

$statuts_counts_json = json_encode($statuts_counts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($statuts_counts_json === false) { $statuts_counts_json = '[]'; }

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
    <title>Dashboard_admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .stat-card.en-cours {
            border-left-color: var(--primary-color);
        }
        
        .stat-card.termines {
            border-left-color: var(--success-color);
        }
        
        .stat-card.retard {
            border-left-color: var(--danger-color);
        }
        
        .stat-card.a-venir {
            border-left-color: var(--warning-color);
        }
        
        .project-image {
            height: 80px;
            width: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .project-image {
            transition: transform 0.2s;
        }

        .project-image:hover {
            transform: scale(1.05);
        }
        
        .progress {
            height: 15px;
        }
        
        .badge-retard {
            background-color: var(--danger-color);
            color: white;
        }

        .alert {
            min-width: 300px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .position-fixed {
            z-index: 9999;
        }

        .chart-container {
            position: relative;
            height: 320px;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 260px;
            }
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
                            <a class="nav-link active" href="dashboard_admin.php">
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
                <!-- En tête -->
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>Tableau de bord</h3>
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
                                <li><a class="dropdown-item" href="#" onclick="exporterImage()">
                                    <i class="fas fa-image me-2"></i>En image PNG
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exporterPDF()">
                                    <i class="fas fa-file-pdf me-2"></i>En PDF
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <hr>
                
                <!-- Statistique Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card en-cours h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Projets en cours</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['en_cours'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sync-alt fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card termines h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Projets terminés</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['termines'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card retard h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Projets en retard</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['retard'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card a-venir h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Projets à venir</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['a_venir'] ?? 0; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Projets par commune
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="regionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-1"></i>
                                Statut des projets
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Project Highlights -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-trophy me-1"></i>
                                Top 3 projets les plus avancés
                            </div>
                            <div class="card-body">
                                <?php if (!empty($top_avances)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach($top_avances as $projet): ?>
                                            <div class="list-group-item">
                                                <div class="row align-items-center">
                                                    <div class="col-3">
                                                        <img src="<?php echo $projet['photo'] ?>" 
                                                             alt="<?php echo htmlspecialchars($projet['nom_projet']); ?>" 
                                                             class="project-image">
                                                    </div>
                                                    <div class="col-9 ps-3">
                                                        <div class="fw-bold"><?php echo htmlspecialchars($projet['nom_projet']); ?></div>
                                                        <div class="progress mt-2">
                                                            <div class="progress-bar bg-success" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $projet['avancement']; ?>%">
                                                                <?php echo $projet['avancement']; ?>%
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">Date fin: <?php echo date('d/m/Y', strtotime($projet['date_fin'])); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                        Aucun projet en cours avec avancement élevé
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Top 3 projets critiques
                            </div>
                            <div class="card-body">
                                <?php if (!empty($top_critiques)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach($top_critiques as $projet): ?>
                                            <div class="list-group-item">
                                                <div class="row align-items-center">
                                                    <div class="col-3">
                                                        <img src="<?php echo $projet['photo']?>" 
                                                             alt="<?php echo htmlspecialchars($projet['nom_projet']); ?>" 
                                                             class="project-image">
                                                    </div>
                                                    <div class="col-9 ps-4">
                                                        <div class="fw-bold"><?php echo htmlspecialchars($projet['nom_projet']); ?></div>
                                                        <div class="mt-2">
                                                            <span class="badge badge-retard">
                                                                <?php 
                                                                $jours = $projet['jours_retard'];
                                                                if ($jours >= 30) {
                                                                    echo floor($jours/30) . ' mois';
                                                                } else {
                                                                    echo $jours . ' jour(s)';
                                                                }
                                                                ?>
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">Date fin: <?php echo date('d/m/Y', strtotime($projet['date_fin'])); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                                        Aucun projet en retard critique
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
        // Attendre que la page soit complètement chargée
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des projets par commune
            const regionCtx = document.getElementById('regionChart');
            if (regionCtx) {
                new Chart(regionCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo $regions_labels_json; ?>,
                        datasets: [{
                            label: 'Nombre de projets',
                            data: <?php echo $regions_counts_json; ?>,
                            backgroundColor: [
                                '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6c757d', '#0dcaf0'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Graphique des statuts des projets
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo $statuts_labels_json; ?>,
                        datasets: [{
                            data: <?php echo $statuts_counts_json; ?>,
                            backgroundColor: [
                                '#0d6efd', // En cours
                                '#198754', // Terminé  
                                '#dc3545', // En retard
                                '#ffc107'  // À venir
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
        // Fonction pour exporter en image 
        async function exporterImage() {
            const btn = event.target.closest('.dropdown-item');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
            btn.disabled = true;
            
            try {
                // Utiliser html2canvas avec des options basiques
                const canvas = await html2canvas(document.querySelector('.col-lg-10.col-md-9'), {
                    scale: 1,
                    useCORS: true,
                    logging: false
                });
                
                // Créer le lien de téléchargement
                const link = document.createElement('a');
                link.download = 'dashboard_sisag_' + new Date().toISOString().split('T')[0] + '.png';
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showCustomAlert(' Dashboard exporté en image!', 'success');
            } catch (error) {
                console.error('Erreur export:', error);
                showCustomAlert(' Erreur lors de l\'export. Vérrez la console.', 'danger');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

    // Fonction pour exporter en PDF 
    async function exporterPDF() {
        const btn = event.target.closest('.dropdown-item');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération PDF...';
        btn.disabled = true;
        
        try {
            const canvas = await html2canvas(document.querySelector('.col-lg-10.col-md-9'), {
                scale: 1.5,
                useCORS: true,
                logging: false
            });
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const imgData = canvas.toDataURL('image/jpeg', 0.8);
            
            // Dimensions
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const imgWidth = pageWidth - 20; // Marges
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            
            // Ajouter l'image
            doc.addImage(imgData, 'JPEG', 10, 10, imgWidth, imgHeight);
            doc.save('dashboard_sisag_' + new Date().toISOString().split('T')[0] + '.pdf');
            
            showCustomAlert(' Dashboard exporté en PDF!','success');
        } catch (error) {
            console.error('Erreur export PDF:', error);
            showCustomAlert(' Erreur export PDF. Vérrez la console.','danger');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
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
    <script src="../shared/sidebar-drawer.js"></script>
</body>
</html>

