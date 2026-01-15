<?php
// user/services.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$services = $conn->query("SELECT * FROM services WHERE status='active' ORDER BY name ASC");

// Fetch total active discount percentage
$disc_res = $conn->query("SELECT SUM(discount_percent) as total_percent FROM notices");
$disc_row = $disc_res->fetch_assoc();
$total_discount_percent = floatval($disc_row['total_percent']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Services - Smart Residence</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <script>
        window.currentUserId = '<?php echo $_SESSION['user_id'] ?? "default"; ?>';
    </script>
    <script src="../assets/js/theme-head.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="../assets/css/logout_animation.css">

    <style>
        /* Shared Dashboard Layout */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            padding: 30px 20px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            background-color: var(--bg-body);
        }

        .nav-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding: 0 10px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            border-radius: 16px;
            margin-bottom: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: var(--bg-hover);
            color: var(--primary);
        }

        .sidebar-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .sidebar-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        /* Service Cards */
        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            height: 100%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-custom:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: var(--bg-hover);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .card-custom:hover .icon-box {
            background: var(--primary);
            color: white;
            transform: scale(1.1) rotate(-5deg);
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .btn-premium {
            background: var(--primary);
            border: none;
            color: white;
            padding: 14px 24px;
            border-radius: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-premium:hover {
            background: var(--primary-hover);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
            color: white;
        }

        h4,
        h5,
        h6 {
            color: var(--text-main);
        }

        .text-dark {
            color: var(--text-main) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php $active_page = 'services.php';
        include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Search & Header -->
            <div class="row align-items-center mb-5">
                <div class="col-lg-6">
                    <h4 class="fw-800 mb-1">Premium Services</h4>
                    <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">On-demand
                        assistance for your home</p>
                </div>
                <div class="col-lg-6 mt-3 mt-lg-0">
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchInput"
                            class="form-control form-control-lg border-0 shadow-sm ps-5 rounded-pill"
                            style="background: var(--bg-card); color: var(--text-main);"
                            placeholder="Find a service (e.g. AC Repair, Cleaning)...">
                    </div>
                </div>
            </div>

            <div class="row g-4" id="serviceGrid">
                <?php while ($row = $services->fetch_assoc()): ?>
                    <div class="col-xl-4 col-md-6 service-item">
                        <div class="card-custom">
                            <div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <?php
                                    // Include Icon Helper securely
                                    $icon_helper_path = __DIR__ . '/../utils/icons.php';
                                    if (file_exists($icon_helper_path)) {
                                        require_once $icon_helper_path;
                                        $icon = IconHelper::getIcon($row['name']);
                                    } else {
                                        $icon = 'fa-concierge-bell';
                                    }
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <?php if ($row['price'] > 500): ?>
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-700">Premium</span>
                                <?php endif; ?>
                            </div>

                            <h4 class="fw-800 mb-2 service-name"><?php echo $row['name']; ?></h4>
                            <p class="text-muted small fw-600 mb-4"><?php echo $row['description']; ?></p>
                        </div>
                        <div>
                            <?php
                            $price = $row['price'];
                            $savings = 0;
                            $original_price = $price;
                            $has_discount = false;

                            // Use the pre-fetched total_discount_percent
                            if (isset($total_discount_percent) && $total_discount_percent > 0 && $total_discount_percent < 100) {
                                $factor = 1 - ($total_discount_percent / 100);
                                $original_price = $price / $factor;
                                $savings = $original_price - $price;
                                $has_discount = true;
                            }
                            ?>

                            <div class="d-flex align-items-end gap-2 mb-4">
                                <h3 class="fw-900 text-primary mb-0">₹<?php echo number_format($price); ?></h3>
                                <?php if ($has_discount): ?>
                                    <small
                                        class="text-muted text-decoration-line-through fw-600 mb-1">₹<?php echo number_format($original_price); ?></small>
                                <?php endif; ?>
                            </div>

                            <?php if ($has_discount && $savings > 0): ?>
                                <div
                                    class="small fw-700 text-success mb-3 p-2 bg-success bg-opacity-10 rounded-3 d-inline-block">
                                    <i class="fas fa-arrow-down me-1"></i> Save ₹<?php echo number_format($savings); ?>
                                </div>
                            <?php endif; ?>

                            <a href="request_service.php?id=<?php echo $row['id']; ?>"
                                class="btn btn-premium d-flex align-items-center justify-content-center gap-2">
                                Book Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
    </div>

    <div id="noResults" class="text-center py-5 d-none">
        <i class="fas fa-search fa-3x text-muted mb-3 opacity-25"></i>
        <h5 class="fw-bold text-muted">No services found</h5>
        <p class="text-muted small">Try searching for something else like 'cleaning' or 'repair'</p>
    </div>
    </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Real-time Search Filter
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const cards = document.querySelectorAll('.service-item');
            let hasVisible = false;

            cards.forEach(card => {
                const title = card.querySelector('.service-name').textContent.toLowerCase();
                const desc = card.querySelector('p').textContent.toLowerCase();

                if (title.includes(filter) || desc.includes(filter)) {
                    card.classList.remove('d-none');
                    hasVisible = true;
                } else {
                    card.classList.add('d-none');
                }
            });

            // Show/Hide "No Results" message
            const noResults = document.getElementById('noResults');
            if (!hasVisible) noResults.classList.remove('d-none');
            else noResults.classList.add('d-none');
        });
    </script>
</body>

</html>