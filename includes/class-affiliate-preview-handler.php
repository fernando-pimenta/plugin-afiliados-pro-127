<?php
/**
 * Afiliados Pro - Preview Handler
 *
 * Handles preview rendering via public endpoint
 *
 * @package AfiliadorsPro
 * @version 1.4.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Affiliate_Preview_Handler
 *
 * Renders preview template via public endpoint for completely isolated preview
 */
class Affiliate_Preview_Handler {

    /**
     * Initialize preview handler (v1.4.4 - Public endpoint approach)
     */
    public static function init() {
        // Register public endpoint for preview (v1.4.4)
        add_action('init', [__CLASS__, 'register_preview_endpoint']);

        // Handle template redirect for preview
        add_action('template_redirect', [__CLASS__, 'handle_preview_request']);

        // Log initialization if debug is enabled
        affiliate_pro_log('Preview Handler: Initialized (v1.4.4 - Public Endpoint)');
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
        affiliate_pro_log('Preview Handler: Registered public endpoint /affiliate-preview/');
    }

    /**
     * Handle preview request via template redirect (v1.4.4)
     */
    public static function handle_preview_request() {
        // Check if this is a preview request
        if (!get_query_var('affiliate_preview')) {
            return;
        }

        // Prevent caching
        nocache_headers();

        // Get settings from database
        $settings = Affiliate_Template_Builder::get_template_settings();

        // Log preview rendering
        affiliate_pro_log('Preview Handler: Rendering public preview');

        // Include preview template (pure HTML)
        if (file_exists(AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php')) {
            include AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php';
        } else {
            echo '<!DOCTYPE html><html><body><p style="color:red;">Erro: Template de preview não encontrado.</p></body></html>';
        }

        exit; // Exit to prevent WordPress from loading
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
Affiliate_Preview_Handler::init();
