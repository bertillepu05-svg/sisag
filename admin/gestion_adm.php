<?php
header('Content-Type: text/html; charset=utf-8');
session_name('admin_session');
session_start();

if (!isset($_SESSION['id_adm'])) {
    header("Location: login.php");
    exit;
}
$id_adm = $_SESSION['id_adm'];



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion_adm</title>
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
                            <a class="nav-link" href="projet_avenir.php" data-page="projects">
                                <i class="fas fa-list"></i> Projets à venir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="gestion_adm.php" data-page="projects">
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
                        <h3>Gestion des administrateurs</h3>
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
                <!-- Cartes de statistiques -->
                <div class="row mb-2">
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card stat-card total">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p>Total Administrateurs</p>
                                        <div class="stat-number" id="total-administrateurs">0</div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people-fill fs-1 text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card stat-card actif">
                            <div class="card-body">
                                <p>Statut Actif</p>
                                <div class="stat-number" id="count-actif">0</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card stat-card inactif">
                            <div class="card-body">
                                <p>Statut Inactif</p>
                                <div class="stat-number" id="count-inactif">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- tableau-->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list me-1"></i>
                        <span>Liste des administrateurs</span>
                    </div>
                    <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="regionProjectsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Sexe</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <?php
                                $conn = new mysqli("localhost", "root", "", "sisag");
                                if($conn->connect_error){
                                    die("erreur".$conn->connect_error);
                                }
                                
                                $sql = "SELECT id_adm, nom_adm, prenom_adm, sexe, email, telephone, statut FROM administrateur ORDER BY id_adm ASC";
                                $result = $conn->query($sql); 

                                if($result->num_rows > 0){
                                    while($ligne = $result->fetch_assoc()){
                                    echo "<tr>"; 
                                        echo "<td>".$ligne["id_adm"]."</td>";
                                        echo "<td>".$ligne["nom_adm"]."</td>";
                                        echo "<td>".$ligne["prenom_adm"]."</td>";
                                        echo "<td>".$ligne["sexe"]."</td>";
                                        echo "<td>".$ligne["email"]."</td>";
                                        echo "<td>".$ligne["telephone"]."</td>";
                                        $statut_class = ($ligne["statut"] == "Actif") ? "badge bg-success" : "badge bg-secondary";
                                        echo "<td><span class='".$statut_class."'>" . $ligne["statut"] ."</span></td>";
                                        
                                        echo "<td>
                                                <div class='d-flex'>
                                                    <a href='' class='btn btn-warning btn-sm'>
                                                        <i class='bi bi-pencil-square'></i> Modifier
                                                    </a>
                                                    <a href='' 
                                                    onclick='return confirm(\"Voulez-vous vraiment supprimer cet administrateur ?\");' 
                                                    class='btn btn-danger btn-sm ms-3'>
                                                        <i class='bi bi-trash'></i> Supprimer
                                                    </a>
                                                </div>
                                            </td>";
                                    echo "</tr>";
                                    }    
                                } else {
                                    echo "<tr><td colspan='9' class='text-center'>Aucun administrateur trouvé</td></tr>";
                                }
                                ?>
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
                                    <h3>Ajouter un administrateur</h3>
                               </div>
                                <form action="gestion_adm1.php" method="POST">
                                    <div class="row">
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Nom</strong></label>
                                            <input type="text" name="nom_adm" placeholder="Nom*" value="<?php echo isset($_SESSION['form_data']['nom_adm']) ? htmlspecialchars($_SESSION['form_data']['nom_adm']) : ''; ?>" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-md-3 form-group mt-3">
                                            <label class="form-label"><strong>Prenom</strong></label>
                                            <input type="text" name="prenom_adm" value="<?php echo isset($_SESSION['form_data']['prenom_adm']) ? htmlspecialchars($_SESSION['form_data']['prenom_adm']) : ''; ?>" placeholder="Prenom*" class="form-control rounded-2" required>
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

            // Fonction pour mettre à jour les compteurs
            function mettreAJourCompteurs() {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'compter_administrateurs.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const resultats = JSON.parse(xhr.responseText);
                            document.getElementById('total-administrateurs').textContent = resultats.total || 0;
                            document.getElementById('count-actif').textContent = resultats.actif || 0;
                            document.getElementById('count-inactif').textContent = resultats.inactif || 0;
                        } catch (e) {
                            console.error('Erreur parsing JSON:', e);
                            reinitialiserCompteurs();
                        }
                    }
                };
                xhr.onerror = function() {
                    console.error('Erreur AJAX');
                    reinitialiserCompteurs();
                };
                xhr.send();
            }

            function reinitialiserCompteurs() {
                document.getElementById('total-administrateurs').textContent = 0;
                document.getElementById('count-actif').textContent = 0;
                document.getElementById('count-inactif').textContent = 0;
            }

            // Charger les données initiales au chargement de la page
            document.addEventListener('DOMContentLoaded', function() {
                mettreAJourCompteurs();
            });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>