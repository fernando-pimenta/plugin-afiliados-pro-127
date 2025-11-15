<?php
/**
 * Classe responsável pelas configurações do plugin
 * v1.7.1: Refatoração gradual - PAP_Settings é agora a classe principal
 * v1.9.4: Geração de CSS delegada para PAP_Template_CSS
 * v1.9.5: Polimento final e validação
 *
 * @package PAP
 * @since 1.2
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal de Settings com prefixo padronizado PAP
 * v1.7.1: Promovida de espelho para classe principal
 *
 * @package PAP
 * @since 1.7.1
 */
class PAP_Settings {

    /**
     * Instância única (Singleton)
     *
     * @var PAP_Settings
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
     * @return PAP_Settings
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
     * v1.9.4: Delegated to PAP_Template_CSS class
     *
     * @return string
     */
    public static function get_dynamic_css() {
        $settings = self::get_settings();
        return PAP_Template_CSS::generate($settings);
    }
}
