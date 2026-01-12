/**
 * UI Settings Management
 * Handles Sidebar Collapse state and Language Preferences using LocalStorage.
 */

// --- IMMEDIATE STATE CHECK (Disabled) ---
(function () {
    // const savedState = localStorage.getItem('sidebarState');
    // if (savedState === 'collapsed') {
    //     document.body.classList.add('sidebar-collapsed');
    // }
    // Ensure we start expanded if button is gone
    document.body.classList.remove('sidebar-collapsed');
    localStorage.setItem('sidebarState', 'expanded');
})();

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleBtn = document.getElementById('sidebarToggle');
    const langSelect = document.getElementById('languageSelect');

    // --- SIDEBAR TOGGLE EVENT ---
    // --- SIDEBAR TOGGLE EVENT ---

    // Desktop Toggle (Footer)
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-collapsed');

            // Save new state (only on desktop)
            if (window.innerWidth > 991) {
                if (document.body.classList.contains('sidebar-collapsed')) {
                    localStorage.setItem('sidebarState', 'collapsed');
                } else {
                    localStorage.setItem('sidebarState', 'expanded');
                }
            }
        });
    }

    // Mobile Toggle (Header) - Dynamic Element Check
    const mobileToggle = document.getElementById('mobileSidebarToggle');

    // Create Overlay if it doesn't exist
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function toggleMobileSidebar() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleMobileSidebar();
        });
    }

    // Close on overlay click
    overlay.addEventListener('click', toggleMobileSidebar);

    // Close on route change (link click) on mobile
    const navLinks = sidebar.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 991) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    });

    // --- LANGUAGE SETTINGS ---

    // Check saved language
    const savedLang = localStorage.getItem('appLanguage') || 'en';
    if (langSelect) {
        langSelect.value = savedLang; // Set initial value of the select element

        langSelect.addEventListener('change', function () {
            const lang = this.value;
            localStorage.setItem('appLanguage', lang);
            changeLanguage(lang);
        });
    }

    // Initialize Google Translate functionality
    if (!document.getElementById('google_translate_script')) {
        const script = document.createElement('script');
        script.id = 'google_translate_script';
        script.src = "//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
        document.body.appendChild(script);
    }
});

// Google Translate Init
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,es,fr,hi',
        autoDisplay: false
    }, 'google_translate_element');

    // Apply saved language after init
    setTimeout(() => {
        const savedLang = localStorage.getItem('appLanguage') || 'en';
        if (savedLang !== 'en') {
            changeLanguage(savedLang);
        }
    }, 1000); // Delay to ensure widget loads
}

function changeLanguage(langCode) {
    const select = document.querySelector('.goog-te-combo');
    if (select) {
        select.value = langCode;
        select.dispatchEvent(new Event('change'));
    } else {
        // Fallback or retry if Google widget isn't ready
        // console.log("Google Translate widget not ready yet.");
    }
}
