<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiSAG - Suivi des Projets Gouvernementaux</title>
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
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
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
        
        .progress {
            height: 10px;
        }
        
        .project-card {
            transition: transform 0.2s;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
        }
        
        .map-container {
            height: 300px;
            background-color: #e9ecef;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .page-content {
            display: none;
        }
        
        .page-content.active {
            display: block;
        }
        
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .region-btn {
            margin: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line me-2"></i>SiSAG
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="loginLink">
                            <i class="fas fa-sign-in-alt"></i> Connexion Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-page="dashboard">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="projects">
                                <i class="fas fa-list"></i> Liste des projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="regions">
                                <i class="fas fa-map-marker-alt"></i> Projets par région
                            </a>
                        </li>
                        <li class="nav-item admin-only" style="display: none;">
                            <a class="nav-link" href="#" data-page="add-project">
                                <i class="fas fa-plus-circle"></i> Ajouter un projet
                            </a>
                        </li>
                        <li class="nav-item admin-only" style="display: none;">
                            <a class="nav-link" href="#" id="logoutLink">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Dashboard Page -->
                <div id="dashboard" class="page-content active">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Tableau de bord</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary">Exporter</button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card en-cours h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Projets en cours</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
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
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
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
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">7</div>
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
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Map -->
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-1"></i>
                                    Projets par région
                                </div>
                                <div class="card-body">
                                    <canvas id="regionChart" width="100%" height="40"></canvas>
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
                                    <canvas id="statusChart" width="100%" height="40"></canvas>
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
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Construction d'écoles à Kinshasa
                                            <span class="badge bg-success rounded-pill">95%</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Réhabilitation des routes à Lubumbashi
                                            <span class="badge bg-success rounded-pill">88%</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Programme d'accès à l'eau potable
                                            <span class="badge bg-success rounded-pill">82%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header bg-danger text-white">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    3 projets en retard critique
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Centre de santé à Goma
                                            <span class="badge bg-danger rounded-pill">-3 mois</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Pont sur le fleuve Congo
                                            <span class="badge bg-danger rounded-pill">-2 mois</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Réseau électrique rural
                                            <span class="badge bg-danger rounded-pill">-1 mois</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects List Page -->
                <div id="projects" class="page-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Liste des projets</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary">Exporter</button>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="regionFilter" class="form-label">Région</label>
                                    <select class="form-select" id="regionFilter">
                                        <option value="">Toutes les régions</option>
                                        <option value="Kinshasa">Kinshasa</option>
                                        <option value="Kongo Central">Kongo Central</option>
                                        <option value="Nord-Kivu">Nord-Kivu</option>
                                        <option value="Sud-Kivu">Sud-Kivu</option>
                                        <option value="Katanga">Katanga</option>
                                        <option value="Kasai">Kasai</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="statusFilter" class="form-label">Statut</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">Tous les statuts</option>
                                        <option value="En cours">En cours</option>
                                        <option value="Terminé">Terminé</option>
                                        <option value="En retard">En retard</option>
                                        <option value="À venir">À venir</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="ministryFilter" class="form-label">Ministère</label>
                                    <select class="form-select" id="ministryFilter">
                                        <option value="">Tous les ministères</option>
                                        <option value="Éducation">Éducation</option>
                                        <option value="Santé">Santé</option>
                                        <option value="Infrastructures">Infrastructures</option>
                                        <option value="Énergie">Énergie</option>
                                        <option value="Agriculture">Agriculture</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="searchInput" class="form-label">Recherche</label>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Nom du projet...">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button class="btn btn-primary" id="applyFilters">Appliquer les filtres</button>
                                    <button class="btn btn-outline-secondary" id="resetFilters">Réinitialiser</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="projectsTable">
                                    <thead>
                                        <tr>
                                            <th>Nom du projet</th>
                                            <th>Région</th>
                                            <th>Ministère</th>
                                            <th>Statut</th>
                                            <th>Date début</th>
                                            <th>Date fin prévue</th>
                                            <th>Avancement</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Construction d'écoles à Kinshasa</td>
                                            <td>Kinshasa</td>
                                            <td>Éducation</td>
                                            <td><span class="badge bg-primary">En cours</span></td>
                                            <td>15/03/2022</td>
                                            <td>30/06/2023</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 95%">95%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-project" data-id="1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Centre de santé à Goma</td>
                                            <td>Nord-Kivu</td>
                                            <td>Santé</td>
                                            <td><span class="badge bg-danger">En retard</span></td>
                                            <td>10/01/2022</td>
                                            <td>15/12/2022</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 65%">65%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-project" data-id="2">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pont sur le fleuve Congo</td>
                                            <td>Kongo Central</td>
                                            <td>Infrastructures</td>
                                            <td><span class="badge bg-danger">En retard</span></td>
                                            <td>05/05/2021</td>
                                            <td>20/11/2022</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 78%">78%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-project" data-id="3">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Réhabilitation des routes à Lubumbashi</td>
                                            <td>Katanga</td>
                                            <td>Infrastructures</td>
                                            <td><span class="badge bg-primary">En cours</span></td>
                                            <td>20/08/2022</td>
                                            <td>15/10/2023</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 88%">88%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-project" data-id="4">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Programme d'accès à l'eau potable</td>
                                            <td>Kasai</td>
                                            <td>Énergie</td>
                                            <td><span class="badge bg-primary">En cours</span></td>
                                            <td>12/02/2022</td>
                                            <td>30/09/2023</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 82%">82%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-project" data-id="5">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <nav aria-label="Projects pagination">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Précédent</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Regions Page -->
                <div id="regions" class="page-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Projets par région</h1>
                    </div>

                    <!-- Map -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-map me-1"></i>
                            Carte des régions de la RDC
                        </div>
                        <div class="card-body">
                            <div class="map-container">
                                <p class="text-muted">Carte interactive des provinces de la RDC</p>
                            </div>
                            <div class="mt-3 text-center">
                                <button class="btn btn-outline-primary region-btn" data-region="Kinshasa">Kinshasa</button>
                                <button class="btn btn-outline-primary region-btn" data-region="Kongo Central">Kongo Central</button>
                                <button class="btn btn-outline-primary region-btn" data-region="Nord-Kivu">Nord-Kivu</button>
                                <button class="btn btn-outline-primary region-btn" data-region="Sud-Kivu">Sud-Kivu</button>
                                <button class="btn btn-outline-primary region-btn" data-region="Katanga">Katanga</button>
                                <button class="btn btn-outline-primary region-btn" data-region="Kasai">Kasai</button>
                            </div>
                        </div>
                    </div>

                    <!-- Region Projects -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-list me-1"></i>
                            Projets de la région: <span id="selectedRegion">Toutes les régions</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="regionProjectsTable">
                                    <thead>
                                        <tr>
                                            <th>Nom du projet</th>
                                            <th>Ministère</th>
                                            <th>Statut</th>
                                            <th>Date début</th>
                                            <th>Date fin prévue</th>
                                            <th>Avancement</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Projects will be dynamically loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Project Page (Admin Only) -->
                <div id="add-project" class="page-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Ajouter un projet</h1>
                    </div>
                    <div class="card form-container">
                        <div class="card-body">
                            <form id="addProjectForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="projectName" class="form-label">Nom du projet</label>
                                        <input type="text" class="form-control" id="projectName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projectRegion" class="form-label">Région</label>
                                        <select class="form-select" id="projectRegion" required>
                                            <option value="">Sélectionner une région</option>
                                            <option value="Kinshasa">Kinshasa</option>
                                            <option value="Kongo Central">Kongo Central</option>
                                            <option value="Nord-Kivu">Nord-Kivu</option>
                                            <option value="Sud-Kivu">Sud-Kivu</option>
                                            <option value="Katanga">Katanga</option>
                                            <option value="Kasai">Kasai</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="projectMinistry" class="form-label">Ministère / Secteur</label>
                                        <select class="form-select" id="projectMinistry" required>
                                            <option value="">Sélectionner un ministère</option>
                                            <option value="Éducation">Éducation</option>
                                            <option value="Santé">Santé</option>
                                            <option value="Infrastructures">Infrastructures</option>
                                            <option value="Énergie">Énergie</option>
                                            <option value="Agriculture">Agriculture</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projectStatus" class="form-label">Statut initial</label>
                                        <select class="form-select" id="projectStatus" required>
                                            <option value="">Sélectionner un statut</option>
                                            <option value="En cours">En cours</option>
                                            <option value="Terminé">Terminé</option>
                                            <option value="En retard">En retard</option>
                                            <option value="À venir">À venir</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="projectDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="projectDescription" rows="4" required></textarea>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="projectStartDate" class="form-label">Date début</label>
                                        <input type="date" class="form-control" id="projectStartDate" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="projectEndDate" class="form-label">Date fin prévue</label>
                                        <input type="date" class="form-control" id="projectEndDate" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="projectImage" class="form-label">Image ou document (facultatif)</label>
                                    <input class="form-control" type="file" id="projectImage">
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">Ajouter le projet</button>
                                    <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Project Details Page -->
                <div id="project-details" class="page-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Détails du projet</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button class="btn btn-sm btn-outline-secondary" id="backToList">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 id="detailProjectName">Construction d'écoles à Kinshasa</h3>
                                    <p class="text-muted" id="detailProjectDescription">
                                        Ce projet vise à construire 50 nouvelles écoles primaires dans la région de Kinshasa pour améliorer l'accès à l'éducation de base.
                                    </p>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <h5>Informations générales</h5>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Région:
                                                    <span id="detailRegion">Kinshasa</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Ministère:
                                                    <span id="detailMinistry">Éducation</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Statut:
                                                    <span id="detailStatus" class="badge bg-primary">En cours</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Date début:
                                                    <span id="detailStartDate">15/03/2022</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Date fin prévue:
                                                    <span id="detailEndDate">30/06/2023</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Avancement</h5>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Progression:</span>
                                                    <span id="detailProgressText">95%</span>
                                                </div>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 95%" id="detailProgressBar">95%</div>
                                                </div>
                                            </div>
                                            
                                            <h5>Objectifs</h5>
                                            <ul id="detailObjectives">
                                                <li>Construction de 50 écoles primaires</li>
                                                <li>Formation de 500 enseignants</li>
                                                <li>Distribution de matériel scolaire</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h5>Historique d'avancement</h5>
                                        <div class="timeline">
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6>Phase 3: Équipement des écoles</h6>
                                                    <p class="text-muted">Achat et distribution du mobilier et équipements scolaires - 85% complété</p>
                                                    <small>Mise à jour: 15/04/2023</small>
                                                </div>
                                            </div>
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6>Phase 2: Construction des bâtiments</h6>
                                                    <p class="text-muted">45 écoles sur 50 sont maintenant construites - 100% complété</p>
                                                    <small>Mise à jour: 28/02/2023</small>
                                                </div>
                                            </div>
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6>Phase 1: Préparation des sites</h6>
                                                    <p class="text-muted">Identification et préparation des 50 sites de construction - 100% complété</p>
                                                    <small>Mise à jour: 30/06/2022</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h5>Images du projet</h5>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <img src="https://via.placeholder.com/150" class="img-fluid rounded" alt="Projet image">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <img src="https://via.placeholder.com/150" class="img-fluid rounded" alt="Projet image">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <img src="https://via.placeholder.com/150" class="img-fluid rounded" alt="Projet image">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <img src="https://via.placeholder.com/150" class="img-fluid rounded" alt="Projet image">
                                        </div>
                                    </div>
                                    
                                    <div class="admin-only mt-4" style="display: none;">
                                        <h5>Actions administrateur</h5>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-warning" id="editProjectBtn">
                                                <i class="fas fa-edit"></i> Modifier le projet
                                            </button>
                                            <button class="btn btn-info" id="updateStatusBtn">
                                                <i class="fas fa-sync-alt"></i> Mettre à jour le statut
                                            </button>
                                            <button class="btn btn-danger" id="deleteProjectBtn">
                                                <i class="fas fa-trash"></i> Supprimer le projet
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Login Page -->
                <div id="login" class="page-content">
                    <div class="login-container">
                        <div class="text-center mb-4">
                            <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                            <h2>Connexion Admin</h2>
                            <p class="text-muted">Accès réservé aux administrateurs</p>
                        </div>
                        
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="loginEmail" placeholder="admin@sisag.cd" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="loginPassword" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Se connecter</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="#" id="backToHome">Retour à l'accueil</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap & Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Sample data for charts
        const regionData = {
            labels: ['Kinshasa', 'Kongo Central', 'Nord-Kivu', 'Sud-Kivu', 'Katanga', 'Kasai'],
            datasets: [{
                label: 'Nombre de projets',
                data: [12, 8, 10, 7, 9, 6],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(201, 203, 207, 0.7)'
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)',
                    'rgb(75, 192, 192)',
                    'rgb(255, 159, 64)',
                    'rgb(153, 102, 255)',
                    'rgb(201, 203, 207)'
                ],
                borderWidth: 1
            }]
        };

        const statusData = {
            labels: ['En cours', 'Terminés', 'En retard', 'À venir'],
            datasets: [{
                data: [24, 18, 7, 12],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 205, 86, 0.7)'
                ],
                hoverOffset: 4
            }]
        };

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Region chart
            const regionCtx = document.getElementById('regionChart').getContext('2d');
            new Chart(regionCtx, {
                type: 'bar',
                data: regionData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Projets par région'
                        }
                    }
                }
            });

            // Status chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Navigation
            const navLinks = document.querySelectorAll('.nav-link[data-page]');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetPage = this.getAttribute('data-page');
                    showPage(targetPage);
                    
                    // Update active nav link
                    navLinks.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // View project details
            const viewProjectButtons = document.querySelectorAll('.view-project');
            viewProjectButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const projectId = this.getAttribute('data-id');
                    showProjectDetails(projectId);
                });
            });

            // Back to list from project details
            document.getElementById('backToList').addEventListener('click', function() {
                showPage('projects');
            });

            // Region buttons
            const regionButtons = document.querySelectorAll('.region-btn');
            regionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const region = this.getAttribute('data-region');
                    filterProjectsByRegion(region);
                    document.getElementById('selectedRegion').textContent = region;
                    
                    // Highlight selected region button
                    regionButtons.forEach(btn => btn.classList.remove('btn-primary'));
                    regionButtons.forEach(btn => btn.classList.add('btn-outline-primary'));
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');
                });
            });

            // Login link
            document.getElementById('loginLink').addEventListener('click', function(e) {
                e.preventDefault();
                showPage('login');
            });

            // Back to home from login
            document.getElementById('backToHome').addEventListener('click', function(e) {
                e.preventDefault();
                showPage('dashboard');
            });

            // Login form
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // In a real app, you would validate credentials with PHP/MySQL
                // For demo purposes, we'll just simulate a successful login
                simulateLogin();
            });

            // Logout
            document.getElementById('logoutLink').addEventListener('click', function(e) {
                e.preventDefault();
                simulateLogout();
            });

            // Add project form
            document.getElementById('addProjectForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // In a real app, you would send this data to PHP to insert into MySQL
                alert('Projet ajouté avec succès! (Fonctionnalité à implémenter avec PHP/MySQL)');
                this.reset();
            });

            // Apply filters
            document.getElementById('applyFilters').addEventListener('click', function() {
                applyFilters();
            });

            // Reset filters
            document.getElementById('resetFilters').addEventListener('click', function() {
                document.getElementById('regionFilter').value = '';
                document.getElementById('statusFilter').value = '';
                document.getElementById('ministryFilter').value = '';
                document.getElementById('searchInput').value = '';
                applyFilters();
            });
        });

        // Function to show specific page
        function showPage(pageId) {
            const pages = document.querySelectorAll('.page-content');
            pages.forEach(page => {
                page.classList.remove('active');
            });
            document.getElementById(pageId).classList.add('active');
        }

        // Function to show project details
        function showProjectDetails(projectId) {
            // In a real app, you would fetch project details from PHP/MySQL based on projectId
            // For demo, we'll just show the details page with sample data
            showPage('project-details');
        }

        // Function to filter projects by region
        function filterProjectsByRegion(region) {
            // In a real app, you would fetch projects for the selected region from PHP/MySQL
            // For demo, we'll just show a message
            const tableBody = document.querySelector('#regionProjectsTable tbody');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">
                        Chargement des projets pour la région: ${region}...
                        <br><small>(Fonctionnalité à implémenter avec PHP/MySQL)</small>
                    </td>
                </tr>
            `;
        }

        // Function to apply filters
        function applyFilters() {
            // In a real app, you would send filter values to PHP and get filtered results from MySQL
            // For demo, we'll just show a message
            alert('Filtres appliqués! (Fonctionnalité à implémenter avec PHP/MySQL)');
        }

        // Function to simulate login
        function simulateLogin() {
            // Show admin-only elements
            const adminElements = document.querySelectorAll('.admin-only');
            adminElements.forEach(element => {
                element.style.display = 'block';
            });
            
            // Change login link to logout
            document.getElementById('loginLink').innerHTML = '<i class="fas fa-user"></i> Admin';
            document.getElementById('loginLink').removeAttribute('href');
            
            // Show dashboard
            showPage('dashboard');
            
            // Update active nav link
            const navLinks = document.querySelectorAll('.nav-link[data-page]');
            navLinks.forEach(nav => nav.classList.remove('active'));
            document.querySelector('.nav-link[data-page="dashboard"]').classList.add('active');
        }

        // Function to simulate logout
        function simulateLogout() {
            // Hide admin-only elements
            const adminElements = document.querySelectorAll('.admin-only');
            adminElements.forEach(element => {
                element.style.display = 'none';
            });
            
            // Change logout to login
            document.getElementById('loginLink').innerHTML = '<i class="fas fa-sign-in-alt"></i> Connexion Admin';
            document.getElementById('loginLink').setAttribute('href', '#');
            
            // Show dashboard
            showPage('dashboard');
            
            // Update active nav link
            const navLinks = document.querySelectorAll('.nav-link[data-page]');
            navLinks.forEach(nav => nav.classList.remove('active'));
            document.querySelector('.nav-link[data-page="dashboard"]').classList.add('active');
        }
    </script>
</body>
</html>