/**
 * Smart Complaint Analysis
 * Auto-detects category and priority based on keywords in description.
 */

document.addEventListener('DOMContentLoaded', function () {
    const descInput = document.getElementById('complaint_text');
    const categorySelect = document.getElementById('category');
    const prioritySelect = document.getElementById('priority');

    if (!descInput || !categorySelect || !prioritySelect) return;

    // Keyword Dictionaries
    const keywords = {
        'plumbing': ['leak', 'pipe', 'water', 'tap', 'drip', 'flow', 'drain', 'clog', 'sink', 'toilet'],
        'electrical': ['light', 'power', 'spark', 'shock', 'switch', 'fuse', 'trip', 'outage', 'mcb', 'bulb'],
        'cleaning': ['dust', 'dirty', 'garbage', 'trash', 'floor', 'clean', 'mess', 'hygiene', 'sweep', 'mop'],
        'security': ['guard', 'theft', 'stolen', 'gate', 'camera', 'cctv', 'stranger', 'noise', 'fight'],
        'lift': ['lift', 'elevator', 'stuck', 'door']
    };

    const urgency = {
        'high': ['fire', 'spark', 'gas', 'smoke', 'stuck', 'emergency', 'danger', 'electric shock', 'burst'],
        'medium': ['not working', 'fail', 'broken', 'stopped']
    };

    descInput.addEventListener('input', function () {
        const text = this.value.toLowerCase();

        // Auto-Detect Category
        for (const [cat, words] of Object.entries(keywords)) {
            if (words.some(w => text.includes(w))) {
                // Find option with value or text matching category regex
                for (let i = 0; i < categorySelect.options.length; i++) {
                    if (categorySelect.options[i].text.toLowerCase().includes(cat)) {
                        categorySelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        // Auto-Detect Priority
        let foundUrgency = false;
        for (const [level, words] of Object.entries(urgency)) {
            if (words.some(w => text.includes(w))) {
                const priorityValue = level.charAt(0).toUpperCase() + level.slice(1); // 'High', 'Medium'
                for (let i = 0; i < prioritySelect.options.length; i++) {
                    if (prioritySelect.options[i].value.toLowerCase() === level) {
                        prioritySelect.selectedIndex = i;
                        foundUrgency = true;
                        break;
                    }
                }
            }
            if (foundUrgency) break;
        }

        // Visual Feedback (Flash effect)
        if (foundUrgency) {
            prioritySelect.style.borderColor = 'var(--primary)';
            setTimeout(() => prioritySelect.style.borderColor = 'var(--border-color)', 500);
        }
    });
});
