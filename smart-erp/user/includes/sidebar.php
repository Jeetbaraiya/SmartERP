<?php
// user/includes/sidebar.php
$active_page = $active_page ?? 'dashboard.php';
?>
<!-- Inline script removed to disable collapse feature -->
<aside class="sidebar" id="sidebar">
    <a href="dashboard.php" class="nav-logo">
        <div class="bg-primary text-white p-2 rounded-3">
            <i class="fas fa-building"></i>
        </div>
        <span>SMART<span style="color: var(--accent)">ERP</span></span>
    </a>
    <ul class="sidebar-nav">
        <li>
            <a href="dashboard.php" class="sidebar-link <?php echo $active_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> <span class="link-text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="services.php" class="sidebar-link <?php echo $active_page == 'services.php' ? 'active' : ''; ?>">
                <i class="fas fa-tools"></i> <span class="link-text">Book Services</span>
            </a>
        </li>
        <li>
            <a href="my_requests.php"
                class="sidebar-link <?php echo $active_page == 'my_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> <span class="link-text">My Activities</span>
            </a>
        </li>
        <li>
            <a href="documents.php" class="sidebar-link <?php echo $active_page == 'documents.php' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> <span class="link-text">Security Vault</span>
            </a>
        </li>
        <li>
            <a href="complaints.php"
                class="sidebar-link <?php echo $active_page == 'complaints.php' ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i> <span class="link-text">Help & Support</span>
            </a>
        </li>
        <li>
            <a href="profile.php" class="sidebar-link <?php echo $active_page == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> <span class="link-text">Profile Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer mt-auto pt-4 border-top">
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
            <span class="btn-text">Log Out</span>
            <div class="animation-container">
                <i class="fas fa-person-walking walker"></i>
                <div class="door"></div>
            </div>
        </a>
    </div>
</aside>
<script src="../assets/js/ui-settings.js"></script>