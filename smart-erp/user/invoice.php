<?php
// user/invoice.php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    die("Invalid Access");
}

$request_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch Request Details
$sql = "SELECT r.*, s.name as service_name, s.price, u.name as user_name, u.email 
        FROM service_requests r 
        JOIN services s ON r.service_id = s.id 
        JOIN users u ON r.user_id = u.id 
        WHERE r.id = $request_id AND r.user_id = $user_id";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Invoice not found or access denied.");
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #1e3c72;
            --accent: #00d2ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fdfdfd;
            color: #1e293b;
        }

        .invoice-card {
            max-width: 850px;
            margin: 40px auto;
            background: white;
            border-radius: 40px;
            padding: 60px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .invoice-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle at top right, rgba(0, 210, 255, 0.05) 0%, transparent 70%);
        }

        .header-section {
            border-bottom: 2px solid #f8fafc;
            padding-bottom: 40px;
            margin-bottom: 40px;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: var(--primary);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .info-value {
            font-weight: 700;
            color: #1e293b;
        }

        .table-invoice thead th {
            background: #f8fafc;
            border: none;
            padding: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }

        .table-invoice tbody td {
            padding: 30px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .grand-total-section {
            background: #f8fafc;
            border-radius: 24px;
            padding: 30px;
            margin-top: 40px;
        }

        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 14px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-print:hover {
            background: #152c55;
            transform: translateY(-2px);
            color: white;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-card {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 20px;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container py-4">
        <div class="text-end mb-4 no-print d-flex justify-content-center gap-3">
            <a href="my_requests.php" class="btn btn-light rounded-pill px-4 fw-700"><i
                    class="fas fa-arrow-left me-2"></i> Back</a>
            <button onclick="window.print()" class="btn btn-print rounded-pill"><i class="fas fa-print me-2"></i>
                Download / Print</button>
        </div>

        <div class="invoice-card">
            <div class="header-section d-flex justify-content-between align-items-start">
                <div>
                    <div class="brand-logo mb-3">
                        <div class="brand-icon"><i class="fas fa-building"></i></div>
                        <span>SMART <span style="color: var(--accent)">RESIDENCE</span></span>
                    </div>
                    <div class="small fw-600 text-muted">A project by Smart Residence Management Group</div>
                    <div class="small fw-600 text-muted">Building B, Electronic City Phase II, Bangalore</div>
                </div>
                <div class="text-end">
                    <h1 class="fw-900 display-6 mb-1">INVOICE</h1>
                    <div class="fw-800 text-primary">ID: #<?php echo str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div class="info-value mt-2"><?php echo date('d M, Y', strtotime($data['created_at'])); ?></div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="info-label">Customer Details</div>
                    <h5 class="fw-800 mb-1"><?php echo $data['user_name']; ?></h5>
                    <div class="fw-600 text-muted"><?php echo $data['email']; ?></div>
                    <div class="fw-600 text-muted">Resident ID:
                        RES-<?php echo str_pad($user_id, 3, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="info-label">Payment & Service</div>
                    <div class="fw-800 mb-1 text-uppercase">Method: <?php echo $data['payment_method'] ?: 'Standard'; ?>
                    </div>
                    <div class="badge bg-success bg-opacity-10 text-success fw-800 px-3 py-2 rounded-pill">SERVICE
                        COMPLETED</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-invoice align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Schedule</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="fw-800 fs-5 text-dark"><?php echo $data['service_name']; ?></div>
                                <div class="small fw-600 text-muted mt-1">Request Token:
                                    <?php echo strtoupper(bin2hex(random_bytes(3))); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-800"><?php echo date('d M, Y', strtotime($data['booking_date'])); ?>
                                </div>
                                <div class="small fw-600 text-muted">
                                    <?php echo $data['booking_slot'] ?: 'Flexible Time'; ?>
                                </div>
                            </td>
                            <td class="text-end fw-900 fs-5">
                                Rs. <?php echo number_format($data['price'], 2); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grand-total-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-800 text-dark">Total Payable Amount</div>
                        <div class="small fw-600 text-muted">Inclusive of all convenience fees</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-900 display-5 text-primary">Rs. <?php echo number_format($data['price'], 2); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 text-center">
                <div class="fw-700 text-dark mb-2">Thank you for your trust!</div>
                <div class="small fw-600 text-muted opacity-50">This is a digitally generated invoice valid for all
                    official purposes.</div>
                <div class="mt-4">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=VERIFIED-INV-<?php echo $data['id']; ?>"
                        alt="Verify" class="opacity-75" style="width: 80px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>

</html>
