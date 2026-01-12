/**
 * Theme Manager for Smart Residence ERP
 * Handles Dark/Light mode toggling with persistence
 */

const themeManager = {
    // specific key for this user
    get STORAGE_KEY() {
        return 'smart_erp_theme_' + (window.currentUserId || 'default');
    },

    // Initialize theme
    init() {
        const storedTheme = localStorage.getItem(this.STORAGE_KEY);
        if (storedTheme) {
            this.applyTheme(storedTheme);
        } else {
            // Default to light
            this.applyTheme('light');
        }

        // Global Sync: Listen for changes in other tabs
        window.addEventListener('storage', (e) => {
            if (e.key === this.STORAGE_KEY) {
                this.applyTheme(e.newValue);
            }
        });

        // Add event listener to toggle button if it exists
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            this.updateButtonState(toggleBtn);
            toggleBtn.addEventListener('click', () => this.toggle());
        }
    },

    // Toggle between light and dark
    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        this.applyTheme(newTheme);
        localStorage.setItem(this.STORAGE_KEY, newTheme);
    },

    // Apply theme to DOM
    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);

        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            this.updateButtonState(toggleBtn);
        }
    },

    // Update button icon/label
    updateButtonState(btn) {
        const theme = document.documentElement.getAttribute('data-theme');
        const isDark = theme === 'dark';

        // Update Aria Label
        btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');

        // Update Icon
        const iconInfo = btn.querySelector('.theme-icon-info');
        if (iconInfo) {
            iconInfo.innerHTML = isDark
                ? '<i class="fas fa-sun"></i> Light Mode'
                : '<i class="fas fa-moon"></i> Dark Mode';
        } else {
            // Fallback
            btn.innerHTML = isDark
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
        }
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    themeManager.init();
});
