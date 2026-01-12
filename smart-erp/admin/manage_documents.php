<?php
// admin/manage_documents.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Level 2 (Central Admin) or Level 1 (Super Admin) only
$level = $_SESSION['level'] ?? 5;
if ($level > 2) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Handle Document Deletion
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);

    // Get file path before deleting record
    $result = $conn->query("SELECT file_path FROM user_documents WHERE id=$id");
    if ($row = $result->fetch_assoc()) {
        $file_path = "../uploads/documents/" . $row['file_path'];

        // Delete physical file if it exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete database record
        $conn->query("DELETE FROM user_documents WHERE id=$id");
    }

    header("Location: manage_documents.php?msg=deleted");
    exit();
}

// Handle Verification
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] == 'verify' ? 'verified' : 'rejected';
    $conn->query("UPDATE user_documents SET status='$action' WHERE id=$id");
    header("Location: manage_documents.php?msg=success");
    exit();
}

// Fetch Pending Documents
$docs = $conn->query("SELECT d.*, u.name as user_name FROM user_documents d JOIN users u ON d.user_id = u.id ORDER BY d.uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Documents - Smart Residence Admin</title>
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

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
            padding: 30px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
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

        /* Table Styles */
        .table {
            color: var(--text-main);
        }

        .table thead th {
            background-color: var(--bg-hover);
            color: var(--text-muted);
            border: none;
        }

        .table td {
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
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
    $active_page = 'manage_documents.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Manage Documents</h2>
                <p class="text-muted fw-600">Verification portal for resident documents.</p>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-4 shadow-sm fw-700">
                <i class="fas fa-file-contract me-2"></i>
                <?php echo $docs->num_rows; ?> Documents
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 rounded-4 mb-4 fw-700 shadow-sm">
                <?php
                if ($_GET['msg'] == 'deleted')
                    echo "Document deleted permanently.";
                if ($_GET['msg'] == 'success')
                    echo "Document status updated.";
                ?>
            </div>
        <?php endif; ?>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 p-3 rounded-start-3">Resident</th>
                            <th class="border-0 p-3">Document Type</th>
                            <th class="border-0 p-3">File Name</th>
                            <th class="border-0 p-3">Uploaded At</th>
                            <th class="border-0 p-3 text-center">Status</th>
                            <th class="border-0 p-3 text-end rounded-end-3">Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($docs->num_rows > 0): ?>
                            <?php while ($row = $docs->fetch_assoc()): ?>
                                <tr>
                                    <td class="p-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div
                                                style="width:40px; height:40px; background:var(--bg-hover); border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--text-muted);">
                                                <?php echo strtoupper(substr($row['user_name'], 0, 1)); ?>
                                            </div>
                                            <div class="fw-700">
                                                <?php echo htmlspecialchars($row['user_name']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 fw-bold text-primary">
                                        <?php echo htmlspecialchars($row['doc_type']); ?>
                                    </td>
                                    <td class="p-3">
                                        <a href="../uploads/documents/<?php echo $row['file_path']; ?>" target="_blank"
                                            class="text-decoration-none fw-600 text-dark">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <?php echo substr($row['file_path'], 0, 20) . '...'; ?>
                                        </a>
                                    </td>
                                    <td class="p-3 text-muted small fw-700">
                                        <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <span class="status-badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                        <?php elseif ($row['status'] == 'verified'): ?>
                                            <span class="status-badge bg-success bg-opacity-10 text-success">Verified</span>
                                        <?php else: ?>
                                            <span class="status-badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-end">
                                        <div class="btn-group">
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <a href="?id=<?php echo $row['id']; ?>&action=verify"
                                                    class="btn btn-sm btn-success rounded-start-3 px-3 fw-bold"
                                                    onclick="return confirm('Verify this document?');">
                                                    Verify
                                                </a>
                                                <a href="?id=<?php echo $row['id']; ?>&action=reject"
                                                    class="btn btn-sm btn-outline-danger px-3 fw-bold"
                                                    onclick="return confirm('Reject this document?');">
                                                    Reject
                                                </a>
                                            <?php endif; ?>
                                            <a href="?id=<?php echo $row['id']; ?>&action=delete"
                                                class="btn btn-sm btn-light text-danger rounded-end-3 px-3"
                                                onclick="return confirm('Permanently delete?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted fw-600">No documents found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>

</html>