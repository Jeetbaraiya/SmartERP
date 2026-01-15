<?php
// user/documents.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// AUTO-FIX: Ensure required columns exist
try {
    // Check document_type
    $check = $conn->query("SHOW COLUMNS FROM `user_documents` LIKE 'document_type'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE `user_documents` ADD COLUMN `document_type` varchar(50) NOT NULL AFTER `user_id`");
    }

    // Check notes
    $check = $conn->query("SHOW COLUMNS FROM `user_documents` LIKE 'notes'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE `user_documents` ADD COLUMN `notes` text DEFAULT NULL AFTER `file_path`");
    }

    // Check status
    $check = $conn->query("SHOW COLUMNS FROM `user_documents` LIKE 'status'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE `user_documents` ADD COLUMN `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
    }
} catch (Exception $e) {
    // Silent fail or log
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $doc_id = (int) $conn->real_escape_string($_GET['id']);

    // Verify ownership and status
    $check = $conn->query("SELECT * FROM user_documents WHERE id=$doc_id AND user_id=$user_id");
    if ($check->num_rows > 0) {
        $doc = $check->fetch_assoc();
        if ($doc['status'] != 'verified') {
            // Delete file
            $file_path = "../uploads/documents/" . $doc['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete record
            $conn->query("DELETE FROM user_documents WHERE id=$doc_id");
            header("Location: documents.php?msg=deleted");
            exit();
        } else {
            $error = "Cannot delete verified documents.";
        }
    }
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $doc_type = $conn->real_escape_string($_POST['doc_type']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // File Upload Logic
    $target_dir = "../uploads/documents/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;

    // Validate
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

    // Strict typing for Agreements
    if ($doc_type == 'rental_agreement') {
        $allowed = ['pdf'];
    }

    if (in_array($file_ext, $allowed)) {
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO user_documents (user_id, document_type, file_path, notes) 
                    VALUES ($user_id, '$doc_type', '$new_filename', '$notes')";
            if ($conn->query($sql)) {
                header("Location: documents.php?msg=uploaded");
                exit();
            }
        }
    }
    $error = "Invalid document upload. Please upload a valid document that matches the document type.";
}

// Fetch Documents
$docs = $conn->query("SELECT * FROM user_documents WHERE user_id=$user_id ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Vault - Smart Residence</title>
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
            height: 100%;
        }

        .upload-zone {
            border: 2px dashed var(--border-color);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            background: var(--bg-hover);
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: var(--bg-body);
        }

        .doc-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
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

        .form-control,
        .form-select {
            background-color: var(--bg-input);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .form-control:focus,
        .form-select:focus {
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
        <?php $active_page = 'documents.php';
        include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="fw-800 mb-1">Security Vault</h4>
                    <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">Securely store &
                        verify your documents</p>
                </div>
            </header>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'uploaded'): ?>
                <div class="alert alert-success">Document uploaded successfully.</div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Upload Form -->
                <div class="col-lg-4">
                    <div class="card-custom">
                        <h5 class="fw-800 mb-4">Upload New Document</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="item form-label fw-800 text-muted small text-uppercase">Document
                                    Type</label>
                                <select name="doc_type" class="form-select rounded-3 py-2 fw-bold" required>
                                    <option value="aadhar_card">Aadhar Card</option>
                                    <option value="pan_card">PAN Card</option>
                                    <option value="driving_license">Driving License</option>
                                    <option value="rental_agreement">Rental Agreement</option>
                                    <option value="other">Other Document</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-800 text-muted small text-uppercase">Notes</label>
                                <textarea name="notes" class="form-control rounded-3 py-2" rows="2"
                                    placeholder="Optional description"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="upload-zone w-100 d-block">
                                    <input type="file" name="document" class="d-none" required
                                        onchange="document.getElementById('fileName').textContent = this.files[0].name">
                                    <div class="doc-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <h6 class="fw-800 mb-1">Click to Upload</h6>
                                    <p class="text-muted small fw-600 mb-0" id="fileName">PDF, JPG, PNG (Max 5MB)</p>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-800">Securely
                                Upload</button>
                        </form>
                    </div>
                </div>

                <!-- Document List -->
                <div class="col-lg-8">
                    <div class="card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-800 mb-0">Stored Documents</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                <i class="fas fa-lock me-1"></i> Encrypted
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>File Details</th>
                                        <th>Type</th>
                                        <th>Uploaded</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($docs && $docs->num_rows > 0): ?>
                                        <?php while ($row = $docs->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="doc-icon" style="font-size: 1.5rem;">
                                                            <?php
                                                            $ext = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                                                            if ($ext == 'pdf')
                                                                echo '<i class="fas fa-file-pdf text-danger"></i>';
                                                            elseif (in_array($ext, ['jpg', 'jpeg', 'png']))
                                                                echo '<i class="fas fa-file-image text-primary"></i>';
                                                            else
                                                                echo '<i class="fas fa-file text-muted"></i>';
                                                            ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-800 text-main" style="font-size: 0.9rem;">
                                                                <?php echo $row['file_path']; ?>
                                                            </div>
                                                            <div class="small text-muted fw-600">
                                                                <?php echo $row['notes'] ? $row['notes'] : 'No notes'; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border fw-700">
                                                        <?php echo ucwords(str_replace('_', ' ', $row['document_type'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small fw-700">
                                                    <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $s = $row['status'];
                                                    $cls = 'warning';
                                                    if ($s == 'verified')
                                                        $cls = 'success';
                                                    elseif ($s == 'rejected')
                                                        $cls = 'danger';
                                                    ?>
                                                    <span
                                                        class="badge bg-<?php echo $cls; ?> bg-opacity-10 text-<?php echo $cls; ?> text-uppercase"
                                                        style="font-size: 0.7rem;">
                                                        <?php echo $s; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <?php
                                                    $file_url = "../uploads/documents/" . $row['file_path'];
                                                    ?>
                                                    <a href="<?php echo $file_url; ?>" target="_blank"
                                                        class="btn btn-light btn-sm fw-800 text-primary bg-light border-0">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <?php if ($s != 'verified'): ?>
                                                        <a href="documents.php?action=delete&id=<?php echo $row['id']; ?>"
                                                            class="btn btn-light btn-sm text-danger border-0"
                                                            onclick="return confirm('Are you sure you want to delete this document?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted fw-700">No documents in
                                                vault.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>

</html>