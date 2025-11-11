<?php
/**
 * Afiliados Pro - Preview Handler
 *
 * Handles AJAX preview rendering for Template Builder
 *
 * @package AfiliadorsPro
 * @version 1.4.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Affiliate_Preview_Handler
 *
 * Renders preview template via AJAX for live preview functionality
 */
class Affiliate_Preview_Handler {

    /**
     * Initialize preview handler
     */
    public static function init() {
        // Register AJAX actions for both logged-in users
        add_action('wp_ajax_affiliate_preview_template', [__CLASS__, 'render_preview']);

        // Log initialization if debug is enabled
        affiliate_pro_log('Preview Handler: Initialized');
    }

    /**
     * Render preview template (v1.4.1 - Fixed disappearing preview)
     *
     * Handles AJAX request and outputs preview HTML
     */
    public static function render_preview() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para visualizar esta página.', 'afiliados-pro'), 403);
        }

        // Prevent caching to ensure fresh preview content (v1.4.1)
        nocache_headers();

        // Get settings from query params (for real-time preview)
        // If not provided via query params, fall back to saved settings
        $settings = self::get_preview_settings();

        // Log preview rendering
        affiliate_pro_log('Preview Handler: Rendering preview with settings: ' . json_encode($settings));

        // Include preview template
        if (file_exists(AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php')) {
            include AFFILIATE_PRO_PLUGIN_DIR . 'admin/preview-template.php';
        } else {
            echo '<p style="color:red;">Erro: Template de preview não encontrado.</p>';
        }

        exit; // Exit cleanly without wp_die() to prevent redirection (v1.4.1)
    }

    /**
     * Get preview settings from query params or database
     *
     * @return array Preview settings
     */
    private static function get_preview_settings() {
        // Get saved settings as base
        $saved_settings = Affiliate_Template_Builder::get_template_settings();

        // Override with query params if present (for live preview)
        $preview_settings = $saved_settings;

        // Color settings
        if (isset($_GET['primary_color'])) {
            $preview_settings['primary_color'] = sanitize_hex_color($_GET['primary_color']);
        }
        if (isset($_GET['button_color'])) {
            $preview_settings['button_color'] = sanitize_hex_color($_GET['button_color']);
        }
        if (isset($_GET['gradient_color'])) { // v1.4.1
            $preview_settings['gradient_color'] = sanitize_hex_color($_GET['gradient_color']);
        }

        // Style settings
        if (isset($_GET['card_style'])) {
            $allowed_styles = ['modern', 'classic', 'minimal'];
            if (in_array($_GET['card_style'], $allowed_styles)) {
                $preview_settings['card_style'] = sanitize_text_field($_GET['card_style']);
            }
        }

        if (isset($_GET['button_style'])) {
            $allowed_button_styles = ['filled', 'outline', 'gradient'];
            if (in_array($_GET['button_style'], $allowed_button_styles)) {
                $preview_settings['button_style'] = sanitize_text_field($_GET['button_style']);
            }
        }

        // Border radius
        if (isset($_GET['border_radius'])) {
            $allowed_radius = ['none', 'small', 'medium', 'large'];
            if (in_array($_GET['border_radius'], $allowed_radius)) {
                $preview_settings['border_radius'] = sanitize_text_field($_GET['border_radius']);
            }
        }

        // Shadow settings (v1.4.0)
        if (isset($_GET['shadow'])) {
            $preview_settings['shadow'] = (bool) $_GET['shadow'];
        }
        if (isset($_GET['shadow_card'])) {
            $preview_settings['shadow_card'] = (bool) $_GET['shadow_card'];
        }
        if (isset($_GET['shadow_button'])) {
            $preview_settings['shadow_button'] = (bool) $_GET['shadow_button'];
        }

        // Layout settings
        if (isset($_GET['layout_default'])) {
            $allowed_layouts = ['grid', 'list'];
            if (in_array($_GET['layout_default'], $allowed_layouts)) {
                $preview_settings['layout_default'] = sanitize_text_field($_GET['layout_default']);
            }
        }

        if (isset($_GET['columns'])) {
            $columns = absint($_GET['columns']);
            $preview_settings['columns'] = max(2, min(4, $columns));
        }

        // Force CSS setting
        if (isset($_GET['force_css'])) {
            $preview_settings['force_css'] = (bool) $_GET['force_css'];
        }

        return $preview_settings;
    }
}

// Initialize the handler
Affiliate_Preview_Handler::init();
