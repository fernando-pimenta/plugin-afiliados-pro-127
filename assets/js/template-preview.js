/**
 * Afiliados Pro - Template Preview Handler
 * Version: 1.4.3
 *
 * Manual preview control with button trigger
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const iframe = document.getElementById('affiliate-preview-frame');
    const btn = document.getElementById('generate-preview');

    // Check if elements exist
    if (!iframe || !btn) {
        console.warn('Afiliados Pro: Preview elements not found');
        return;
    }

    // Set initial transition style
    iframe.style.transition = 'opacity 0.3s ease-in-out';
    iframe.style.opacity = '1';

    /**
     * Reload preview when button is clicked (v1.4.3)
     */
    btn.addEventListener('click', () => {
        // Apply fade-out effect
        iframe.style.opacity = '0.4';

        // Simple reload - manually triggered
        iframe.src = iframe.src;
    });

    /**
     * Restore opacity when iframe finishes loading
     */
    iframe.addEventListener('load', () => {
        iframe.style.opacity = '1';
    });

    // Log initialization
    console.log('Afiliados Pro: Manual preview control initialized (v1.4.3)');
});
