/**
 * Afiliados Pro - Template Preview Handler
 * Version: 1.4.1
 *
 * Handles live preview updates in the Template Builder admin page
 */

(function($) {
    'use strict';

    /**
     * Initialize preview functionality
     */
    function initPreview() {
        const form = document.querySelector('form');
        const iframe = document.getElementById('affiliate-preview-frame');

        if (!form || !iframe) {
            console.warn('Afiliados Pro: Preview elements not found');
            return;
        }

        // Set initial transition style (v1.4.1)
        iframe.style.transition = 'opacity 0.3s ease-in-out';
        iframe.style.opacity = '1';

        // Track if we're currently reloading to prevent multiple reloads
        let isReloading = false;
        let reloadTimeout = null;

        /**
         * Reload preview iframe with debounce and loading effect (v1.4.1)
         */
        function reloadPreview() {
            if (isReloading) return;

            // Clear existing timeout
            if (reloadTimeout) {
                clearTimeout(reloadTimeout);
            }

            // Debounce reload by 500ms
            reloadTimeout = setTimeout(() => {
                isReloading = true;

                // Apply fade-out effect (v1.4.1)
                iframe.style.opacity = '0.3';

                // Get current settings from form
                const formData = new FormData(form);
                const params = new URLSearchParams();

                // Build query params from form data
                for (const [key, value] of formData.entries()) {
                    if (key !== 'affiliate_template_nonce' && key !== '_wp_http_referer') {
                        params.append(key, value);
                    }
                }

                // Reload iframe with updated settings
                const baseUrl = iframe.src.split('?')[0];
                iframe.src = baseUrl + '&' + params.toString() + '&t=' + Date.now();

                // Reset reload flag after animation
                setTimeout(() => {
                    isReloading = false;
                }, 300);
            }, 500);
        }

        /**
         * Restore opacity when iframe finishes loading (v1.4.1)
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

                // Color picker inputs
                if (element.type === 'color') {
                    element.addEventListener('change', reloadPreview);
                    element.addEventListener('input', reloadPreview); // Real-time for color
                }
                // Checkbox inputs
                else if (element.type === 'checkbox') {
                    element.addEventListener('change', reloadPreview);
                }
                // Select dropdowns
                else if (element.tagName === 'SELECT') {
                    element.addEventListener('change', reloadPreview);
                }
                // Text/number inputs
                else {
                    element.addEventListener('change', reloadPreview);
                }
            });
        }

        // Initialize listeners
        attachListeners();

        // Log initialization
        console.log('Afiliados Pro: Template preview initialized (v1.4.1)');
    }

    /**
     * DOM ready handler
     */
    $(document).ready(function() {
        // Wait for iframe to be available
        setTimeout(initPreview, 100);
    });

})(jQuery);
