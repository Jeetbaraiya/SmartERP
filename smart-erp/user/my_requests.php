<?php
// user/my_requests.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

include 'includes/auto_expire.php';

// Handle Rating Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    $request_id = intval($_POST['request_id']);
    $rating = intval($_POST['rating']);
    $review = $conn->real_escape_string($_POST['review']);

    $conn->query("UPDATE service_requests SET rating=$rating, review='$review' WHERE id=$request_id AND user_id={$_SESSION['user_id']}");
    header("Location: my_requests.php?msg=rated");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT r.*, s.name as service_name, s.price FROM service_requests r 
        JOIN services s ON r.service_id = s.id 
        WHERE user_id=$user_id 
        ORDER BY r.created_at DESC";
$requests = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity History - Smart Residence</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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
            transition: all 0.3s;
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

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .table {
            color: var(--text-main);
        }

        .table thead th {
            background: transparent;
            border-bottom: 2px solid var(--border-color);
            padding: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .table tbody td {
            padding: 25px 20px;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        h4,
        .text-dark {
            color: var(--text-main) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .btn-statement {
            background: var(--bg-hover);
            color: var(--primary);
            border: none;
        }

        .btn-statement:hover {
            background: var(--bg-input);
            transform: translateY(-2px);
            color: var(--primary-hover);
        }

        /* Star Rating */
        .star-rating {
            direction: rtl;
            display: inline-flex;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            color: var(--border-color);
            font-size: 1.5rem;
            transition: color 0.2s;
            padding: 0 2px;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffbc00;
        }

        .modal-content {
            background-color: var(--bg-card);
            color: var(--text-main);
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
        <?php $active_page = 'my_requests.php';
        include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="fw-800 mb-1">Activity History</h4>
                    <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">Track your home
                        management journey</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="download_history.php" target="_blank" class="btn btn-action btn-statement">
                        <i class="fas fa-file-invoice me-2"></i> Download Statement
                    </a>
                </div>
            </header>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'rated'): ?>
                <div class="alert alert-success border-0 rounded-4 mb-4 fw-700 shadow-sm p-3">
                    <i class="fas fa-star text-warning me-2"></i> Thank you for your feedback!
                </div>
            <?php endif; ?>

            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Service Category</th>
                                <th>Requested On</th>
                                <th class="text-center">Status</th>
                                <th>Grand Total</th>
                                <th class="text-center">Rating</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests->num_rows > 0): ?>
                                <?php while ($row = $requests->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-800 text-main"><?php echo $row['service_name']; ?></div>
                                            <div class="small text-muted fw-600">ID:
                                                #SR-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></div>
                                        </td>
                                        <td class="text-muted fw-700">
                                            <?php echo date('D, M d Y', strtotime($row['created_at'])); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $s = $row['status'];
                                            $cls_class = 'bg-secondary bg-opacity-10 text-secondary';
                                            $display_status = $s;

                                            if ($row['refund_status'] == 'refunded') {
                                                $s = 'refunded';
                                                $display_status = 'Refunded';
                                                $cls_class = 'bg-info bg-opacity-10 text-info-emphasis';
                                            } elseif ($s == 'pending') {
                                                $cls_class = 'bg-warning bg-opacity-10 text-warning-emphasis';
                                            } elseif ($s == 'approved') {
                                                $cls_class = 'bg-primary bg-opacity-10 text-primary';
                                            } elseif ($s == 'completed') {
                                                $cls_class = 'bg-success bg-opacity-10 text-success';
                                            } elseif ($s == 'rejected') {
                                                // High contrast for rejected
                                                $cls_class = 'bg-danger text-white';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $cls_class; ?>">
                                                <?php echo ucfirst($display_status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-900 text-main">₹<?php echo number_format($row['price']); ?></div>
                                            <div class="small text-muted fw-700">
                                                <?php echo strtoupper($row['payment_method'] ?? 'CASH'); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['rating']): ?>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= $row['rating'] ? '' : '-o'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <div class="small text-muted fw-600 mt-1"><?php echo $row['rating']; ?>/5</div>
                                            <?php elseif ($s == 'completed'): ?>
                                                <button class="btn btn-warning btn-action text-white"
                                                    onclick="openRatingModal(<?php echo $row['id']; ?>, '<?php echo $row['service_name']; ?>')">
                                                    <i class="fas fa-star me-1"></i> Review
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small fw-600">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                                <?php if ($s == 'completed' || $s == 'rejected' || $row['refund_status'] == 'refunded'): ?>
                                                    <a href="request_service.php?rebook_id=<?php echo $row['id']; ?>"
                                                        class="btn btn-primary-custom btn-action d-flex align-items-center">
                                                        <i class="fas fa-redo-alt me-2"></i> Book Again
                                                    </a>
                                                <?php elseif ($s == 'pending' || $s == 'approved'): ?>
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill fw-800 px-3"
                                                        onclick="openTrackingModal('<?php echo $s; ?>', '<?php echo date('M d, Y', strtotime($row['created_at'])); ?>', '<?php echo $row['service_name']; ?>', '<?php echo $row['id']; ?>')">
                                                        <i class="fas fa-route me-1"></i> Track
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small fw-600">-</span>
                                                <?php endif; ?>

                                                <?php
                                                // Show Invoice Logic:
                                                // 1. MUST NOT be Refunded
                                                // 2. AND (Status is Completed OR (Payment is Paid AND NOT Cash))
                                                $is_refunded = ($row['refund_status'] == 'refunded');
                                                $is_completed = ($s == 'completed');
                                                $is_online_paid = ($row['payment_status'] == 'paid' && strtolower($row['payment_method']) != 'cash');

                                                $show_invoice = !$is_refunded && ($is_completed || $is_online_paid);
                                                ?>

                                                <?php if ($show_invoice): ?>
                                                    <a href="invoice.php?id=<?php echo $row['id']; ?>" target="_blank"
                                                        class="btn btn-light btn-action border text-dark d-flex align-items-center"
                                                        style="background: var(--bg-hover); color: var(--text-main) !important; border-color: var(--border-color) !important;">
                                                        <i class="fas fa-file-invoice me-2"></i> Invoice
                                                    </a>
                                                <?php else: ?>
                                                    <!-- Invisible Placeholder to maintain layout alignment -->
                                                    <a href="#"
                                                        class="btn btn-light btn-action border invisible d-flex align-items-center"
                                                        aria-hidden="true" tabindex="-1"
                                                        style="background: var(--bg-hover); border-color: var(--border-color) !important;">
                                                        <i class="fas fa-file-invoice me-2"></i> Invoice
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted fw-700">No activities found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-5 overflow-hidden">
                <div class="modal-header border-0 bg-warning text-white p-4">
                    <h5 class="modal-title fw-800">Share Your Feedback</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4 text-center">
                        <input type="hidden" name="submit_rating" value="1">
                        <input type="hidden" name="request_id" id="ratingRequestId">
                        <h4 id="ratingServiceName" class="fw-800 text-main mb-4">Service Name</h4>
                        <div class="mb-4">
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" required /><label for="star5"><i
                                        class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" /><label for="star4"><i
                                        class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" /><label for="star3"><i
                                        class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" /><label for="star2"><i
                                        class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" /><label for="star1"><i
                                        class="fas fa-star"></i></label>
                            </div>
                        </div>
                        <textarea class="form-control rounded-4 py-3 fw-600" name="review" rows="3"
                            placeholder="Tell us about the service quality..."></textarea>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-800">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tracking Modal -->
    <div class="modal fade" id="trackingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-5">
                <div class="modal-header border-0 p-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-800 text-main mb-1">Request Timeline</h5>
                        <p class="text-muted small fw-600 mb-0">Tracking ID: #SR-<span id="trackId"></span></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-5">
                    <!-- Timeline Component -->
                    <div class="timeline-steps aos-animate">
                        <div class="timeline-step" id="step-requested">
                            <div class="timeline-content">
                                <div class="inner-circle"></div>
                                <p class="h6 mt-3 mb-1 fw-800 text-main">Requested</p>
                                <p class="h6 text-muted mb-0 mb-lg-0 small" id="trackDate">Date</p>
                            </div>
                        </div>
                        <div class="timeline-step" id="step-approved">
                            <div class="timeline-content">
                                <div class="inner-circle"></div>
                                <p class="h6 mt-3 mb-1 fw-800 text-main">Approved</p>
                                <p class="h6 text-muted mb-0 mb-lg-0 small">Admin Verified</p>
                            </div>
                        </div>
                        <div class="timeline-step" id="step-inprogress">
                            <div class="timeline-content">
                                <div class="inner-circle"></div>
                                <p class="h6 mt-3 mb-1 fw-800 text-main">In Progress</p>
                                <p class="h6 text-muted mb-0 mb-lg-0 small">Work Started</p>
                            </div>
                        </div>
                        <div class="timeline-step" id="step-completed">
                            <div class="timeline-content">
                                <div class="inner-circle"></div>
                                <p class="h6 mt-3 mb-1 fw-800 text-main">Completed</p>
                                <p class="h6 text-muted mb-0 mb-lg-0 small">Service Done</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Timeline CSS */
        .timeline-steps {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
        }

        .timeline-steps .timeline-step {
            align-items: center;
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0 1rem;
            flex: 1;
            opacity: 0.5;
            /* Default inactive opacity */
            transition: all 0.3s ease;
        }

        .timeline-steps .timeline-step.active {
            opacity: 1;
        }

        .timeline-steps .timeline-step.completed .inner-circle {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .timeline-steps .timeline-step.completed .inner-circle:before {
            content: '\f00c';
            /* FontAwesome Check */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: white;
            font-size: 10px;
        }

        .timeline-steps .timeline-step.active-current .inner-circle {
            background-color: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        .timeline-steps .timeline-step.active-current .inner-circle:before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
        }

        .timeline-steps .timeline-step:not(:last-child):after {
            content: "";
            position: absolute;
            top: 20px;
            /* Center with circle */
            left: 50%;
            width: 100%;
            height: 4px;
            background-color: var(--border-color);
            z-index: -1;
            transform: translateY(-50%);
        }

        .timeline-steps .timeline-step.completed:not(:last-child):after {
            background-color: var(--primary);
        }

        .timeline-steps .timeline-step .inner-circle {
            border-radius: 50%;
            height: 40px;
            width: 40px;
            border: 3px solid var(--border-color);
            background-color: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: all 0.3s;
        }

        .timeline-steps .timeline-content {
            width: 100%;
            text-align: center;
        }

        @media (max-width: 768px) {
            .timeline-steps {
                flex-direction: column;
                align-items: flex-start;
                padding-left: 20px;
            }

            .timeline-steps .timeline-step {
                flex-direction: row;
                margin: 0 0 30px 0;
                width: 100%;
                text-align: left;
                align-items: center;
                gap: 20px;
            }

            .timeline-steps .timeline-step:not(:last-child):after {
                width: 4px;
                height: 100%;
                top: 50%;
                left: 20px;
                /* Align with circle center */
                transform: translateX(-50%);
            }

            .timeline-steps .timeline-content {
                text-align: left;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        function openRatingModal(id, name) {
            document.getElementById('ratingRequestId').value = id;
            document.getElementById('ratingServiceName').textContent = name;
            new bootstrap.Modal(document.getElementById('ratingModal')).show();
        }

        function openTrackingModal(status, dateStr, serviceName, id) {
            document.getElementById('trackId').innerText = id;
            document.getElementById('trackDate').innerText = dateStr;

            // Reset classes
            const steps = ['requested', 'approved', 'inprogress', 'completed'];
            steps.forEach(step => {
                const el = document.getElementById('step-' + step);
                el.classList.remove('completed', 'active', 'active-current');
            });

            // Logic
            // Step 1: Requested - Always completed if it exists
            document.getElementById('step-requested').classList.add('completed', 'active');

            const approved = document.getElementById('step-approved');
            const inprogress = document.getElementById('step-inprogress');
            const completed = document.getElementById('step-completed');

            if (status === 'pending') {
                document.getElementById('step-requested').classList.remove('completed');
                document.getElementById('step-requested').classList.add('active-current', 'active');
            } else if (status === 'approved') {
                approved.classList.add('completed', 'active');
                // Assume "In Progress" is current if approved
                inprogress.classList.add('active-current', 'active');
            } else if (status === 'completed') {
                approved.classList.add('completed', 'active');
                inprogress.classList.add('completed', 'active');
                completed.classList.add('completed', 'active'); // Completed is final state
            } else if (status === 'rejected') {
                // Handle rejected state if needed, maybe just show requested and a rejected badge
            }

            // Show Modal
            new bootstrap.Modal(document.getElementById('trackingModal')).show(); }
    </script>
</body>

</html>