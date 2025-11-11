<?php
/**
 * Afiliados Pro - Preview Handler
 *
 * Handles preview rendering for Template Builder using admin page approach
 *
 * @package AfiliadorsPro
 * @version 1.4.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Affiliate_Preview_Handler
 *
 * Renders preview template via admin page for stable live preview functionality
 */
class Affiliate_Preview_Handler {

    /**
     * Initialize preview handler (v1.4.2 - Admin page approach)
     */
    public static function init() {
        // Register hidden admin page for preview (v1.4.2)
        add_action('admin_menu', [__CLASS__, 'register_preview_page']);

        // Log initialization if debug is enabled
        affiliate_pro_log('Preview Handler: Initialized (v1.4.2 - Admin Page)');
    }

    /**
     * Register hidden admin page for preview (v1.4.2)
     */
    public static function register_preview_page() {
        add_submenu_page(
            null, // No parent menu (hidden page)
            __('Affiliate Preview', 'afiliados-pro'),
            __('Affiliate Preview', 'afiliados-pro'),
            'manage_options',
            'affiliate-preview',
            [__CLASS__, 'render_preview_page']
        );
    }

    /**
     * Render preview page (v1.4.2 - Stable admin page approach)
     *
     * This is a permanent admin page that doesn't expire or disappear
     */
    public static function render_preview_page() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para visualizar esta página.', 'afiliados-pro'), 403);
        }

        // Prevent caching to ensure fresh preview content
        nocache_headers();

        // Get settings from database
        $settings = Affiliate_Template_Builder::get_template_settings();

        // Log preview rendering
        affiliate_pro_log('Preview Handler: Rendering preview page');

        // Include preview template
        if (file_exists(AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php')) {
            include AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php';
        } else {
            echo '<p style="color:red;">Erro: Template de preview não encontrado.</p>';
        }

        exit; // Exit cleanly to prevent admin footer
    }
}

// Initialize the handler
Affiliate_Preview_Handler::init();
