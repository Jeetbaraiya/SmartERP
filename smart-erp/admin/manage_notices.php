<?php
// admin/manage_notices.php
session_start();
require_once '../config/db.php';

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Level 3 (Branch Admin) or Level 1 (Super Admin) only
$level = $_SESSION['level'] ?? 5;
if ($level != 3 && $level != 1) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Handle Add Notice
// Handle Add Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['message']);
    $percent = floatval($_POST['percent']);

    // Ensure percent is valid (0 to 99)
    if ($percent < 0 || $percent >= 100)
        $percent = 0;

    $sql = "INSERT INTO notices (title, message, discount_percent) VALUES ('$title', '$content', '$percent')";
    if ($conn->query($sql)) {
        // Apply discount percentage to all services
        if ($percent > 0) {
            $factor = 1 - ($percent / 100);
            $conn->query("UPDATE services SET price = price * $factor");
        }
        header("Location: manage_notices.php?msg=added");
    } else {
        header("Location: manage_notices.php?error=" . $conn->error);
    }
    exit();
}

// Handle Delete Notice
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check for discount to revert logic
    $res = $conn->query("SELECT discount_percent FROM notices WHERE id=$id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $percent = floatval($row['discount_percent']);

        // Revert price changes: Original = Current / (1 - percent/100)
        if ($percent > 0 && $percent < 100) {
            $factor = 1 - ($percent / 100);
            $conn->query("UPDATE services SET price = price / $factor");
        }
    }

    $conn->query("DELETE FROM notices WHERE id=$id");
    header("Location: manage_notices.php?msg=deleted");
    exit();
}

// Fetch Notices
$result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Notices - Smart Residence ERP</title>
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

        .notice-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border-left: 6px solid var(--accent);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }

        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border-color: var(--primary);
        }

        .btn-post {
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
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

        .discount-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
    $active_page = 'manage_notices.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Notice Board</h2>
                <p class="text-muted fw-600 mb-0">Broadcast important updates to all residents.</p>
            </div>
            <button class="btn btn-post shadow-sm" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
                <i class="fas fa-plus me-2"></i> Post New
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
            <div class="alert alert-success border-0 rounded-4 mb-4 fw-700 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> Announcement posted successfully!
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-6">
                        <div class="notice-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-800 mb-1"><?php echo $row['title']; ?></h5>
                                    <?php if ($row['discount_percent'] > 0): ?>
                                        <div class="discount-badge mt-1">
                                            <i class="fas fa-tag"></i> <?php echo number_format($row['discount_percent'], 0); ?>%
                                            Discount Applied
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <a href="?delete=<?php echo $row['id']; ?>"
                                    class="btn btn-outline-danger border-0 rounded-circle"
                                    onclick="return confirm('Delete notice? This will revert any price changes.');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                            <div class="small fw-700 text-primary mb-3">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?php echo date('D, M d Y | h:i A', strtotime($row['created_at'])); ?>
                            </div>
                            <div class="text-muted fw-600 bg-light bg-opacity-10 p-3 rounded-4" style="line-height: 1.6;">
                                <?php echo nl2br($row['message']); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="bg-card p-5 rounded-5 shadow-sm d-inline-block">
                        <i class="fas fa-bullhorn fa-4x text-muted mb-4" style="opacity: 0.3"></i>
                        <h4 class="fw-800">No Active Notices</h4>
                        <p class="text-muted fw-600">Your announcements will appear here.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Notice Modal -->
    <div class="modal fade" id="addNoticeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-custom border-0 p-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <h5 class="modal-title fw-800">New Announcement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_notice" value="1">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted text-uppercase">Announcement Title</label>
                            <input type="text" name="title" class="form-control rounded-3 py-2 fw-600" required
                                placeholder="e.g. Festival Offer">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted text-uppercase">Discount Percentage
                                (%)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 fw-bold">%</span>
                                <input type="number" name="percent" class="form-control rounded-end-3 py-2 fw-600"
                                    min="0" max="99" step="1" placeholder="0">
                            </div>
                            <small class="text-muted fst-italic">This percentage will be applied to ALL service
                                prices.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted text-uppercase">Detailed Message</label>
                            <textarea name="message" class="form-control rounded-3 py-2 fw-600" rows="5" required
                                placeholder="Provide details about the offer..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-gradient-secondary fw-800"
                            data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-post px-4">Post & Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>

</html>