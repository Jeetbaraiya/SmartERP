<?php
// user/request_service.php
session_start();
require_once '../config/db.php';
require_once '../config/razorpay_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Pre-fill / Re-book Logic
$prefill_comments = "";
$rebook_service_id = 0;

if (isset($_GET['rebook_id'])) {
    $rebook_id = intval($_GET['rebook_id']);
    // Fetch previous request details owned by this user
    $prev_req = $conn->query("SELECT service_id, comments FROM service_requests WHERE id=$rebook_id AND user_id=$user_id")->fetch_assoc();
    if ($prev_req) {
        $rebook_service_id = $prev_req['service_id'];
        $prefill_comments = $prev_req['comments'];
    }
}

// Determine Service ID (Re-book ID takes priority, then GET ID)
$id = $rebook_service_id > 0 ? $rebook_service_id : ($_GET['id'] ?? 0);

$service = $conn->query("SELECT * FROM services WHERE id=$id AND status='active'")->fetch_assoc();

if (!$service) {
    header("Location: services.php");
    exit();
}
$upi_raw = "upi://pay?pa=admin@smart-residence&pn=SmartResidence&am={$price_formatted}&cu=INR";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($upi_raw);

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = intval($_POST['service_id']);
    $comments = $conn->real_escape_string($_POST['comments']);
    $payment_method = $_POST['payment_method'];
    $booking_date = $_POST['booking_date'];
    $booking_slot = $_POST['booking_slot'];
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id']);
    $payment_id = isset($_POST['razorpay_payment_id']) ? $conn->real_escape_string($_POST['razorpay_payment_id']) : NULL;
    $payment_status = ($payment_method == 'razorpay' && !empty($payment_id)) ? 'paid' : 'pending';

    $sql = "INSERT INTO service_requests 
            (user_id, service_id, booking_date, booking_slot, comments, payment_method, payment_id, payment_status, request_date, approval_stage) 
            VALUES 
            ('$user_id', '$service_id', '$booking_date', '$booking_slot', '$comments', '$payment_method', '$payment_id', '$payment_status', CURDATE(), 4)";

    if ($conn->query($sql)) {
        $new_request_id = $conn->insert_id;
        $success = "Booking Confirmed! Your request #SR-" . str_pad($new_request_id, 3, '0', STR_PAD_LEFT) . " is now being processed.";

        if ($payment_id) {
            require_once '../utils/mailer.php';
            Mailer::sendReceipt($_SESSION['email'], $_SESSION['name'], $service['price'], $service['name'] . " Booking");
        }
    } else {
        $error = "Database Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Smart Residence</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <script>
        window.currentUserId = '<?php echo $_SESSION['user_id'] ?? "default"; ?>';
    </script>
    <script src="../assets/js/theme-head.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="../assets/css/logout_animation.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Fallback for variable letdown */
        [data-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card) !important;
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            padding: 30px 20px;
        }

        [data-theme="dark"] .sidebar {
            background: #1e293b !important;
            border-color: #334155;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
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
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.15);
        }

        .card-custom {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-color);
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        [data-theme="dark"] .card-custom {
            background: #1e293b !important;
            border-color: #334155;
        }

        .service-summary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 40px;
        }

        .payment-card {
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            background: var(--bg-card);
        }

        .payment-card:hover {
            border-color: var(--accent);
            background: var(--bg-hover);
        }

        .payment-card.active {
            border-color: var(--primary);
            background: var(--bg-hover);
        }

        .payment-card.active::after {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 15px;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .slot-btn {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
            background: var(--bg-card);
            color: var(--text-main);
            width: 100%;
        }

        .slot-btn:hover:not(:disabled) {
            border-color: var(--accent);
            color: var(--primary);
        }

        .slot-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .slot-btn:disabled {
            opacity: 0.5;
            background: var(--bg-hover);
            cursor: not-allowed;
            color: var(--text-muted);
        }

        .btn-confirm {
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
            border: none;
            color: white;
            padding: 18px;
            border-radius: 18px;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 10px 20px rgba(0, 210, 255, 0.2);
            transition: all 0.3s;
        }

        .btn-confirm:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 210, 255, 0.3);
            color: white;
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

        /* Headers and Text Adjustments */
        h4,
        h2,
        h3,
        h5,
        .text-dark {
            color: var(--text-main) !important;
        }

        .payment-card .small {
            color: var(--text-main) !important;
        }

        /* Fix for Service Summary Card (Always White Text on Blue Background) */
        .service-summary h1,
        .service-summary h2,
        .service-summary h3,
        .service-summary h4,
        .service-summary h5,
        .service-summary p,
        .service-summary .small,
        .service-summary .text-uppercase,
        .service-summary .fw-800,
        .service-summary .fw-900,
        .service-summary .fw-500 {
            color: white !important;
        }

        /* Ensure opacity classes work with white text */
        .service-summary .opacity-50 {
            opacity: 0.5;
        }

        .service-summary .opacity-75 {
            opacity: 0.75;
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper">
        <?php $active_page = 'services.php';
        include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="fw-800 mb-1 text-dark">Finalize Booking</h4>
                    <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">Secure your
                        professional service request</p>
                </div>
            </header>

            <div class="row justify-content-center">
                <div class="col-xl-11">
                    <div class="card-custom">
                        <div class="row g-0">
                            <div class="col-lg-5 service-summary d-flex flex-column justify-content-between">
                                <div>
                                    <h2 class="fw-800 mb-4">Review Booking</h2>
                                    <div class="mb-5">
                                        <div class="small fw-700 text-uppercase opacity-50 mb-2">Service Selected</div>
                                        <h3 class="fw-800 mb-0"><?php echo htmlspecialchars($service['name']); ?></h3>
                                        <p class="opacity-75 mt-2 fw-500">
                                            <?php echo htmlspecialchars($service['description']); ?>
                                        </p>
                                    </div>
                                    <div class="mb-4">
                                        <div class="small fw-700 text-uppercase opacity-50 mb-2">Total Amount</div>
                                        <h1 class="display-5 fw-900">Rs. <?php echo number_format($service['price']); ?>
                                        </h1>
                                        <div class="small fw-700 text-uppercase opacity-50 mt-1">Inclusive of all taxes
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white bg-opacity-10 p-3 rounded-4">
                                    <small class="d-flex align-items-center gap-2 fw-700">
                                        <i class="fas fa-shield-alt text-success"></i> Secure Professional Booking
                                    </small>
                                </div>
                            </div>

                            <div class="col-lg-7 p-4 p-md-5">
                                <?php if ($success): ?>
                                    <div class="text-center py-5">
                                        <div class="bg-success text-white mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle"
                                            style="width: 80px; height: 80px;">
                                            <i class="fas fa-check fa-2x"></i>
                                        </div>
                                        <h3 class="fw-800 text-dark mb-3">Order Confirmed!</h3>
                                        <p class="text-muted fw-600 mb-5"><?php echo $success; ?></p>
                                        <div class="d-grid gap-3">
                                            <a href="my_requests.php" class="btn btn-primary py-3 rounded-4 fw-800">Track My
                                                Request</a>
                                            <a href="dashboard.php" class="btn btn-light py-3 rounded-4 fw-800">Return to
                                                Dashboard</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div id="guidanceBox"
                                        class="alert bg-primary bg-opacity-10 text-primary border-0 rounded-4 mb-4 p-3 d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; flex-shrink: 0;">
                                            <i class="fas fa-info-circle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-800 small text-uppercase" style="letter-spacing: 0.5px;">Current
                                                Step</div>
                                            <div id="guidanceText" class="fw-700">Please select a preferred date and time
                                                slot.</div>
                                        </div>
                                    </div>

                                    <?php if ($error): ?>
                                        <div class="alert alert-danger border-0 rounded-4 mb-4 small fw-700">
                                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" id="bookingForm">
                                        <input type="hidden" name="service_id" value="<?php echo $id; ?>">

                                        <div class="mb-5">
                                            <h5 class="fw-800 text-dark mb-4">1. Schedule Appointment</h5>
                                            <label class="form-label fw-800 small text-muted text-uppercase ms-1">Preferred
                                                Date</label>
                                            <input type="date" name="booking_date" id="booking_date"
                                                class="form-control form-control-lg rounded-4 py-3 fw-700 border-2 mb-4"
                                                min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"
                                                required>

                                            <label
                                                class="form-label fw-800 small text-muted text-uppercase ms-1 mb-3">Available
                                                Time Slots</label>
                                            <div class="row g-2" id="slotContainer"></div>
                                            <input type="hidden" name="booking_slot" id="booking_slot" required>
                                            <div id="slotError" class="text-danger small fw-800 mt-2" style="display:none;">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Please pick a time slot.
                                            </div>
                                        </div>

                                        <div class="mb-5">
                                            <h5 class="fw-800 text-dark mb-4">2. Payment Method</h5>
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <div class="payment-card active" data-value="cash"
                                                        onclick="selectPayment(this)">
                                                        <i class="fas fa-money-bill-wave-alt text-success fa-2x mb-3"></i>
                                                        <div class="fw-800 text-dark small">Cash on Service</div>
                                                        <input type="radio" name="payment_method" value="cash"
                                                            class="d-none" checked>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="payment-card" data-value="razorpay"
                                                        onclick="selectPayment(this)">
                                                        <i class="fas fa-credit-card text-primary fa-2x mb-3"></i>
                                                        <div class="fw-800 text-dark small">Razorpay Online (UPI, Card)
                                                        </div>
                                                        <input type="radio" name="payment_method" value="razorpay"
                                                            class="d-none">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h5 class="fw-800 text-dark mb-3">3. Anything else?</h5>
                                            <select class="form-select bg-light" id="service_id" name="service_id" required
                                                style="pointer-events: none;">
                                                <option value="<?php echo $service['id']; ?>" selected>
                                                    <?php echo $service['name']; ?> -
                                                    ₹<?php echo number_format($service['price']); ?>
                                                </option>
                                            </select>
                                            <div class="form-text text-muted small"><i class="fas fa-lock me-1"></i> Service
                                                selection is locked for this booking.</div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="comments" class="form-label">Additional Notes</label>
                                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                                placeholder="Describe your issue or specific requirements..."><?php echo htmlspecialchars($prefill_comments); ?></textarea>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-confirm py-4">
                                                Confirm & Book Appointment <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const slotContainer = document.getElementById('slotContainer');
        const slotInput = document.getElementById('booking_slot');
        const dateInput = document.getElementById('booking_date');
        const timeSlots = ["09:00 - 10:00", "10:00 - 11:00", "11:00 - 12:00", "12:00 - 13:00", "13:00 - 14:00", "14:00 - 15:00", "15:00 - 16:00", "16:00 - 17:00", "17:00 - 18:00"];

        var razorpay_options = {
            "key": "<?php echo RAZORPAY_KEY_ID; ?>",
            "amount": "<?php echo $service['price'] * 100; ?>",
            "currency": "INR",
            "name": "Smart Residence ERP",
            "description": "Booking for <?php echo htmlspecialchars($service['name']); ?>",
            "handler": function (response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('bookingForm').submit();
            },
            "prefill": {
                "name": "<?php echo $_SESSION['name']; ?>",
                "email": "<?php echo $_SESSION['email']; ?>"
            },
            "config": {
                "display": {
                    "hide": [
                        { "method": "paylater" },
                        { "method": "wallet" },
                        { "method": "emi" }
                    ],
                    "preferences": {
                        "show_default_blocks": true
                    }
                }
            },
            "theme": { "color": "#1e3c72" }
        };

        var rzp1 = null;
        try { if (typeof Razorpay !== 'undefined') rzp1 = new Razorpay(razorpay_options); } catch (e) { }

        function fetchSlots() {
            const date = dateInput.value;
            if (!date) return;

            slotContainer.innerHTML = '<div class="col-12 py-3 text-center"><i class="fas fa-spinner fa-spin text-primary"></i> Checking Availability...</div>';

            const now = new Date();

            fetch(`get_booked_slots.php?service_id=<?php echo $id; ?>&date=${date}`)
                .then(r => r.json())
                .then(booked => {
                    slotContainer.innerHTML = ''; slotInput.value = '';
                    // Numeric Date Comparison
                    const [y, m, d] = date.split('-').map(Number);
                    const selDate = new Date(y, m - 1, d); // Midnight Local
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // Midnight Local

                    const now = new Date();
                    const currentHour = now.getHours();

                    timeSlots.forEach(s => {
                        const isBooked = booked.includes(s);
                        let isPast = false;

                        if (selDate < today) {
                            isPast = true; // All slots in past dates
                        } else if (selDate.getTime() === today.getTime()) {
                            // Today: Check Hour
                            const slotStartHour = parseInt(s.split(':')[0], 10);

                            // User Request: Keep valid until the hour is fully over.
                            // e.g. Slot 11-12 (11). Now 11:30 (11). 11 < 11 is False -> ENABLED.
                            // Now 12:00 (12). 11 < 12 is True -> DISABLED.
                            if (slotStartHour < currentHour) {
                                isPast = true;
                            }
                        }

                        const isDisabled = isBooked || isPast;

                        const wrap = document.createElement('div');
                        wrap.className = 'col-sm-4';
                        wrap.innerHTML = `<button type="button" class="slot-btn ${isDisabled ? 'disabled' : ''}" ${isDisabled ? 'disabled' : `onclick="selectSlot(this, '${s}')"`}>${s}</button>`;
                        slotContainer.appendChild(wrap);
                    });
                });
        }

        function selectSlot(btn, slot) {
            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            slotInput.value = slot;
            document.getElementById('slotError').style.display = 'none';
            updateGuidance("Awesome! Now choose your Payment Method below.");
        }

        function selectPayment(el) {
            document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            const val = el.getAttribute('data-value');
            el.querySelector('input').checked = true;
            updateGuidance(val === 'razorpay' ? "Perfect! Click 'Confirm' to pay via UPI or Card." : "Great! Click 'Confirm' to book with Cash on Service.");
        }

        document.getElementById('bookingForm')?.addEventListener('submit', (e) => {
            if (!slotInput.value) {
                e.preventDefault();
                document.getElementById('slotError').style.display = 'block';
                return;
            }
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            if (method === 'razorpay' && !document.getElementById('razorpay_payment_id').value) {
                e.preventDefault();
                // Open Razorpay payment gateway
                if (rzp1) {
                    rzp1.open();
                } else {
                    alert("Payment gateway is not available. Please try again or contact support.");
                }
            }
        });

        function updateGuidance(text) { document.getElementById('guidanceText').innerText = text; }

        fetchSlots();
        dateInput.onchange = fetchSlots;
    </script>
</body>

</html>