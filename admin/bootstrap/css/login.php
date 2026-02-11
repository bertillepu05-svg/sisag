<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>login</title>
    <style>

    </style>
</head>
<body>
        <!-- Login Page -->
        <div id="login" class="page-content">
            <div class="login-container">
                <div class="container">
                    <div class="row g-0  justify-content-center align-items-stretch pt-5 mt-5">
                        <div class="col-xl-3 col-md-3 pt-5 border rounded-4 p-3" style="box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
                            <div class="text-center mb-4">
                                <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                                <h2>Connexion Admin</h2>
                                <p class="text-muted">Accès réservé aux administrateurs</p>
                            </div>
                            
                            <form action="login1.php" method="POST">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="bertillepu05@gmail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Mot de passe</label>
                                    <input type="password" placeholder="sisag"class="form-control" name="mot_de_passe" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="login" class="btn btn-primary">Se connecter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>