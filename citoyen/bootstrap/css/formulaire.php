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
    <title>formulaire</title>
    <style>
        .required:after {
            content: " *";
            color: red;
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
    
    <div class="container">
        <!-- Messages d'alerte -->
        <?php
        session_start();
        
        // Afficher les messages d'erreur
        if (isset($_SESSION['error_message'])): 
        ?>
            <div class="alert alert-danger-custom alert-custom alert-dismissible fade show mx-auto mt-4" style="max-width: 800px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                    <div>
                        <h5 class="alert-heading mb-2">Erreur d'inscription</h5>
                        <div class="mb-0"><?php echo $_SESSION['error_message']; ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <?php 
            // Supprimer le message après affichage
            unset($_SESSION['error_message']);
        endif; 
        
        // Afficher les messages de succès
        if (isset($_SESSION['success_message'])): 
        ?>
            <div class="alert alert-success-custom alert-custom alert-dismissible fade show mx-auto mt-4" style="max-width: 800px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill alert-icon"></i>
                    <div>
                        <h5 class="alert-heading mb-2">Inscription réussie !</h5>
                        <div class="mb-0"><?php echo $_SESSION['success_message']; ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close-custom position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close" onclick="closeAlert(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <?php 
            // Supprimer le message après affichage
            unset($_SESSION['success_message']);
        endif; 
        ?>

        <div class="form-container pb-4 w-50 mx-auto rounded-3 mt-5" style="box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
            <div class="form-header rounded-top pt-2 pb-1 ps-2 bg-dark text-white">
                <p><i class="bi bi-person-plus-fill me-2"></i>Formulaire d'Inscription</p>
            </div>
            
            <div class="form-body">
                <form action="formulaire1.php" method="POST" id="inscriptionForm">
                    <div class="row ps-3 pe-3">
                        <p>Inscrivez-vous pour profiter de plusieurs de nos fonctionnalités.</p>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label class="form-label required"><strong>Nom</strong></label>
                                            <input type="text" name="nom" placeholder="Nom*" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label class="form-label required"><strong>Prénom</strong></label>
                                            <input type="text" name="prenom" placeholder="Prénom*" class="form-control rounded-2" required>
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label class="form-label required"><strong>Sexe</strong></label><br>
                                            <select class="form-select" name="sexe" required>
                                                <option value="">--Sélectionner--</option>
                                                <option value="M">Homme</option>
                                                <option value="F">Femme</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label class="form-label required"><strong>Email</strong></label>
                                            <input type="email" name="email" placeholder="Email*" class="form-control rounded-2">
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label for="loginPassword required" class="form-label">Mot de passe</label>
                                            <input type="password" placeholder="●●●●●●●●" class="form-control" name="mot_de_passe" required>
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label for="loginPassword2 required" class="form-label">Confirmer le mot de passe</label>
                                            <input type="password" placeholder="●●●●●●●●" class="form-control" name="mot_de_passe_confirm" required>
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label class="form-label required"><strong>Téléphone</strong></label>
                                            <input type="tel" name="telephone" placeholder="+243 XX XXX XXXX" class="form-control rounded-2">
                                        </div>
                                        <div class="col-xl-6 col-md-12 form-group mt-3">
                                            <label class="form-label required"><strong>Profession</strong></label>
                                            <input type="text" name="profession" placeholder="Profession*" class="form-control rounded-2">
                                        </div>
                                        <!-- Boutons d'action -->
                                        <div class="d-flex justify-content-center mt-4 pt-3 border-top">
                                            <a href="accueil.php" class="btn btn-secondary me-3">
                                                <i class="bi bi-x-circle me-2 mb-3"></i>Annuler
                                            </a>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-person-check me-2"></i>S'inscrire
                                            </button>
                                        </div>
                                    </div>
                </form>
            </div>
        </div>
    </div>
        
    <script>
            // Validation basique du formulaire
            document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
            const telephone = document.querySelector('input[name="telephone"]').value;
            const email = document.querySelector('input[name="email"]').value;
            
            // Validation basique du téléphone
            if (!telephone.match(/^\+?[\d\s-]{10,}$/)) {
                alert('Veuillez entrer un numéro de téléphone valide');
                e.preventDefault();
                return;
            }
            
            // Validation basique de l'email
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                alert('Veuillez entrer une adresse email valide');
                e.preventDefault();
                return;
            }
        });

        // Fonction pour fermer les alertes
        function closeAlert(button) {
            const alert = button.closest('.alert');
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }

        // Auto-fermeture des alertes après 5 secondes
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    </script>

</body>
</html>