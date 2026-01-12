<?php
// admin/includes/sidebar.php
// Reusable role-based sidebar navigation
// Usage: include 'includes/sidebar.php'; (set $active_page before including)

$level = $_SESSION['level'] ?? 5;
$active_page = $active_page ?? 'dashboard.php';
?>
<!-- Inline script removed to disable collapse feature -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../index.php" class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-building"></i></div>
            <span>SMART <span style="color: var(--accent)">RESIDENCE</span></span>
        </a>
    </div>

    <div class="sidebar-nav mt-4">
        <a href="dashboard.php" class="nav-link <?php echo $active_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> <span class="link-text">Dashboard</span>
        </a>

        <?php if ($level == 1): // Super Admin ?>
            <a href="manage_email_requests.php" class="nav-link <?php echo $active_page == 'manage_email_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope-open-text"></i> <span class="link-text">Email Requests</span>
            </a>
            <a href="manage_users.php" class="nav-link <?php echo $active_page == 'manage_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <span class="link-text">User Directory</span>
            </a>
            <a href="manage_services.php" class="nav-link <?php echo $active_page == 'manage_services.php' ? 'active' : ''; ?>">
                <i class="fas fa-concierge-bell"></i> <span class="link-text">Services Catalog</span>
            </a>
            <a href="service_requests.php" class="nav-link <?php echo $active_page == 'service_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> <span class="link-text">Service Requests</span>
            </a>
            <a href="manage_notices.php" class="nav-link <?php echo $active_page == 'manage_notices.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i> <span class="link-text">Notice Board</span>
            </a>
            <a href="manage_complaints.php" class="nav-link <?php echo $active_page == 'manage_complaints.php' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-circle"></i> <span class="link-text">Complaints</span>
            </a>
            <a href="manage_documents.php" class="nav-link <?php echo $active_page == 'manage_documents.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder-open"></i> <span class="link-text">Resident Vault</span>
            </a>

        <?php elseif ($level == 2): // Central Admin ?>
            <a href="manage_users.php" class="nav-link <?php echo $active_page == 'manage_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <span class="link-text">User Directory</span>
            </a>
            <a href="service_requests.php" class="nav-link <?php echo $active_page == 'service_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> <span class="link-text">Service Requests</span>
            </a>
            <a href="manage_documents.php" class="nav-link <?php echo $active_page == 'manage_documents.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder-open"></i> <span class="link-text">Resident Vault</span>
            </a>

        <?php elseif ($level == 3): // Branch Admin ?>
            <a href="manage_users.php" class="nav-link <?php echo $active_page == 'manage_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <span class="link-text">User Directory</span>
            </a>
            <a href="service_requests.php" class="nav-link <?php echo $active_page == 'service_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> <span class="link-text">Service Requests</span>
            </a>
            <a href="manage_notices.php" class="nav-link <?php echo $active_page == 'manage_notices.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i> <span class="link-text">Notice Board</span>
            </a>
            <a href="manage_complaints.php" class="nav-link <?php echo $active_page == 'manage_complaints.php' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-circle"></i> <span class="link-text">Complaints</span>
            </a>

        <?php elseif ($level == 4): // Service Manager ?>
            <a href="manage_services.php" class="nav-link <?php echo $active_page == 'manage_services.php' ? 'active' : ''; ?>">
                <i class="fas fa-concierge-bell"></i> <span class="link-text">Services Catalog</span>
            </a>
            <a href="service_requests.php" class="nav-link <?php echo $active_page == 'service_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> <span class="link-text">Service Requests</span>
            </a>

        <?php endif; ?>

        <a href="profile.php" class="nav-link <?php echo $active_page == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle"></i> <span class="link-text">Profile Settings</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <!-- New Controls -->
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <!-- Sidebar Toggle Removed -->
            <div class="lang-select-wrapper">
                <select id="languageSelect" class="form-select form-select-sm bg-transparent border-0 text-muted"
                    style="width: auto; cursor: pointer;">
                    <option value="en">🇺🇸 EN</option>
                    <option value="es">🇪🇸 ES</option>
                    <option value="fr">🇫🇷 FR</option>
                    <option value="hi">🇮🇳 HI</option>
                </select>
                <!-- Hidden Google Translate Element -->
                <div id="google_translate_element" style="display:none; position: absolute; z-index: -1;"></div>
            </div>
        </div>

        <a href="../auth/logout.php" class="btn-animate-logout">
            <span class="btn-text">Sign Out</span>
            <div class="animation-container">
                <i class="fas fa-person-walking walker"></i>
                <div class="door"></div>
            </div>
        </a>
    </div>
</div>
<!-- UI Settings Persistence -->
<script src="../assets/js/ui-settings.js"></script>