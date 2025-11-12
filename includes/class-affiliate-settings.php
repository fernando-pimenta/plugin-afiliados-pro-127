<?php
/**
 * Classe responsável pelas configurações do plugin
 *
 * @package Affiliate_Pro
 * @since 1.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class Affiliate_Pro_Settings {

    /**
     * Instância única (Singleton)
     *
     * @var Affiliate_Pro_Settings
     */
    private static $instance = null;

    /**
     * Nome da opção no banco de dados
     *
     * @var string
     */
    private $option_name = 'affiliate_pro_settings';

    /**
     * Obtém a instância única
     *
     * @return Affiliate_Pro_Settings
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_affiliate_pro_reset_settings', array($this, 'reset_settings'));
    }

    /**
     * Registra as configurações
     */
    public function register_settings() {
        register_setting(
            'affiliate_pro_settings_group',
            $this->option_name,
            array($this, 'sanitize_settings')
        );
    }

    /**
     * Sanitiza as configurações antes de salvar
     *
     * @param array $input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // Seção 1 - Identidade Visual dos Cards
        $sanitized['primary_color'] = isset($input['primary_color']) ? sanitize_hex_color($input['primary_color']) : '#283593';
        $sanitized['secondary_color'] = isset($input['secondary_color']) ? sanitize_hex_color($input['secondary_color']) : '#3949ab';
        $sanitized['accent_color'] = isset($input['accent_color']) ? sanitize_hex_color($input['accent_color']) : '#ffa70a';
        $sanitized['card_bg_color'] = isset($input['card_bg_color']) ? sanitize_hex_color($input['card_bg_color']) : '#ffffff';
        $sanitized['text_color'] = isset($input['text_color']) ? sanitize_hex_color($input['text_color']) : '#1a1a1a';
        $sanitized['price_color'] = isset($input['price_color']) ? sanitize_hex_color($input['price_color']) : '#111111';
        $sanitized['card_image_background'] = isset($input['card_image_background']) ? sanitize_hex_color($input['card_image_background']) : '#f9f9f9';
        $sanitized['card_border_radius'] = isset($input['card_border_radius']) ? absint($input['card_border_radius']) : 12;
        $sanitized['card_shadow'] = isset($input['card_shadow']) ? (bool) $input['card_shadow'] : true;
        $sanitized['shadow_button'] = isset($input['shadow_button']) ? (bool) $input['shadow_button'] : false;
        $sanitized['force_css'] = isset($input['force_css']) ? (bool) $input['force_css'] : false;

        // Seção 2 - Botão de Ação
        $sanitized['button_text'] = isset($input['button_text']) ? sanitize_text_field($input['button_text']) : __('Ver oferta', 'afiliados-pro');
        $sanitized['button_style'] = isset($input['button_style']) && in_array($input['button_style'], array('gradient', 'flat', 'outline')) ? $input['button_style'] : 'gradient';
        $sanitized['button_color_start'] = isset($input['button_color_start']) ? sanitize_hex_color($input['button_color_start']) : '#6a82fb';
        $sanitized['button_color_end'] = isset($input['button_color_end']) ? sanitize_hex_color($input['button_color_end']) : '#fc5c7d';
        $sanitized['button_text_color'] = isset($input['button_text_color']) ? sanitize_hex_color($input['button_text_color']) : '#ffffff';
        $sanitized['button_text_disabled'] = isset($input['button_text_disabled']) ? sanitize_text_field($input['button_text_disabled']) : __('Indisponível', 'afiliados-pro');

        // Seção 3 - Layout da Grade
        $sanitized['default_layout'] = isset($input['default_layout']) && in_array($input['default_layout'], array('grid', 'list')) ? $input['default_layout'] : 'grid';
        $sanitized['default_columns'] = isset($input['default_columns']) ? absint($input['default_columns']) : 3;
        $sanitized['card_gap'] = isset($input['card_gap']) ? absint($input['card_gap']) : 20;

        // Seção 4 - Exibição de Preços
        $sanitized['price_format'] = isset($input['price_format']) ? sanitize_text_field($input['price_format']) : 'R$ {valor}';
        $sanitized['price_placeholder'] = isset($input['price_placeholder']) ? sanitize_text_field($input['price_placeholder']) : __('Consulte o preço', 'afiliados-pro');

        // Seção 5 - Outros Ajustes
        $sanitized['title_clickable'] = isset($input['title_clickable']) ? (bool) $input['title_clickable'] : true;
        $sanitized['open_in_new_tab'] = isset($input['open_in_new_tab']) ? (bool) $input['open_in_new_tab'] : true;
        $sanitized['show_store_badge'] = isset($input['show_store_badge']) ? (bool) $input['show_store_badge'] : true;
        $sanitized['show_price'] = isset($input['show_price']) ? (bool) $input['show_price'] : true;
        $sanitized['custom_css'] = isset($input['custom_css']) ? wp_strip_all_tags($input['custom_css']) : '';

        return $sanitized;
    }

    /**
     * Retorna as configurações padrão
     *
     * @return array
     */
    public static function get_default_settings() {
        return array(
            // Seção 1 - Identidade Visual dos Cards
            'primary_color' => '#283593',
            'secondary_color' => '#3949ab',
            'accent_color' => '#ffa70a',
            'card_bg_color' => '#ffffff',
            'text_color' => '#1a1a1a',
            'price_color' => '#111111',
            'card_image_background' => '#f9f9f9',
            'card_border_radius' => 12,
            'card_shadow' => true,
            'shadow_button' => false,
            'force_css' => false,

            // Seção 2 - Botão de Ação
            'button_text' => __('Ver oferta', 'afiliados-pro'),
            'button_style' => 'gradient',
            'button_color_start' => '#6a82fb',
            'button_color_end' => '#fc5c7d',
            'button_text_color' => '#ffffff',
            'button_text_disabled' => __('Indisponível', 'afiliados-pro'),

            // Seção 3 - Layout da Grade
            'default_layout' => 'grid',
            'default_columns' => 3,
            'card_gap' => 20,

            // Seção 4 - Exibição de Preços
            'price_format' => 'R$ {valor}',
            'price_placeholder' => __('Consulte o preço', 'afiliados-pro'),

            // Seção 5 - Outros Ajustes
            'title_clickable' => true,
            'open_in_new_tab' => true,
            'show_store_badge' => true,
            'show_price' => true,
            'custom_css' => ''
        );
    }

    /**
     * Obtém as configurações atuais
     *
     * @return array
     */
    public static function get_settings() {
        $settings = get_option('affiliate_pro_settings', array());
        $defaults = self::get_default_settings();

        // Mescla com os padrões
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Obtém um valor específico de configuração
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get_setting($key, $default = null) {
        $settings = self::get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Reseta as configurações para os valores padrão
     */
    public function reset_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
        }

        check_admin_referer('affiliate_pro_reset_settings');

        update_option($this->option_name, self::get_default_settings());

        wp_redirect(add_query_arg(
            array('page' => 'affiliate-settings', 'reset' => 'success'),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Gera CSS dinâmico baseado nas configurações
     *
     * @return string
     */
    public static function get_dynamic_css() {
        $settings = self::get_settings();

        // Variáveis CSS no :root para fácil customização (v1.6.5)
        $css = "
        /* Afiliados Pro - CSS Dinâmico v1.6.5 */

        :root {
            --affiliate-primary-color: {$settings['primary_color']};
            --affiliate-secondary-color: {$settings['secondary_color']};
            --affiliate-accent-color: {$settings['accent_color']};
            --affiliate-card-bg: {$settings['card_bg_color']};
            --affiliate-text-color: {$settings['text_color']};
            --affiliate-image-bg: {$settings['card_image_background']};
            --affiliate-card-radius: {$settings['card_border_radius']}px;
            --affiliate-card-gap: {$settings['card_gap']}px;
            --affiliate-button-start: {$settings['button_color_start']};
            --affiliate-button-end: {$settings['button_color_end']};
            --affiliate-button-text: {$settings['button_text_color']};
            --affiliate-price-color: {$settings['price_color']};
        }

        /* Grade de produtos com gap dinâmico */
        .affiliate-products-grid {
            gap: var(--affiliate-card-gap);
        }

        /* Cards com cores e bordas personalizadas */
        .affiliate-product-card {
            background: var(--affiliate-card-bg);
            border-radius: var(--affiliate-card-radius);
            color: var(--affiliate-text-color);
            margin: 12px;
            padding: 0;
        }

        .affiliate-product-card .product-content {
            padding: 16px;
        }
        ";

        // Sombra nos cards (condicional)
        if ($settings['card_shadow']) {
            $css .= "
        .affiliate-product-card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .affiliate-product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
            ";
        } else {
            $css .= "
        .affiliate-product-card {
            box-shadow: none;
        }

        .affiliate-product-card:hover {
            box-shadow: none;
        }
            ";
        }

        $css .= "
        /* Títulos com cor primária (v1.5.6) */
        .affiliate-product-card .product-title {
            color: var(--affiliate-primary-color);
        }

        .affiliate-product-card .product-title a {
            color: var(--affiliate-primary-color);
        }

        /* Texto auxiliar com cor secundária (v1.5.6) */
        .affiliate-product-card .product-excerpt,
        .affiliate-product-card .product-description {
            color: var(--affiliate-text-color);
        }

        /* Imagens com fundo controlado (v1.5.6) */
        .affiliate-product-card .product-image {
            height: 220px;
            background: var(--affiliate-image-bg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .affiliate-product-card .product-image img {
            max-height: 220px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        /* Badge da loja com cor de destaque (v1.5.6) */
        .affiliate-product-card .store-badge {
            background: var(--affiliate-accent-color);
            color: #fff;
        }

        /* Preço com cor personalizada (v1.6.3: aplica-se também ao texto alternativo Sem preco) */
        .affiliate-product-card .product-price {
            color: var(--affiliate-price-color) !important;
            font-weight: 600;
        }

        /* Botões base (v1.5.5) */
        .affiliate-product-card .product-button,
        .affiliate-product-card .affiliate-btn-flat,
        .affiliate-product-card .affiliate-btn-outline,
        .affiliate-product-card .affiliate-btn-gradient {
            width: auto;
            min-width: 120px;
            max-width: 90%;
            text-align: center;
            display: inline-block;
            transition: all 0.3s ease;
            padding: 10px 18px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 6px;
        }

        /* Estilo: Preenchido (Flat) - v1.5.6 */
        .affiliate-product-card .affiliate-btn-flat {
            background: var(--button-color-start, var(--affiliate-button-start));
            color: var(--button-text-color, var(--affiliate-button-text));
            border: 2px solid var(--button-color-start, var(--affiliate-button-start));";

        // v1.6.5: Sombra nos botões condicional
        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);";
        }

        $css .= "
        }

        .affiliate-product-card .affiliate-btn-flat:hover {
            opacity: 0.9;
            transform: translateY(-2px);";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);";
        }

        $css .= "
        }

        /* Estilo: Contorno (Outline) - v1.5.6 */
        .affiliate-product-card .affiliate-btn-outline {
            background: transparent;
            color: var(--button-color-start, var(--affiliate-button-start));
            border: 2px solid var(--button-color-start, var(--affiliate-button-start));
            box-shadow: none;
        }

        .affiliate-product-card .affiliate-btn-outline:hover {
            background: var(--button-color-start, var(--affiliate-button-start));
            color: var(--button-text-color, var(--affiliate-button-text));
            transform: translateY(-2px);";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);";
        }

        $css .= "
        }

        /* Estilo: Gradiente - v1.5.6 */
        .affiliate-product-card .affiliate-btn-gradient {
            background: linear-gradient(135deg, var(--button-color-start, var(--affiliate-button-start)) 0%, var(--button-color-end, var(--affiliate-button-end)) 100%);
            color: var(--button-text-color, var(--affiliate-button-text));
            border: none;";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);";
        }

        $css .= "
        }

        .affiliate-product-card .affiliate-btn-gradient:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);";
        }

        $css .= "
        }
        ";

        // Título clicável (condicional)
        if ($settings['title_clickable']) {
            $css .= "
        .affiliate-product-card .product-title a {
            cursor: pointer;
            transition: color 0.2s;
        }

        .affiliate-product-card .product-title a:hover {
            color: var(--affiliate-primary-color);
        }
            ";
        } else {
            $css .= "
        .affiliate-product-card .product-title a {
            pointer-events: none;
            cursor: default;
        }
            ";
        }

        // Badge da loja (condicional)
        if (!$settings['show_store_badge']) {
            $css .= "
        .affiliate-product-card .store-badge {
            display: none !important;
        }
            ";
        }

        // CSS customizado adicional
        if (!empty($settings['custom_css'])) {
            $css .= "\n/* CSS Customizado */\n" . $settings['custom_css'];
        }

        return $css;
    }
}
