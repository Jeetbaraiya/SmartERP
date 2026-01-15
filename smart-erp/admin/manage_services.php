<?php
// admin/manage_services.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Level 4 (Service Manager) or Level 1 (Super Admin) only
$level = $_SESSION['level'] ?? 5;
if ($level != 4 && $level != 1) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

$message = '';

// Handle Add Service
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $_POST['price'];

    $sql = "INSERT INTO services (name, description, price) VALUES ('$name', '$description', '$price')";
    if ($conn->query($sql) === TRUE) {
        $message = '<div class="alert alert-success border-0 rounded-4 mb-4 fw-700 shadow-sm"><i class="fas fa-check-circle me-2"></i> Service added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger border-0 rounded-4 mb-4 fw-700 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> Error: ' . $conn->error . '</div>';
    }
}

// Handle Delete (Get Request)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM services WHERE id=$id");
    header("Location: manage_services.php");
    exit();
}

// Handle Status Toggle
if (isset($_GET['toggle']) && isset($_GET['status'])) {
    $id = $_GET['toggle'];
    $new_status = $_GET['status'] == 'active' ? 'inactive' : 'active';
    $conn->query("UPDATE services SET status='$new_status' WHERE id=$id");
    header("Location: manage_services.php");
    exit();
}

$services = $conn->query("SELECT * FROM services ORDER BY created_at DESC");

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
    <title>Manage Services - Smart Residence ERP</title>
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
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            background-color: var(--bg-body);
            min-height: 100vh;
        }

        .service-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }

        .icon-box {
            width: 55px;
            height: 55px;
            background: var(--bg-hover);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .price-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary);
        }

        .btn-plus {
            background: var(--primary);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 14px;
            font-weight: 800;
        }

        h2,
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

        .form-control {
            background-color: var(--bg-input);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .form-control:focus {
            background-color: var(--bg-input);
            color: var(--text-main);
            border-color: var(--primary);
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <?php
    $active_page = 'manage_services.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Services Catalog</h2>
                <p class="text-muted fw-600 mb-0">Manage on-demand residential services.</p>
            </div>
            <button class="btn btn-plus shadow-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus me-2"></i> Add New
            </button>
        </div>

        <?php echo $message; ?>

        <div class="row g-4">
            <?php while ($row = $services->fetch_assoc()): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="service-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="icon-box">
                                    <?php
                                    require_once '../utils/icons.php';
                                    $icon = IconHelper::getIcon($row['name']);
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <span
                                    class="badge rounded-pill <?php echo $row['status'] == 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'; ?> fw-800 px-3 py-2"
                                    style="font-size: 0.65rem;">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </div>
                            <h5 class="fw-800 text-dark mb-2"><?php echo $row['name']; ?></h5>
                            <p class="text-muted small fw-600 mb-4"><?php echo substr($row['description'], 0, 80); ?>...</p>
                        </div>
                        <div>
                            <?php
                            $price = $row['price'];
                            $savings = 0;
                            $original_price = $price;
                            $has_discount = false;

                            // Use pre-fetched total_discount_percent
                            if (isset($total_discount_percent) && $total_discount_percent > 0 && $total_discount_percent < 100) {
                                $factor = 1 - ($total_discount_percent / 100);
                                $original_price = $price / $factor;
                                $savings = $original_price - $price;
                                $has_discount = true;
                            }
                            ?>
                            <div class="price-text mb-1 d-flex align-items-center flex-wrap gap-2">
                                <?php if ($has_discount): ?>
                                    <small
                                        class="text-muted text-decoration-line-through fw-600 fs-6">₹<?php echo number_format($original_price); ?></small>
                                <?php endif; ?>
                                <span>₹<?php echo number_format($price); ?></span>
                            </div>
                            <?php if ($has_discount && $savings > 0): ?>
                                <div class="small fw-700 text-success mb-3">
                                    <i class="fas fa-arrow-down"></i> Save ₹<?php echo number_format($savings); ?>
                                </div>
                            <?php else: ?>
                                <div class="mb-4"></div>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <a href="?toggle=<?php echo $row['id']; ?>&status=<?php echo $row['status']; ?>"
                                    class="btn <?php echo $row['status'] == 'active' ? 'btn-gradient-secondary' : 'btn-gradient-primary'; ?> fw-800 flex-grow-1 rounded-3 small">
                                    <?php echo $row['status'] == 'active' ? 'Disable' : 'Enable'; ?>
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-outline-danger rounded-3 px-3"
                                    onclick="return confirm('Delete service?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-custom border-0 p-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <h5 class="modal-title fw-800">Add New Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="icon-box mx-auto mb-2" id="iconPreview" style="background: var(--bg-hover);">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <small class="text-muted fw-700">Icon Preview</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted text-uppercase">Service Name</label>
                            <input type="text" name="name" id="serviceNameInput"
                                class="form-control rounded-3 py-2 fw-600" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted text-uppercase">Description</label>
                            <textarea name="description" class="form-control rounded-3 py-2 fw-600" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted text-uppercase">Standard Price
                                (Rs.)</label>
                            <input type="number" step="0.01" name="price" class="form-control rounded-3 py-2 fw-600"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary fw-800"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_service" class="btn btn-primary fw-800 px-4">Create
                            Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Live Icon Preview Logic
        document.getElementById('serviceNameInput').addEventListener('input', function () {
            const name = this.value.toLowerCase();
            let icon = 'fa-concierge-bell';

            const icons = {
                'laundry': 'fa-tshirt', 'wash': 'fa-tshirt', 'iron': 'fa-tshirt',
                'pest': 'fa-bug', 'insect': 'fa-bug',
                'carpenter': 'fa-hammer', 'wood': 'fa-hammer',
                'garden': 'fa-leaf', 'plant': 'fa-seedling',
                'paint': 'fa-paint-roller', 'wall': 'fa-palette',
                'sofa': 'fa-couch',
                'ro ': 'fa-bottle-water', 'water': 'fa-tint',
                'cctv': 'fa-video', 'camera': 'fa-video',
                'ac ': 'fa-snowflake', 'cooling': 'fa-snowflake',
                'car': 'fa-car', 'vehicle': 'fa-car',
                'electric': 'fa-bolt', 'power': 'fa-plug',
                'plumb': 'fa-wrench', 'pipe': 'fa-faucet',
                'clean': 'fa-broom', 'maid': 'fa-broom',
                'gym': 'fa-dumbbell', 'fitness': 'fa-dumbbell'
            };

            for (const [key, val] of Object.entries(icons)) {
                if (name.includes(key)) {
                    icon = val;
                    break;
                }
            }

            const preview = document.getElementById('iconPreview').querySelector('i');
            preview.className = 'fas ' + icon;
        });
    </script>
</body>

</html>