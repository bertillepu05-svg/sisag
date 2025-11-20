<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRACK-GOV RDC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">🏠 TRACK-GOV RDC</a>
        </div>
    </nav>

    <!-- Page d'accueil -->
    <div class="container mt-4">
        <!-- Cartes résumé -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h4>47</h4>
                        <p>Projets Totaux</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h4>28</h4>
                        <p>En Cours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h4>12</h4>
                        <p>En Retard</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h4>7</h4>
                        <p>Bloqués</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des projets -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>📋 Liste des Projets Gouvernementaux</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Projet</th>
                            <th>Ministère</th>
                            <th>Statut</th>
                            <th>Avancement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Route Kinshasa-Matadi</td>
                            <td>Infrastructures</td>
                            <td><span class="badge bg-warning">En retard</span></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: 45%">45%</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Hôpital de Goma</td>
                            <td>Santé</td>
                            <td><span class="badge bg-success">Terminé</span></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: 100%">100%</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>