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
            --secondary: #2a5298;
            --accent: #00d2ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            flex-direction: column;
            -webkit-print-color-adjust: exact;
        }

        .invoice-card {
            width: 100%;
            max-width: 800px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 40px auto;
            position: relative;
        }

        .invoice-header {
            background: var(--primary);
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .invoice-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, transparent 40%, rgba(255, 255, 255, 0.1) 100%);
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 2;
        }

        .invoice-title {
            font-size: 3rem;
            font-weight: 900;
            opacity: 0.1;
            position: absolute;
            right: 20px;
            top: 10px;
            letter-spacing: 5px;
        }

        .invoice-body {
            padding: 40px;
        }

        .info-group {
            margin-bottom: 25px;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .table-invoice th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .table-invoice td {
            padding: 20px 0;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-weight: 600;
        }

        .total-section {
            background: #f8fafc;
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
        }

        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(30, 60, 114, 0.2);
        }

        .btn-print:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(30, 60, 114, 0.3);
            color: white;
        }

        .btn-back {
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .invoice-card {
                box-shadow: none;
                max-width: 100%;
                border: none;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .invoice-header {
                background: white !important;
                color: black !important;
                border-bottom: 2px solid black;
                padding: 20px 0;
            }

            .brand-logo {
                color: black !important;
            }

            .invoice-title {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container py-4 no-print d-flex justify-content-between align-items-center" style="max-width: 800px;">
        <a href="javascript:void(0)" onclick="window.close()" class="btn-back"><i class="fas fa-arrow-left me-2"></i>
            Back</a>
        <button onclick="window.print()" class="btn btn-print"><i class="fas fa-download me-2"></i> Download /
            Print</button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header">
            <div class="brand-logo">
                <i class="fas fa-building fa-lg"></i>
                <span>SMART RESIDENCE</span>
            </div>
            <div class="invoice-title">INVOICE</div>
            <div style="position: relative; z-index: 2; margin-top: 20px;">
                <div style="opacity: 0.8; font-weight: 600;">#<?php echo str_pad($data['id'], 6, '0', STR_PAD_LEFT); ?>
                </div>
                <div style="font-weight: 700; font-size: 1.2rem;">
                    <?php echo date('F d, Y', strtotime($data['created_at'])); ?>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="row mb-5">
                <div class="col-6">
                    <div class="info-group">
                        <div class="info-label">Billed To</div>
                        <div class="info-value"><?php echo $data['user_name']; ?></div>
                        <div class="small text-muted fw-600"><?php echo $data['email']; ?></div>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="info-group">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value"><?php echo strtoupper($data['payment_method'] ?? 'Online'); ?></div>
                        <div class="badge bg-success bg-opacity-10 text-success rounded-pill mt-1">PAID</div>
                    </div>
                </div>
            </div>

            <table class="table table-invoice w-100">
                <thead>
                    <tr>
                        <th class="text-start">Description</th>
                        <th class="text-center">Date</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start">
                            <div class="fw-800 text-dark"><?php echo $data['service_name']; ?></div>
                            <div class="small text-muted">Service Reference: SR-<?php echo $data['id']; ?></div>
                        </td>
                        <td class="text-center">
                            <?php echo date('M d', strtotime($data['booking_date'])); ?>
                        </td>
                        <td class="text-end fw-800">
                            ₹<?php echo number_format($data['price'], 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="total-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-800 fs-5 text-dark">Total Paid</div>
                        <div class="small text-muted fw-600">Including Taxes & Fees</div>
                    </div>
                    <div class="display-6 fw-900 text-primary">₹<?php echo number_format($data['price'], 2); ?></div>
                </div>
            </div>

            <div class="mt-5 text-center">
                <div class="small fw-700 text-muted">Thank you for choosing Smart Residence!</div>
                <div class="small text-muted mt-1" style="font-size: 0.65rem;">Generated digitally. No signature
                    required.</div>
            </div>
        </div>
    </div>

</body>

</html>