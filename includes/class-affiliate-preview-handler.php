<?php
/**
 * Afiliados Pro - Preview Handler
 *
 * Handles preview rendering for Template Builder using pure HTML approach
 *
 * @package AfiliadorsPro
 * @version 1.4.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Affiliate_Preview_Handler
 *
 * Renders preview template via pure HTML without WP admin dependencies
 */
class Affiliate_Preview_Handler {

    /**
     * Initialize preview handler (v1.4.3 - Pure HTML approach)
     */
    public static function init() {
        // Register hidden admin page for preview (v1.4.3)
        add_action('admin_menu', [__CLASS__, 'register_preview_page']);

        // Log initialization if debug is enabled
        affiliate_pro_log('Preview Handler: Initialized (v1.4.3 - Pure HTML)');
    }

    /**
     * Register hidden admin page for preview (v1.4.3)
     */
    public static function register_preview_page() {
        add_submenu_page(
            null, // No parent menu (hidden page)
            __('Pré-visualização Afiliados Pro', 'afiliados-pro'),
            '',
            'manage_options',
            'affiliate-preview',
            [__CLASS__, 'render_preview_page']
        );
    }

    /**
     * Render preview page (v1.4.3 - Pure HTML without WP admin header)
     *
     * This outputs pure HTML without WordPress admin dependencies
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
        affiliate_pro_log('Preview Handler: Rendering pure HTML preview (v1.4.3)');

        // Output pure HTML structure (v1.4.3)
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Pré-visualização - Afiliados Pro</title>';
        echo '<style>
            body {
                margin: 0;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background: #f7f7f7;
            }
            .affiliate-container {
                max-width: 900px;
                margin: 0 auto;
            }
        </style>';
        echo '</head><body>';
        echo '<div class="affiliate-container">';

        // Include preview template
        if (file_exists(AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php')) {
            include AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php';
        } else {
            echo '<p style="color:red;">Erro: Template de preview não encontrado.</p>';
        }

        echo '</div></body></html>';

        exit; // Exit cleanly to prevent any WordPress output
    }
}

// Initialize the handler
Affiliate_Preview_Handler::init();
