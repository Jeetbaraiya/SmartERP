<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Residence - Premium ERP Management</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">

    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #00d2ff;
            --glass: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.25);
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            padding: 20px 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        .navbar.scrolled {
            padding: 12px 0;
            background: var(--bg-card) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--border-color);
        }

        .navbar.scrolled .navbar-brand,
        .navbar.scrolled .nav-link {
            color: var(--primary) !important;
        }

        .navbar.scrolled .btn-outline-light {
            color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        .navbar.scrolled .btn-outline-light:hover {
            background: var(--primary) !important;
            color: white !important;
        }

        .nav-link {
            font-weight: 700;
            padding: 8px 16px !important;
            border-radius: 100px;
            transition: all 0.3s ease;
        }

        .navbar-dark .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent) !important;
        }

        .navbar-light .nav-link:hover {
            background: var(--primary);
            color: white !important;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Hero Section */
        .hero {
            position: relative;
            min-height: 100vh;
            background: url('assets/img/hero.png') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            padding: 120px 0 80px;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 60, 114, 0.65) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            color: white;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 8vw, 4.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -2px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            color: #ffffff !important;
            /* Always white on dark hero image */
        }

        .hero p {
            font-size: 1.25rem;
            font-weight: 500;
            opacity: 1;
            max-width: 600px;
            margin-bottom: 40px;
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
            color: rgba(255, 255, 255, 0.9) !important;
            /* Always light */
        }

        /* Hero Glass Card */
        .hero-glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 40px 80px -12px rgba(0, 0, 0, 0.6);
            transition: all 0.3s ease;
        }

        .hero-glass-card h2,
        .hero-glass-card h5 {
            font-weight: 800;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            color: white !important;
        }

        .hero-glass-card small,
        .hero-glass-card p,
        .hero-glass-card i {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        /* Feature Cards */
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 40px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: var(--accent);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: rgba(30, 60, 114, 0.05);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 28px;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            background: var(--primary);
            color: white;
            transform: scale(1.1) rotate(-5deg);
        }

        .learn-more {
            margin-top: 20px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: gap 0.3s ease;
        }

        .feature-card:hover .learn-more {
            gap: 12px;
            color: var(--accent);
        }

        /* Stats Section */
        .stats-bar {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            margin-top: -60px;
            position: relative;
            z-index: 10;
        }

        /* Responsive Buttons */
        .btn-premium {
            padding: 14px 32px;
            border-radius: 100px;
            font-weight: 800;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-width: 2px !important;
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
            border: none;
            color: white;
            box-shadow: 0 10px 20px rgba(0, 210, 255, 0.3);
        }

        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 210, 255, 0.4);
            color: white;
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 100px 0 40px;
        }

        footer h5 {
            color: white !important;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .footer-link {
            transition: all 0.3s ease;
            color: #94a3b8;
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
        }

        .footer-link:hover {
            color: white;
            transform: translateX(5px);
        }

        .contact-pill {
            background: rgba(255, 255, 255, 0.05);
            padding: 12px 20px;
            border-radius: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive Grid fixes */
        .footer-link {
            transition: all 0.3s ease;
            color: #94a3b8 !important;
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
        }

        .contact-pill small {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        .hero {
            text-align: center;
        }

        .hero p {
            margin-left: auto;
            margin-right: auto;
        }

        .hero-glass-card {
            margin-top: 40px;
        }
        }
    </style>
</head>

<body>

    <!-- Transparent Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#">
                <div class="bg-primary p-2 rounded-3 d-inline-flex">
                    <i class="fas fa-building text-white"></i>
                </div>
                <span>SMART <span class="text-accent">RESIDENCE</span></span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-center gap-lg-3">
                    <li class="nav-item"><a class="nav-link fw-600" href="#features">Services</a></li>
                    <li class="nav-item"><a class="nav-link fw-600" href="#benefits">Benefits</a></li>
                    <li class="nav-item mt-3 mt-lg-0">
                        <a href="auth/login.php" class="btn btn-outline-light btn-premium px-4 border-2">Login</a>
                    </li>
                    <li class="nav-item mt-2 mt-lg-0">
                        <a href="auth/register.php" class="btn btn-primary-gradient btn-premium px-4">Register Now</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Interactive Hero -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 hero-content" data-aos="fade-right" data-aos-duration="1200">
                    <div class="hero-badge">
                        <span class="text-accent fs-5">●</span> Trusted by 5000+ Residencies
                    </div>
                    <h1 style="color: #ffffff !important;">Simplify Your <br>Living <span
                            class="text-accent">Experience</span></h1>
                    <p style="color: rgba(255, 255, 255, 0.9) !important; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">The
                        all-in-one ERP solution for modern residence management. Book services, track requests, and
                        manage your lifestyle with precision.</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                        <a href="auth/register.php" class="btn btn-primary-gradient btn-premium py-3 px-5">Get Started
                            Today</a>
                        <a href="#features" class="btn btn-outline-light btn-premium py-3 px-5 border-2">Explore
                            Services</a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1200">
                    <div class="hero-glass-card text-center">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="p-3">
                                    <h2 class="fw-800 mb-0">12k+</h2>
                                    <small class="opacity-75">Service Requests</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3">
                                    <h2 class="fw-800 mb-0">99%</h2>
                                    <small class="opacity-75">Satisfaction Rate</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="glass-inner">
                                    <i class="fas fa-shield-alt fa-3x mb-3"></i>
                                    <h5 class="fw-bold">Secured Management</h5>
                                    <p class="small mb-0 opacity-75">End-to-end encryption for all your data and
                                        transactions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <div class="container">
        <div class="stats-bar d-none d-lg-block">
            <div class="row text-center align-items-center">
                <div class="col-md-3 border-end">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-broom me-2"></i> 24/7 Cleaning</h5>
                </div>
                <div class="col-md-3 border-end">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-hammer me-2"></i> Fast Repairs</h5>
                </div>
                <div class="col-md-3 border-end">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-shield-virus me-2"></i> Sanitization</h5>
                </div>
                <div class="col-md-3">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-car me-2"></i> Auto Care</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Grid -->
    <section id="features" class="py-120" style="padding: 120px 0;">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-7" data-aos="fade-up">
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 rounded-pill fw-bold">OUR
                        ECOSYSTEM</span>
                    <h2 class="display-5 fw-bold mb-3">Premium Services at Your Fingertips</h2>
                    <p class="text-muted">Explore a wide range of residential services designed for your comfort and
                        convenience.</p>
                </div>
            </div>
            <div class="row g-4 pt-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <a href="auth/register.php" class="text-decoration-none h-100 d-block">
                        <div class="feature-card">
                            <div>
                                <div class="feature-icon"><i class="fas fa-snowflake"></i></div>
                                <h4 class="fw-bold text-main">Climate Control</h4>
                                <p class="text-muted mb-0">Professional AC servicing and repair to keep your home
                                    comfortable in every season.</p>
                            </div>
                            <span class="learn-more">Get Started <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <a href="auth/register.php" class="text-decoration-none h-100 d-block">
                        <div class="feature-card">
                            <div>
                                <div class="feature-icon"><i class="fas fa-bug"></i></div>
                                <h4 class="fw-bold text-main">Pest Control</h4>
                                <p class="text-muted mb-0">Advanced sanitation and pest management for a healthy living
                                    environment.</p>
                            </div>
                            <span class="learn-more">Get Started <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <a href="auth/register.php" class="text-decoration-none h-100 d-block">
                        <div class="feature-card">
                            <div>
                                <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                                <h4 class="fw-bold text-main">Smart Power</h4>
                                <p class="text-muted mb-0">Certified electricians for everything from minor repairs to
                                    smart home setups.</p>
                            </div>
                            <span class="learn-more">Get Started <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section id="benefits" class="py-5 bg-body overflow-hidden">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="https://images.unsplash.com/photo-1558002038-1055907df827?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80"
                        class="img-fluid rounded-5 shadow-2xl" alt="Smart Home Control">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="display-5 fw-bold mb-4">The Smart Choice <br>for Smart People</h2>
                    <div class="d-flex gap-4 mb-4">
                        <div class="bg-card p-3 rounded-4 shadow-sm h-100">
                            <i class="fas fa-check-circle text-success fa-lg mb-3"></i>
                            <h6 class="fw-bold">Verified Staff</h6>
                            <p class="small text-muted mb-0">Every service provider is background-checked and rated.</p>
                        </div>
                        <div class="bg-card p-3 rounded-4 shadow-sm h-100">
                            <i class="fas fa-clock text-primary fa-lg mb-3"></i>
                            <h6 class="fw-bold">Instant Booking</h6>
                            <p class="small text-muted mb-0">Schedule services in 30 seconds via your dashboard.</p>
                        </div>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-center gap-2"><i class="fas fa-check-circle text-accent"></i>
                            Real-time tracking of service requests</li>
                        <li class="mb-3 d-flex align-items-center gap-2"><i class="fas fa-check-circle text-accent"></i>
                            Secure online payments and digital invoices</li>
                        <li class="mb-3 d-flex align-items-center gap-2"><i class="fas fa-check-circle text-accent"></i>
                            Transparent feedback and rating system</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h4 class="text-white fw-bold mb-4">Smart Residence.</h4>
                    <p class="mb-4">Revolutionizing residential management with a focus on trust, efficiency, and modern
                        technology.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white opacity-50 hover-opacity-100 fs-4 tran-03"><i
                                class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white opacity-50 hover-opacity-100 fs-4 tran-03"><i
                                class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white opacity-50 hover-opacity-100 fs-4 tran-03"><i
                                class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 ms-auto">
                    <h5 style="color: #ffffff !important;">Quick Support</h5>
                    <div class="contact-pill">
                        <i class="fas fa-phone-alt text-accent"></i>
                        <div>
                            <small class="d-block opacity-50" style="color: rgba(255, 255, 255, 0.6) !important;">Call
                                Support</small>
                            <span class="fw-bold text-white" style="color: #ffffff !important;">+91 98765 43210</span>
                        </div>
                    </div>
                    <div class="contact-pill">
                        <i class="fas fa-envelope text-accent"></i>
                        <div>
                            <small class="d-block opacity-50" style="color: rgba(255, 255, 255, 0.6) !important;">Email
                                Us</small>
                            <span class="fw-bold text-white"
                                style="color: #ffffff !important;">help@smartresidence.com</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5 style="color: #ffffff !important;">Links</h5>
                    <a href="user/dashboard.php" class="footer-link" style="color: #94a3b8 !important;">Dashboard</a>
                    <a href="#features" class="footer-link" style="color: #94a3b8 !important;">Services</a>
                    <a href="auth/login.php" class="footer-link" style="color: #94a3b8 !important;">Login</a>
                    <a href="auth/register.php" class="footer-link" style="color: #94a3b8 !important;">Register</a>
                </div>
            </div>
            <div class="text-center mt-5 pt-5 border-top border-secondary">
                <p class="small mb-0 opacity-50">&copy; 2026 Smart Residence ERP. All rights reserved. Crafted for
                    excellence.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        window.addEventListener('scroll', function () {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 100) {
                nav.classList.add('scrolled', 'navbar-light');
                nav.classList.remove('navbar-dark');
            } else {
                nav.classList.remove('scrolled', 'navbar-light');
                nav.classList.add('navbar-dark');
            }
        });

        // Close mobile menu on link click
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                const navContent = document.getElementById('navContent');
                const bsCollapse = bootstrap.Collapse.getInstance(navContent);
                if (bsCollapse) {
                    bsCollapse.hide();
                } else if (navContent.classList.contains('show')) {
                    new bootstrap.Collapse(navContent).hide();
                }
            });
        });
    </script>
</body>

</html>