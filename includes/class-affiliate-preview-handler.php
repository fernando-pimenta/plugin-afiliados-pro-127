<?php
/**
 * PAP - Preview Handler
 *
 * Handles preview rendering via public endpoint
 *
 * @package PAP
 * @version 1.4.5
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class PAP_Preview_Handler
 *
 * Renders preview template via public endpoint for completely isolated preview
 */
class PAP_Preview_Handler {

    /**
     * Initialize preview handler (v1.4.5 - Public endpoint with cache)
     */
    public static function init() {
        // Register public endpoint for preview (v1.4.4)
        add_action('init', [__CLASS__, 'register_preview_endpoint']);

        // Handle template redirect for preview
        add_action('template_redirect', [__CLASS__, 'handle_preview_request']);

        // Clear cache when settings are saved (v1.4.5)
        add_action('update_option_affiliate_template_settings', [__CLASS__, 'clear_preview_cache']);

        // Log initialization if debug is enabled
        pap_log('Preview Handler: Initialized (v1.4.5 - Public Endpoint with Cache)');
    }

    /**
     * Register public endpoint for preview (v1.4.4)
     *
     * Creates public URL: /affiliate-preview/
     */
    public static function register_preview_endpoint() {
        // Add rewrite rule for preview endpoint
        add_rewrite_rule(
            'affiliate-preview/?$',
            'index.php?affiliate_preview=1',
            'top'
        );

        // Add query var for preview
        add_filter('query_vars', function($vars) {
            $vars[] = 'affiliate_preview';
            return $vars;
        });

        // Log registration
        pap_log('Preview Handler: Registered public endpoint /affiliate-preview/');
    }

    /**
     * Handle preview request via template redirect (v1.4.5 - With 30s cache)
     */
    public static function handle_preview_request() {
        // Check if this is a preview request
        if (!get_query_var('affiliate_preview')) {
            return;
        }

        // Prevent browser caching
        nocache_headers();

        // Cache key for preview HTML
        $cache_key = 'affiliate_preview_html_v145';

        // Try to get cached version (v1.4.5)
        $cached_html = get_transient($cache_key);

        if ($cached_html !== false) {
            // Serve cached version
            pap_log('Preview Handler: Serving cached preview');
            echo $cached_html;
            exit;
        }

        // Get settings from database
        $settings = PAP_Template_Builder::get_template_settings();

        // Log preview rendering
        pap_log('Preview Handler: Generating fresh preview (cached for 30s)');

        // Start output buffering
        ob_start();

        // Include preview template (pure HTML)
        if (file_exists(PAP_DIR . 'admin/preview-template.php')) {
            include PAP_DIR . 'admin/preview-template.php';
        } else {
            echo '<!DOCTYPE html><html><body><p style="color:red;">Erro: Template de preview não encontrado.</p></body></html>';
        }

        // Get buffered output
        $output = ob_get_clean();

        // Cache for 30 seconds (v1.4.5)
        set_transient($cache_key, $output, 30);

        // Output the result
        echo $output;

        exit; // Exit to prevent WordPress from loading
    }

    /**
     * Clear preview cache (v1.4.5)
     *
     * Called when settings are saved
     */
    public static function clear_preview_cache() {
        delete_transient('affiliate_preview_html_v145');
        pap_log('Preview Handler: Cache cleared');
    }

    /**
     * Get preview URL (v1.4.4)
     *
     * @return string Public preview URL
     */
    public static function get_preview_url() {
        return home_url('/affiliate-preview/');
    }
}

// Initialize the handler
PAP_Preview_Handler::init();
