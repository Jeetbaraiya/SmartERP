/**
 * Smart Residence ERP - Theme Initializer
 * Prevents Flash of Incorrect Theme (FOUC)
 * Must be loaded in <head>
 */
(function () {
    // Use User ID if available (injected via PHP), otherwise default
    const userId = window.currentUserId || 'default';
    const STORAGE_KEY = 'smart_erp_theme_' + userId;

    try {
        const storedTheme = localStorage.getItem(STORAGE_KEY);
        let theme = 'light';

        if (storedTheme) {
            theme = storedTheme;
        }
        // User Request: Default is Light if no preference. No system sync.

        // Apply immediately
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
    } catch (e) {
        console.error('Theme init failed', e);
    }
})();
