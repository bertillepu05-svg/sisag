
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISAG - Suivi des Projets Gouvernementaux</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --sisag-blue: #1a56db;
            --sisag-light: #e8f4fd;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        /* Navigation */
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--sisag-blue) !important;
        }
        
        .nav-link {
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--sisag-blue) !important;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--sisag-blue) 0%, #1e40af 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .btn-hero {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: white;
            color: var(--sisap-blue);
            border: 2px solid white;
        }
        
        .btn-primary-custom:hover {
            background: transparent;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-custom {
            border: 2px solid white;
            color: white;
            background: transparent;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: var(--sisag-blue);
            transform: translateY(-2px);
        }
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: white;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: #1f2937;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--sisag-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--sisag-blue);
        }
        
        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .feature-description {
            color: #6b7280;
            line-height: 1.6;
        }
        
        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 80px 0;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--warning-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Projects Preview */
        .projects-section {
            padding: 100px 0;
            background: #f8fafc;
        }
        
        .project-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .project-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .project-content {
            padding: 1.5rem;
        }
        
        .project-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .status-en-cours { background: #dbeafe; color: #1e40af; }
        .status-termine { background: #d1fae5; color: #065f46; }
        .status-a-venir { background: #fef3c7; color: #92400e; }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--success-color) 0%, #16a34a 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Footer */
        .footer {
            background: #1f2937;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }
        
        .footer-links h5 {
            color: var(--warning-color);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .footer-links a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .copyright {
            border-top: 1px solid #374151;
            padding-top: 2rem;
            margin-top: 3rem;
            text-align: center;
            color: #9ca3af;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line me-2"></i>SISAG
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projets">Projets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fonctionnalites">Fonctionnalités</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="liste_projet.php">Voir les projets</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="liste_projet.php">
                            <i class="fas fa-rocket me-2"></i>Explorer
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="accueil">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        Suivi Transparent des <span class="text-warning">Projets Gouvernementaux</span>
                    </h1>
                    <p class="hero-subtitle">
                        Découvrez, suivez et participez au développement de Kinshasa. 
                        Une plateforme citoyenne pour un gouvernement ouvert et responsable.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="liste_projet.php" class="btn btn-hero btn-primary-custom">
                            <i class="fas fa-search me-2"></i>Explorer les projets
                        </a>
                        <a href="#fonctionnalites" class="btn btn-hero btn-outline-custom">
                            <i class="fas fa-info-circle me-2"></i>En savoir plus
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="feature-icon mx-auto" style="width: 300px; height: 300px; background: rgba(255,255,255,0.1);">
                        <i class="fas fa-city" style="font-size: 8rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="fonctionnalites">
        <div class="container">
            <h2 class="section-title">Pourquoi utiliser SISAG ?</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="feature-title">Transparence Totale</h3>
                        <p class="feature-description">
                            Accédez à toutes les informations sur les projets gouvernementaux : 
                            budgets, délais, avancement et responsables.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Suivi en Temps Réel</h3>
                        <p class="feature-description">
                            Visualisez l'avancement des projets avec des indicateurs clairs 
                            et des mises à jour régulières.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="feature-title">Participation Citoyenne</h3>
                        <p class="feature-description">
                            Donnez votre avis sur les projets, posez des questions 
                            et contribuez à l'amélioration des services publics.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-6 mb-4">
                    <div class="stat-number" data-count="150">0</div>
                    <div class="stat-label">Projets Actifs</div>
                </div>
                <div class="col-lg-3 col-6 mb-4">
                    <div class="stat-number" data-count="45">0</div>
                    <div class="stat-label">Projets Terminés</div>
                </div>
                <div class="col-lg-3 col-6 mb-4">
                    <div class="stat-number" data-count="25">0</div>
                    <div class="stat-label">Communes Couvertes</div>
                </div>
                <div class="col-lg-3 col-6 mb-4">
                    <div class="stat-number" data-count="5000">0</div>
                    <div class="stat-label">Citoyens Actifs</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Preview -->
    <section class="projects-section" id="projets">
        <div class="container">
            <h2 class="section-title">Projets en Vedette</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <div class="project-content">
                            <span class="project-status status-en-cours">En Cours</span>
                            <h4>Construction Hôpital Central</h4>
                            <p class="text-muted">Nouvel hôpital moderne de 500 lits dans la commune de Gombe</p>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-primary" style="width: 65%">65%</div>
                            </div>
                            <small class="text-muted">Date fin: 15/12/2024</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <i class="fas fa-road"></i>
                        </div>
                        <div class="project-content">
                            <span class="project-status status-termine">Terminé</span>
                            <h4>Réhabilitation Boulevard</h4>
                            <p class="text-muted">Rénovation complète du boulevard Triomphal sur 5km</p>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width: 100%">100%</div>
                            </div>
                            <small class="text-muted">Terminé le: 20/10/2024</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="project-content">
                            <span class="project-status status-a-venir">À Venir</span>
                            <h4>Écoles Primaires</h4>
                            <p class="text-muted">Construction de 10 nouvelles écoles dans les quartiers défavorisés</p>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-warning" style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted">Début: 01/02/2024</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="liste_projet.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>Voir tous les projets
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Prêt à découvrir les projets ?</h2>
            <p class="fs-5 mb-4 opacity-90">
                Rejoignez des milliers de citoyens qui suivent déjà le développement de Kinshasa
            </p>
            <a href="liste_projet.php" class="btn btn-light btn-lg">
                <i class="fas fa-rocket me-2"></i>Commencer maintenant
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">SISAG</div>
                    <p class="text-muted">
                        Plateforme citoyenne de suivi des projets gouvernementaux de Kinshasa. 
                        Transparence, participation et redevabilité.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <div class="footer-links">
                        <h5>Navigation</h5>
                        <a href="#accueil">Accueil</a>
                        <a href="#projets">Projets</a>
                        <a href="#fonctionnalites">Fonctionnalités</a>
                        <a href="liste_projet.php">Tous les projets</a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <div class="footer-links">
                        <h5>Ressources</h5>
                        <a href="#">À propos</a>
                        <a href="#">Contact</a>
                        <a href="#">FAQ</a>
                        <a href="#">Mentions légales</a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="footer-links">
                        <h5>Contact</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Kinshasa, République Démocratique du Congo
                        </p>
                        <p class="text-muted mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +243 81 234 5678
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-envelope me-2"></i>
                            contact@sisag.cd
                        </p>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2024 SISAG - Système d'Information et de Suivi des Actions Gouvernementales. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animation des statistiques
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.getAttribute('data-count'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(current);
                }, 16);
            });
        }
        
        // Animation au scroll
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
        
        function handleScroll() {
            const statsSection = document.querySelector('.stats-section');
            if (isElementInViewport(statsSection)) {
                animateStats();
                window.removeEventListener('scroll', handleScroll);
            }
        }
        
        // Smooth scroll pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Initialisation
        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Vérifier au chargement
    </script>
</body>
</html>