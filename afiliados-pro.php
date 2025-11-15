<?php
/**
 * Plugin Name: PAP – Plugin Afiliados Pro
 * Plugin URI: https://fernandopimenta.blog.br
 * Description: Sistema PAP de exibição de produtos afiliados com Template Builder e Presets.
 * Version: 1.9.3
 * Author: Fernando Pimenta
 * Author URI: https://fernandopimenta.blog.br
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: afiliados-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Tested up to: 6.7
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Constantes do plugin
define('PAP_VERSION', '1.9.3');
define('PAP_DIR', plugin_dir_path(__FILE__));
define('PAP_URL', plugin_dir_url(__FILE__));
define('PAP_BASENAME', plugin_basename(__FILE__));

// Modo debug (descomente a linha abaixo para ativar logs detalhados)
// define('PAP_DEBUG', true);

/**
 * Função helper para logs condicionais
 *
 * @param string $message Mensagem para log
 * @since 1.7.3
 */
function pap_log($message) {
    if (defined('PAP_DEBUG') && PAP_DEBUG) {
        error_log('[PAP] ' . $message);
    }
}

/**
 * Classe principal do PAP - Plugin Afiliados Pro
 *
 * @package PAP
 * @since 1.0
 */
class PAP_Plugin {

    /**
     * Instância única do plugin (Singleton)
     *
     * @var PAP_Plugin
     */
    private static $instance = null;

    /**
     * Obtém a instância única do plugin
     *
     * @return PAP_Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor privado (Singleton)
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Carrega as dependências do plugin
     * v1.7.2: Arquivos de classes renomeados para padrão class-pap-*
     */
    private function load_dependencies() {
        // Core classes (v1.7.2: renomeadas para prefixo pap_)
        require_once PAP_DIR . 'includes/class-pap-products.php';
        require_once PAP_DIR . 'includes/class-pap-settings.php';
        require_once PAP_DIR . 'includes/class-pap-template-builder.php';
        require_once PAP_DIR . 'includes/class-affiliate-preview-handler.php'; // v1.4.0
        require_once PAP_DIR . 'includes/class-affiliate-tracker.php'; // v1.4.7
        require_once PAP_DIR . 'includes/csv-import.php';
        require_once PAP_DIR . 'includes/class-pap-shortcodes.php'; // v1.7.2: renomeado de shortcodes.php
    }

    /**
     * Inicializa os hooks do WordPress
     */
    private function init_hooks() {
        // Inicialização do plugin
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));

        // Hooks de ativação e desativação
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Assets (CSS/JS)
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Carrega o domínio de tradução do plugin
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'afiliados-pro',
            false,
            dirname(PAP_BASENAME) . '/languages'
        );
    }

    /**
     * Inicializa o plugin
     */
    public function init() {
        // IMPORTANTE: Registrar CPT e Taxonomia ANTES de tudo
        // Isso garante que estarão disponíveis quando admin_menu for chamado
        $products = PAP_Products::get_instance();
        $products->register_post_type();
        $products->register_taxonomy();

        // Agora inicializar outras classes
        PAP_Settings::get_instance();
        PAP_Template_Builder::get_instance();
        PAP_CSV_Import::get_instance();
        PAP_Shortcodes::get_instance();
        PAP_Tracker::get_instance(); // v1.4.7 - Click tracking
    }

    /**
     * Ativação do plugin
     */
    public function activate() {
        // Registrar post type e taxonomia
        PAP_Products::get_instance()->register_post_type();
        PAP_Products::get_instance()->register_taxonomy();

        // Criar opções padrão
        $default_settings = PAP_Settings::get_default_settings();
        if (!get_option('affiliate_pro_settings')) {
            add_option('affiliate_pro_settings', $default_settings);
        }

        // Register preview endpoint rules (v1.4.4)
        PAP_Preview_Handler::register_preview_endpoint();

        // Create click tracking table (v1.4.7)
        PAP_Tracker::create_table();

        // Flush rewrite rules to activate preview endpoint
        flush_rewrite_rules();
    }

    /**
     * Desativação do plugin
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Carrega CSS e JS do frontend
     */
    public function enqueue_frontend_assets() {
        global $post;

        // Verificar se a página atual usa os shortcodes do plugin
        $load_assets = false;

        if (is_a($post, 'WP_Post')) {
            if (has_shortcode($post->post_content, 'pap_product') ||
                has_shortcode($post->post_content, 'pap_products') ||
                has_shortcode($post->post_content, 'pap_preset')) {
                $load_assets = true;
            }
        }

        // Verificar se é single do nosso CPT
        if (is_singular('affiliate_product')) {
            $load_assets = true;
        }

        // Carregar assets apenas quando necessário
        if ($load_assets) {
            // CSS principal
            wp_enqueue_style(
                'affiliate-pro-style',
                PAP_URL . 'public/affiliate-pro.css',
                array(),
                PAP_VERSION
            );

            // Adicionar CSS dinâmico baseado nas configurações
            $dynamic_css = PAP_Settings::get_dynamic_css();
            wp_add_inline_style('affiliate-pro-style', $dynamic_css);

            // JavaScript (se necessário)
            wp_enqueue_script(
                'affiliate-pro-script',
                PAP_URL . 'public/affiliate-pro.js',
                array('jquery'),
                PAP_VERSION,
                true
            );
        }
    }

    /**
     * Carrega CSS e JS do admin
     */
    public function enqueue_admin_assets($hook) {
        // Carregar apenas nas páginas do plugin
        if (strpos($hook, 'affiliate') === false && get_post_type() !== 'affiliate_product') {
            return;
        }

        // CSS do admin
        wp_enqueue_style(
            'affiliate-pro-admin-style',
            PAP_URL . 'admin/admin-style.css',
            array('wp-color-picker'),
            PAP_VERSION
        );

        // JavaScript do admin
        wp_enqueue_script(
            'affiliate-pro-admin-script',
            PAP_URL . 'admin/admin-script.js',
            array('jquery', 'wp-color-picker'),
            PAP_VERSION,
            true
        );

        // Localizar scripts
        wp_localize_script('affiliate-pro-admin-script', 'affiliateProAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('affiliate_pro_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Tem certeza que deseja excluir este produto?', 'afiliados-pro'),
                'confirm_duplicate' => __('Deseja duplicar este produto?', 'afiliados-pro'),
                'error_duplicate' => __('Erro ao duplicar produto', 'afiliados-pro'),
                'copied' => __('Copiado!', 'afiliados-pro'),
            )
        ));
    }
}

/**
 * Função de inicialização do plugin
 *
 * @return PAP_Plugin
 * @since 1.7.4
 */
function pap() {
    return PAP_Plugin::get_instance();
}

// Inicializar o plugin
PAP_Plugin::get_instance();
