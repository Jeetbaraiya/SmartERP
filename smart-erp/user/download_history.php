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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1e3c72;
            --accent: #00d2ff;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #ffffff; color: #1e293b; }

        .statement-wrapper { max-width: 1000px; margin: 40px auto; padding: 40px; border: 1px solid #f1f5f9; border-radius: 30px; }

        .header-section { border-bottom: 2px solid #f8fafc; padding-bottom: 30px; margin-bottom: 40px; }
        .statement-label { background: var(--primary); color: white; padding: 8px 18px; border-radius: 100px; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; display: inline-block; margin-bottom: 15px; }

        .user-card { background: #f8fafc; border-radius: 20px; padding: 25px; margin-bottom: 40px; border: 1px solid #f1f5f9; }

        .table-statement thead th { background: #1e3c72; color: white; border: none; padding: 15px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; }
        .table-statement tbody td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 0.9rem; }

        .total-row { background: #f8fafc; }
        .total-label { font-weight: 800; color: #64748b; }
        .total-value { font-weight: 900; color: var(--primary); font-size: 1.25rem; }

        .btn-action { background: #f1f5f9; color: #1e293b; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; transition: all 0.2s; }
        .btn-action:hover { background: #e2e8f0; }

        @media print {
            .no-print { display: none !important; }
            .statement-wrapper { border: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>

<body>

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center py-4 no-print">
            <a href="my_requests.php" class="btn-action"><i class="fas fa-arrow-left me-2"></i> Activity Log</a>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-800 shadow-sm"><i class="fas fa-print me-2"></i> Download PDF / Print</button>
        </div>

        <div class="statement-wrapper">
            <div class="header-section d-flex justify-content-between align-items-start">
                <div>
                    <div class="statement-label">ACCOUNT STATEMENT</div>
                    <h2 class="fw-900 text-primary mb-1">SMART <span style="color: var(--accent)">RESIDENCE</span></h2>
                    <p class="text-muted fw-600 mb-0 small">Residential Service & Management ERP</p>
                </div>
                <div class="text-end">
                    <div class="fw-800 text-dark">GENERATED ON</div>
                    <div class="fw-700 text-muted small"><?php echo date('d M, Y | h:i A'); ?></div>
                </div>
            </div>

            <div class="user-card">
                <div class="row items-center">
                    <div class="col-6 border-end">
                        <div class="small fw-800 text-muted text-uppercase mb-1" style="letter-spacing: 0.5px;">Recipient</div>
                        <h4 class="fw-900 text-dark mb-0"><?php echo $user_name; ?></h4>
                        <div class="small fw-700 text-muted">Authorized Resident</div>
                    </div>
                    <div class="col-6 ps-4">
                        <div class="small fw-800 text-muted text-uppercase mb-1" style="letter-spacing: 0.5px;">Account Identifier</div>
                        <h4 class="fw-900 text-primary mb-0">RES-<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></h4>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-statement align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Transaction Date</th>
                            <th>Entry ID</th>
                            <th>Service Description</th>
                            <th>Status Badge</th>
                            <th class="text-end">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): 
                                $total_spent += $row['price'];
                                ?>
                                <tr>
                                    <td class="text-muted small"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="fw-800 text-primary">#REQ-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $row['service_name']; ?></td>
                                    <td>
                                        <?php 
                                        $s = $row['status'];
                                        $badgeClass = ($s == 'completed') ? 'bg-success' : (($s == 'rejected' || $s == 'refunded') ? 'bg-danger' : 'bg-warning');
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> bg-opacity-10 text-<?php echo str_replace('bg-', '', $badgeClass); ?> rounded-pill px-3 py-2 fw-800" style="font-size: 0.6rem;">
                                            <?php echo strtoupper($s); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-800 text-dark">Rs. <?php echo number_format($row['price'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted fw-600">No transactions recorded for this residency.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4" class="text-end py-4 total-label">Cumulative Settled Amount</td>
                            <td class="text-end py-4 total-value">Rs. <?php echo number_format($total_spent, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-5 text-center">
                <div class="small fw-700 text-muted opacity-50">This document is meticulously generated by the Smart Residence Automated Billing System.</div>
                <div class="small fw-700 text-muted opacity-50 mt-1">Verification Code: <?php echo strtoupper(bin2hex(random_bytes(8))); ?></div>
            </div>
        </div>
    </div>

</body>

</html>
