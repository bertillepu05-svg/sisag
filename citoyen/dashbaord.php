<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
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
        width: 98%;
        border-bottom: 3px solid var(--light);
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
                            <a class="nav-link" href="#" id="logoutLink">
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
                        <h4>Tableau de bord</h4>
                    </div>
                    <<div class="d-flex"> 
                        <input class="border rounded-2" style="width:100%; height:27px;" type="search" id="searchInput" placeholder="Rechercher">
                        <button class="btn btn-outline-dark border rounded-2 ps-2 pe-2" typ="submit" style="background-color : white; width:15%; height : 33px;"><i class="fas fa-chart-line "></i></button>
                        <button class="btn btn-outline-dark border rounded-2 ps-2 pe-2" typ="submit" style="background-color : white; width:15%; height : 33px;"><i class="fas fa-chart-line"></i></button>
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
                <!-- Charts -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Projets par région communale
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
                                        <img src="resto.jpg" alt="text" class="photo rounded-2">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Construction d'écoles à Kinshasa
                                            <div class="mb-3 mt-3">
                                                <div class="progress" style="height: 15px; width:190px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 95%" id="detailProgressBar">95%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Réhabilitation des routes à Lubumbashi
                                            <div class="mb-3 mt-3">
                                                <div class="progress" style="height: 15px; width:190px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 75%" id="detailProgressBar">75%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Programme d'accès à l'eau potable
                                             <div class="mb-3 mt-3">
                                                <div class="progress" style="height: 15px; width:190px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 85%" id="detailProgressBar">85%</div>
                                                </div>
                                            </div>
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
                                        <img src="resto.jpg" alt="text" class="photo rounded-2">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Centre de santé à Goma
                                            <div class="mb-3 mt-3">
                                                <div class="progress" style="height: 15px; width:190px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" id="detailProgressBar">95%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Pont sur le fleuve Congo
                                            <div class="mb-3 mt-3">
                                                <div class="progress" style="height: 15px; width:190px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 80%" id="detailProgressBar">80%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Réseau électrique rural
                                             <div class="mb-3 mt-3">
                                                <div class="progress" style="height: 15px; width:190px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 90%" id="detailProgressBar">90%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

</body>
</html>