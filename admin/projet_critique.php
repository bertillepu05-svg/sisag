
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

// Récupérer les projets en retard
$sql_projets_retard = "SELECT COUNT(*) as total FROM projet WHERE statut = 'En retard'";
$result_count = $conn->query($sql_projets_retard);
$total_retard = $result_count->fetch_assoc()['total'];


// Récupérer les projets avec pagination
$limit = 5; // Nombre de projets par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_projets = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, date_debut, date_fin, avancement, photo, descript, objectif 
                FROM projet 
                WHERE statut = 'En retard'
                ORDER BY id_projet ASC 
                LIMIT $limit OFFSET $offset";

$result_projets = $conn->query($sql_projets);

// Calculer le nombre total de pages
$sql_total_pages = "SELECT COUNT(*) as total FROM projet WHERE statut = 'En retard'";
$result_total_pages = $conn->query($sql_total_pages);
$total_projets = $result_total_pages->fetch_assoc()['total'];
$total_pages = ceil($total_projets / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets Critiques</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Bibliothèques pour l'export PDF -->
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
        .stat-card.retard {
            border-left-color: var(--danger-color);
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
            height: 15px;
        }
        
        .table-danger {
            background-color: #f8d7da;
        }
        
        .alert-critique {
            border-left: 4px solid #dc3545;
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
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
                            <a class="nav-link active" href="projet_critique.php" data-page="projects">
                                <i class="fas fa-list"></i> Projets critiques
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="projet_avenir.php" data-page="projects">
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
                <!-- Alertes si aucun projet en retard -->
                <?php if ($total_retard == 0): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Aucun projet critique</h5>
                        <p class="mb-0">Félicitations ! Aucun projet n'est actuellement en retard.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-critique alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Projets nécessitant une attention immédiate</h5>
                        <p class="mb-0"><strong><?php echo $total_retard; ?> projet(s)</strong> sont en retard sur leur planning. Veuillez prendre les mesures nécessaires.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- En tête -->
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>Projets Critiques</h3>
                    </div>
                   <div class="d-flex align-items-center">
                        <span class="navbar-text" style="font-size : 20px;">
                        <i class="bi bi-person-gear"></i> Espace administrateur
                        </span>
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
                
                <!-- Statistique Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card retard h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Projets en retard</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_retard; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistique supplémentaire : Jours de retard moyen -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100" style="border-left-color: #e74a3b;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Jours de retard moyen</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php
                                            $sql_retard_moyen = "SELECT AVG(DATEDIFF('$aujourdhui', date_fin)) as retard_moyen 
                                                               FROM projet WHERE statut = 'En retard'";
                                            $result_retard = $conn->query($sql_retard_moyen);
                                            $retard_moyen = $result_retard->fetch_assoc()['retard_moyen'];
                                            echo $retard_moyen ? round($retard_moyen) . ' jours' : '0 jour';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-times fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- tableau-->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span>Liste des projets en retard</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="regionProjectsTable">
                                <thead class="table-dar">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom du projet</th>
                                        <th>Commune</th>
                                        <th>Quartier</th>
                                        <th>Ministère</th>
                                        <th>Statut</th>
                                        <th>Budget</th>
                                        <th>Date_début</th>
                                        <th>Date_fin_prévue</th>
                                        <th>Jours_de_retard</th>
                                        <th>Avancement</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupérer les projets en retard avec calcul des jours de retard
                                    $sql = "SELECT id_projet, nom_projet, commune, quartier, ministere, statut, budget, 
                                                date_debut, date_fin, avancement, descript, objectif,
                                                DATEDIFF('$aujourdhui', date_fin) as jours_retard
                                            FROM projet 
                                            WHERE statut = 'En retard' 
                                            ORDER BY jours_retard DESC, date_fin ASC";
                                    $result = $conn->query($sql);

                                    if($result->num_rows > 0){
                                        while($ligne = $result->fetch_assoc()){
                                            $jours_retard = $ligne['jours_retard'];
                                            $classe_retard = $jours_retard > 30 ? 'table-danger' : '';
                                            
                                            echo "<tr class='$classe_retard'>"; 
                                            echo"<td>".$ligne["id_projet"]."</td>";
                                            echo "<td>" . htmlspecialchars($ligne["nom_projet"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($ligne["commune"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($ligne["quartier"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($ligne["ministere"]) . "</td>";
                                            
                                            // Statut avec badge
                                            echo "<td><span class='badge badge-statut badge-en-retard'>" . $ligne["statut"] . "</span></td>";
                                            
                                            echo "<td>" . number_format($ligne["budget"], 0) . " $</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($ligne["date_debut"])) . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($ligne["date_fin"])) . "</td>";
                                            
                                            // Jours de retard avec couleur selon la gravité
                                            $couleur_retard = $jours_retard > 30 ? 'text-danger fw-bold' : ($jours_retard > 15 ? 'text-warning' : 'text-secondary');
                                            echo "<td><span class='$couleur_retard'>" . $jours_retard . " jour(s)</span></td>";
                                            
                                            // Avancement avec barre de progression
                                            $couleur_progress = $ligne['avancement'] < 50 ? 'bg-danger' : ($ligne['avancement'] < 80 ? 'bg-warning' : 'bg-info');
                                            echo "<td>
                                                    <div class='progress'>
                                                        <div class='progress-bar $couleur_progress' 
                                                            role='progressbar' 
                                                            style='width: " . $ligne['avancement'] . "%' 
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
                                                            <a href='modifier_critique.php?id=".$ligne['id_projet']."' class='btn btn-warning btn-sm'>
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
                                        echo "<tr><td colspan='12' class='text-center text-muted py-4'>
                                                <i class='bi bi-check-circle display-4 text-success'></i><br>
                                                Aucun projet en retard
                                            </td></tr>";
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
    <script>
        // Fonction pour exporter en PDF
        function exporterPDF() {
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('p', 'mm', 'a4');
                
                // Titre du document
                const dateExport = new Date().toLocaleDateString('fr-FR');
                const totalProjets = <?php echo $total_projets; ?>;
                
                // En-tête
                doc.setFontSize(16);
                doc.setTextColor(220, 53, 69); // Rouge pour les projets critiques
                doc.text('PROJETS CRITIQUES - SISAG', 105, 15, { align: 'center' });
                
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Export du ${dateExport} - ${totalProjets} projet(s) en retard`, 105, 22, { align: 'center' });
                
                // Statistiques
                doc.setFontSize(9);
                doc.setTextColor(40, 40, 40);
                doc.text(`Jours de retard moyen: <?php echo $retard_moyen ? round($retard_moyen) . ' jours' : '0 jour'; ?>`, 14, 32);
                
                // Préparer les données du tableau
                const headers = [
                    'ID', 
                    'Nom Projet', 
                    'Commune', 
                    'Quartier', 
                    'Ministère', 
                    'Jours Retard', 
                    'Budget ($)', 
                    'Avancement'
                ];
                
                const data = [];
                
                // Récupérer les données du tableau HTML
                const rows = document.querySelectorAll('#projetsCritiquesTable tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        const rowData = [
                            cells[0].textContent.trim(),
                            cells[1].textContent.trim().substring(0, 25), // limite la longueur
                            cells[2].textContent.trim(),
                            cells[3].textContent.trim(),
                            cells[4].textContent.trim(),
                            cells[9].textContent.trim(),
                            cells[6].textContent.trim(),
                            cells[10].textContent.trim()
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
                        fillColor: [220, 53, 69], // Rouge pour les projets critiques
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: {
                        fillColor: [255, 245, 245] // Fond rouge très clair
                    },
                    columnStyles: {
                        0: { cellWidth: 15 }, // ID
                        1: { cellWidth: 35 }, // Nom Projet
                        2: { cellWidth: 25 }, // Commune
                        3: { cellWidth: 25 }, // Quartier
                        4: { cellWidth: 25 }, // Ministère
                        5: { cellWidth: 20 }, // Jours Retard
                        6: { cellWidth: 25 }, // Budget
                        7: { cellWidth: 20 }  // Avancement
                    },
                    margin: { top: 40 }
                });
                
                // Pied de page
                const pageCount = doc.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.setTextColor(100, 100, 100);
                    doc.text(`Page ${i} / ${pageCount} - SISAG Projets Critiques - ${dateExport}`, 105, doc.internal.pageSize.height - 10, { align: 'center' });
                }
                
                // Sauvegarder le PDF
                doc.save(`projets_critiques_${dateExport}.pdf`);
                
                // Message de confirmation
                showAlert('Liste des projets critiques exportée en PDF avec succès!', 'success');
                
            } catch (error) {
                console.error('Erreur export PDF:', error);
                showAlert('Erreur lors de l\'export PDF', 'danger');
            }
        }

        // Fonction pour exporter en Excel (CSV)
        function exporterExcel() {
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
                    'Jours Retard', 
                    'Avancement (%)'
                ];
                
                let csvContent = headers.join(';') + '\n';
                
                // Récupérer les données du tableau HTML
                const rows = document.querySelectorAll('#projetsCritiquesTable tbody tr');
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
                            cells[9].textContent.trim().replace(' jour(s)', ''),
                            cells[10].textContent.trim().replace('%', '')
                        ];
                        csvContent += rowData.join(';') + '\n';
                    }
                });
                
                // Créer et télécharger le fichier CSV
                const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const dateExport = new Date().toLocaleDateString('fr-FR');
                link.download = `projets_critiques_${dateExport}.csv`;
                link.href = URL.createObjectURL(blob);
                link.click();
                URL.revokeObjectURL(link.href);
                
                showAlert('Liste des projets critiques exportée en CSV avec succès!', 'success');
                
            } catch (error) {
                console.error('Erreur export CSV:', error);
                showAlert('Erreur lors de l\'export CSV', 'danger');
            }
        }

        // Fonction pour afficher les messages d'alerte
        function showAlert(message, type) {
            const alertClass = {
                'success': 'alert-success',
                'danger': 'alert-danger'
            }[type] || 'alert-info';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', alertHtml);

            // Auto-supprimer après 5 secondes
            setTimeout(() => {
                const alert = document.querySelector('.position-fixed.alert');
                if (alert) alert.remove();
            }, 5000);
        }

        // Initialisation Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            // Les dropdowns Bootstrap devraient fonctionner maintenant
        });
        </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>


