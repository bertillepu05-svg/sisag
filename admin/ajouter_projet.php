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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter_projet</title>
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
                            <a class="nav-link active" href="ajouter_projet.php" data-page="projects">
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
                        <h3>Ajouter un projet</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="navbar-text" style="font-size : 20px;">
                        <i class="bi bi-person-gear"></i> Espace administrateur
                        </span>
                    </div>
                </div>
                <hr>
                <!-- Ajout projet -->
                <div id="add-project" class="page-content">
                    <div class="card form-container">
                        <div class="card-body">
                            <form action="ajouter_projet1.php" method="POST" id="projectForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="projectName" class="form-label">Nom du projet</label>
                                        <textarea class="form-control" name="nom_projet" rows="1" required></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projectRegion" class="form-label">Communes</label>
                                        <select class="form-select" name="commune" required>
                                            <option value="">--Selectionner--</option>
                                            <option value="Kinshasa">Kinshasa</option>
                                            <option value="Ngaliema">Ngaliema</option>
                                            <option value="Lemba">Lemba</option>
                                            <option value="N'djili">N'djili</option>
                                            <option value="Ngaba">Ngaba</option>
                                            <option value="Nsele">Nsele</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="projetQuartier" class="form-label">Quartier</label>
                                        <input type="text" class="form-control" name="quartier" placeholder="Quartier*" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="projectMinistry" class="form-label">Ministère / Secteur</label>
                                        <select class="form-select" name="ministere" required>
                                            <option value="">Sélectionner un ministère</option>
                                            <option value="Éducation">Éducation</option>
                                            <option value="Santé">Santé</option>
                                            <option value="Infrastructures">Infrastructures</option>
                                            <option value="Énergie">Énergie</option>
                                            <option value="Agriculture">Agriculture</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="projectStatus" class="form-label">Statut initial (automatique)</label>
                                        <select class="form-select" name="statut" id="statut" required readonly>
                                            <option value="">Le statut sera déterminé automatiquement</option>
                                        </select>
                                        <small class="form-text text-muted">Le statut est calculé automatiquement selon les dates</small>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="projectbudget" class="form-label">Budget</label>
                                        <input type="number" name="budget" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projectStartDate" class="form-label">Date début</label>
                                        <input type="date" name="date_debut" id="date_debut" class="form-control" required onchange="calculerStatutAutomatique()">
                                    </div>
                                    
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="projectEndDate" class="form-label">Date fin prévue</label>
                                        <input type="date" class="form-control" name="date_fin" id="date_fin" required onchange="calculerStatutAutomatique()">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projectAvance" class="form-label">Avancement</label>
                                        <input type="number" class="form-control" name="avancement" id="avancement" value="0" min="0" max="100" readonly>
                                        <small class="form-text text-muted">L'avancement est automatiquement à 0% et passera à 100% quand le projet sera terminé</small>
                                    </div>
                                   
                                </div>
                                <div class="mb-3 mt-3">
                                    <label for="projectDescription" class="form-label">Description</label>
                                    <textarea class="form-control" name="descript" rows="4" placeholder="Ajouter une brève description*" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="projectObject" class="form-label">Objectif</label>
                                    <textarea class="form-control" rows="4" name="objectif" placeholder="Décrivez quelques objectifs de votre projet*" required></textarea>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="ajout" class="btn btn-primary">Ajouter le projet</button>
                                    <button type="reset" class="btn btn-outline-secondary" onclick="resetForm()">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function calculerStatutAutomatique() {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            const statutSelect = document.getElementById('statut');
            const avancementInput = document.getElementById('avancement');
            
            if (!dateDebut || !dateFin) {
                return;
            }
            
            const aujourdhui = new Date().toISOString().split('T')[0];
            const debut = new Date(dateDebut);
            const fin = new Date(dateFin);
            const aujourdhuiDate = new Date(aujourdhui);
            
            let statut = '';
            let avancement = 0;
            
            // Logique de détermination du statut
            if (aujourdhuiDate < debut) {
                // Date début pas encore arrivée
                statut = 'À venir';
                avancement = 0;
            } else if (aujourdhuiDate >= debut && aujourdhuiDate <= fin) {
                // Projet en cours
                statut = 'En cours';
                avancement = 0;
            } else if (aujourdhuiDate > fin) {
                // Projet terminé (date fin dépassée)
                statut = 'Terminé';
                avancement = 100;
            }
            
            // Mise à jour de l'interface
            statutSelect.innerHTML = <option value="${statut}">${statut}</option>;
            avancementInput.value = avancement;
            
            // Affichage d'un message informatif
            afficherMessageStatut(statut);
        }
        
        function afficherMessageStatut(statut) {
            // Supprimer les messages existants
            const existingAlert = document.getElementById('statut-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            let message = '';
            let type = '';
            
            switch(statut) {
                case 'À venir':
                    message = 'Le projet est programmé pour le futur. Statut défini sur "À venir".';
                    type = 'info';
                    break;
                case 'En cours':
                    message = 'Le projet est en cours d\'exécution. Statut défini sur "En cours".';
                    type = 'warning';
                    break;
                case 'Terminé':
                    message = 'Le projet est terminé. Statut défini sur "Terminé" et avancement à 100%.';
                    type = 'success';
                    break;
            }
            
            if (message) {
                const alertDiv = document.createElement('div');
                alertDiv.id = 'statut-alert';
                alertDiv.className = alert alert-${type} alert-dismissible fade show mt-3;
                alertDiv.innerHTML = `
                    <i class="bi bi-info-circle-fill me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.querySelector('form').insertBefore(alertDiv, document.querySelector('.d-grid'));
            }
        }
        
        function resetForm() {
            // Réinitialiser le formulaire
            document.getElementById('statut').innerHTML = '<option value="">Le statut sera déterminé automatiquement</option>';
            document.getElementById('avancement').value = '0';
            
            // Supprimer les messages d'alerte
            const existingAlert = document.getElementById('statut-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier si des dates sont déjà remplies (au cas où)
            calculerStatutAutomatique();
        });

           

            function calculerStatutAutomatique() {
                const dateDebut = document.getElementById('date_debut').value;
                const dateFin = document.getElementById('date_fin').value;
                const statutSelect = document.getElementById('statut');
                const avancementInput = document.getElementById('avancement');
                
                if (!dateDebut || !dateFin) {
                    return;
                }
                
                const aujourdhui = new Date().toISOString().split('T')[0];
                const debut = new Date(dateDebut);
                const fin = new Date(dateFin);
                const aujourdhuiDate = new Date(aujourdhui);
                
                let statut = '';
                let avancement = 0;
                
                // Logique de détermination du statut
                if (aujourdhuiDate < debut) {
                    statut = 'À venir';
                    avancement = 0;
                } else if (aujourdhuiDate >= debut && aujourdhuiDate <= fin) {
                    statut = 'En cours';
                    avancement = 0;
                } else if (aujourdhuiDate > fin) {
                    statut = 'Terminé';
                    avancement = 100;
                }
                
                // Mise à jour de l'interface
                statutSelect.innerHTML = `<option value="${statut}">${statut}</option>`;
                avancementInput.value = avancement;
                
                // Affichage d'un message informatif
                afficherMessageStatut(statut);
            }

            function afficherMessageStatut(statut) {
                const existingAlert = document.getElementById('statut-alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                let message = '';
                let type = '';
                
                switch(statut) {
                    case 'À venir':
                        message = 'Le projet est programmé pour le futur. Statut défini sur "À venir".';
                        type = 'info';
                        break;
                    case 'En cours':
                        message = 'Le projet est en cours d\'exécution. Statut défini sur "En cours".';
                        type = 'warning';
                        break;
                    case 'Terminé':
                        message = 'Le projet est terminé. Statut défini sur "Terminé" et avancement à 100%.';
                        type = 'success';
                        break;
                }
                
                if (message) {
                    const alertDiv = document.createElement('div');
                    alertDiv.id = 'statut-alert';
                    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                    alertDiv.innerHTML = `
                        <i class="fas fa-info-circle me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    document.querySelector('form').insertBefore(alertDiv, document.querySelector('.d-grid'));
                }
            }

            function resetForm() {
                document.getElementById('statut').innerHTML = '<option value="">Le statut sera déterminé automatiquement</option>';
                document.getElementById('avancement').value = '0';
                document.getElementById('image-preview').innerHTML = '';
                
                const existingAlert = document.getElementById('statut-alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                calculerStatutAutomatique();
            });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>


