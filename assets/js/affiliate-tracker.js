/**
 * Afiliados Pro - Rastreamento de Cliques
 * Version: 1.4.7
 *
 * Lightweight client-side click tracking for affiliate products
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // Check if tracker config is available
    if (typeof affiliateTracker === 'undefined') {
        console.warn('Afiliados Pro: Tracker configuration not found');
        return;
    }

    /**
     * Send click data to REST API endpoint
     *
     * @param {string} productId - Product identifier
     * @param {string} source - Click source (button, title, image, etc)
     */
    const sendClick = (productId, source) => {
        // Validate input
        if (!productId || productId.trim() === '') {
            console.warn('Afiliados Pro: Invalid product ID');
            return;
        }

        // Send async request (fire and forget)
        fetch(affiliateTracker.restUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': affiliateTracker.nonce
            },
            body: JSON.stringify({
                product_id: productId,
                source: source || 'button'
            })
        }).catch(error => {
            // Silent fail - tracking should never break user experience
            console.debug('Afiliados Pro: Tracking error', error);
        });
    };

    /**
     * Attach click listener to document (event delegation)
     */
    document.body.addEventListener('click', event => {
        // Find closest element with data-aff-id attribute
        const target = event.target.closest('[data-aff-id]');

        if (!target) {
            return;
        }

        // Extract tracking data from element
        const productId = target.dataset.affId;
        const source = target.dataset.source || 'button';

        // Record click
        sendClick(productId, source);
    });

    // Log initialization
    console.log('Afiliados Pro: Click tracking initialized (v1.4.7)');
});
