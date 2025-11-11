/**
 * Afiliados Pro - Template Preview Handler
 * Version: 1.4.2
 *
 * Simplified live preview with admin page approach
 */

(function($) {
    'use strict';

    /**
     * Initialize preview functionality (v1.4.2 - Simplified)
     */
    function initPreview() {
        const form = document.querySelector('form');
        const iframe = document.getElementById('affiliate-preview-frame');

        if (!form || !iframe) {
            console.warn('Afiliados Pro: Preview elements not found');
            return;
        }

        // Set initial transition style
        iframe.style.transition = 'opacity 0.3s ease-in-out';
        iframe.style.opacity = '1';

        /**
         * Reload preview iframe with fade effect (v1.4.2 - Simplified)
         */
        function reloadPreview() {
            // Apply fade-out effect
            iframe.style.opacity = '0.3';

            // Simple reload - the admin page approach makes this stable
            iframe.src = iframe.src;
        }

        /**
         * Restore opacity when iframe finishes loading
         */
        iframe.addEventListener('load', function() {
            iframe.style.opacity = '1';
        });

        /**
         * Attach change listeners to form elements
         */
        function attachListeners() {
            // Listen to all input, select, and checkbox changes
            const formElements = form.querySelectorAll('input, select, textarea');

            formElements.forEach(element => {
                // Skip submit button and nonce field
                if (element.type === 'submit' || element.name === 'affiliate_template_nonce') {
                    return;
                }

                // Attach change listener to all form elements
                element.addEventListener('change', reloadPreview);

                // Real-time for color inputs
                if (element.type === 'color') {
                    element.addEventListener('input', reloadPreview);
                }
            });
        }

        // Initialize listeners
        attachListeners();

        // Log initialization
        console.log('Afiliados Pro: Template preview initialized (v1.4.2 - Stable Admin Page)');
    }

    /**
     * DOM ready handler
     */
    $(document).ready(function() {
        // Wait for iframe to be available
        setTimeout(initPreview, 100);
    });

})(jQuery);
