
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DÉBUT DE SESSION POUR AFFICHER LES MESSAGES
session_name('citoyen_session');
session_start();
?>

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
        /* Votre CSS reste identique */
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
    </style>
</head>
<body>
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

    <!-- Login Page -->
    <div id="login" class="page-content">
        <div class="login-container">
            <div class="container">
                <div class="row g-0 justify-content-center align-items-stretch pt-5 mt-5">
                    <div class="col-xl-3 col-md-5 pt-5 border rounded-4 p-3" style="box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
                        <!-- Le formulaire envoie vers login1.php -->
                        <form action="login1.php" method="POST">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="exemple : bertillepu05@gmail.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Mot de passe</label>
                                <input type="password" placeholder="●●●●●●●●" class="form-control" name="mot_de_passe" required>
                            </div>
                            <div class="mb-3">
                                <label>
                                    <input type="checkbox" name="remember" value="1"> Se souvenir de moi
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary">Se connecter</button>
                            </div>
                            <div class="mt-3 mb-2">
                                <span class="lien mt-3">Pas de compte ? <a href="formulaire.php">Inscrivez-vous</a></span>
                            </div>
                        </form>
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
</body>
</html>

