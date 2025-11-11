/**
 * Afiliados Pro - Template Preview Handler
 * Version: 1.4.6
 *
 * Manual preview control with public endpoint loading and dynamic height
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

    // Get preview URL from data attribute
    const previewUrl = iframe.dataset.previewUrl;

    if (!previewUrl) {
        console.error('Afiliados Pro: Preview URL not found');
        return;
    }

    // Set initial transition style
    iframe.style.transition = 'opacity 0.3s ease-in-out';
    iframe.style.opacity = '1';

    /**
     * Dynamic height adjustment (v1.4.6)
     */
    const adjustHeight = () => {
        const height = Math.max(window.innerHeight * 0.8, 600);
        iframe.style.height = height + 'px';
    };

    // Adjust height on load and resize
    adjustHeight();
    window.addEventListener('resize', adjustHeight);

    /**
     * Load preview when button is clicked (v1.4.4)
     */
    btn.addEventListener('click', () => {
        // Apply fade-out effect
        iframe.style.opacity = '0.4';

        // Load preview URL
        iframe.src = previewUrl;
    });

    /**
     * Restore opacity when iframe finishes loading
     */
    iframe.addEventListener('load', () => {
        iframe.style.opacity = '1';
    });

    // Log initialization
    console.log('Afiliados Pro: Manual preview control initialized (v1.4.6 - Split Layout with Dynamic Height)');
});
