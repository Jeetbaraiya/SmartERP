<?php
// user/download_history.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Resident';

// Fetch Only Completed Services
$sql = "SELECT r.*, s.name as service_name, s.price 
        FROM service_requests r 
        JOIN services s ON r.service_id = s.id 
        WHERE r.user_id = $user_id 
        AND r.status = 'completed'
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
$total_spent = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - <?php echo $user_name; ?></title>
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
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-body: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            -webkit-print-color-adjust: exact;
        }

        .statement-wrapper {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 50px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .statement-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .header-section {
            margin-bottom: 50px;
            border-bottom: 2px solid #f8fafc;
            padding-bottom: 30px;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .account-card {
            background: linear-gradient(145deg, #f8fafc, #fff);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 40px;
        }

        .label-sm {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .value-lg {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .table-custom thead th {
            background: #f8fafc;
            color: var(--text-muted);
            padding: 15px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            border: none;
        }

        .table-custom tbody td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            color: var(--text-main);
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .status-refunded {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.2);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(30, 60, 114, 0.3);
            background: var(--secondary);
            color: white;
        }

        .btn-back {
            background: transparent;
            border: 2px solid #e2e8f0;
            color: var(--text-muted);
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-back:hover {
            border-color: var(--text-muted);
            color: var(--text-main);
            background: white;
        }

        @media print {
            body {
                background: white;
            }

            .statement-wrapper {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                border: none;
            }

            .no-print {
                display: none !important;
            }

            .badge-status {
                border: 1px solid #ddd;
                background: transparent !important;
                color: black !important;
            }
        }
    </style>
</head>

<body>

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center py-4 no-print">
            <a href="javascript:void(0)" onclick="window.close()" class="btn-back"><i class="fas fa-arrow-left"></i>
                Back to History</a>
            <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print me-2"></i> Print
                Statement</button>
        </div>

        <div class="statement-wrapper">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center header-section">
                <div class="brand-logo">
                    <div class="brand-icon"><i class="fas fa-building"></i></div>
                    <span>SMART <span style="color: var(--accent)">RESIDENCE</span></span>
                </div>
                <div class="text-end">
                    <div class="label-sm">Statement Date</div>
                    <div class="fw-800 text-dark"><?php echo date('d M, Y'); ?></div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="account-card">
                <div class="row">
                    <div class="col-md-6 border-end-md">
                        <div class="label-sm">Resident Details</div>
                        <div class="value-lg mb-1"><?php echo $user_name; ?></div>
                        <div class="small text-muted fw-600">ID:
                            RES-<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th width="20%">Date</th>
                            <th width="40%">Description & ID</th>
                            <th width="20%" class="text-center">Status</th>
                            <th width="20%" class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()):
                                $total_spent += $row['price'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-700 text-dark">
                                            <?php echo date('d M, Y', strtotime($row['created_at'])); ?>
                                        </div>
                                        <div class="small text-muted fw-600">
                                            <?php echo date('h:i A', strtotime($row['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-800 text-primary"><?php echo $row['service_name']; ?></div>
                                        <div class="small text-muted fw-600">Ref:
                                            #SR-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $s = $row['status'];
                                        $cls = 'status-warning';
                                        if ($s == 'completed')
                                            $cls = 'status-completed';
                                        elseif ($s == 'rejected')
                                            $cls = 'status-rejected';
                                        elseif ($row['refund_status'] == 'refunded') {
                                            $s = 'refunded';
                                            $cls = 'status-refunded';
                                        }
                                        ?>
                                        <span class="badge-status <?php echo $cls; ?>"><?php echo strtoupper($s); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-800 text-dark">₹<?php echo number_format($row['price'], 2); ?></div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted fw-700">No completed transactions found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc;">
                            <td colspan="3" class="text-end py-4 fw-800 text-muted text-uppercase letter-spacing-1">
                                Total Settled Amount</td>
                            <td class="text-end py-4 fw-900 fs-5 text-primary">
                                ₹<?php echo number_format($total_spent, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-5 pt-4 text-center border-top">
                <p class="small text-muted fw-600 mb-1">Pass System Generated Document</p>
                <p class="small text-muted fw-600">Reference: <?php echo strtoupper(bin2hex(random_bytes(6))); ?></p>
            </div>
        </div>
    </div>

</body>

</html>