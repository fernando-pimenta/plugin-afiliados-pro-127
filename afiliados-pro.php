<?php
/**
 * Plugin Name: Plugin Afiliados Pro
 * Plugin URI: https://fernandopimenta.blog.br
 * Description: Gerencie e exiba produtos afiliados com importação CSV, shortcodes personalizáveis e painel visual.
 * Version: 1.5.7
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

// Definir constantes do plugin
define('AFFILIATE_PRO_VERSION', '1.5.7');
define('AFFILIATE_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AFFILIATE_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AFFILIATE_PRO_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Modo debug (descomente a linha abaixo para ativar logs detalhados)
// define('AFFILIATE_PRO_DEBUG', true);

/**
 * Função helper para logs condicionais
 *
 * @param string $message Mensagem para log
 */
function affiliate_pro_log($message) {
    if (defined('AFFILIATE_PRO_DEBUG') && AFFILIATE_PRO_DEBUG) {
        error_log('Affiliate Pro: ' . $message);
    }
}

/**
 * Classe principal do Plugin Afiliados Pro
 *
 * @since 1.0
 */
class Affiliate_Pro_Plugin {

    /**
     * Instância única do plugin (Singleton)
     *
     * @var Affiliate_Pro_Plugin
     */
    private static $instance = null;

    /**
     * Obtém a instância única do plugin
     *
     * @return Affiliate_Pro_Plugin
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
     */
    private function load_dependencies() {
        // Core classes
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/class-affiliate-products.php';
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/class-affiliate-settings.php';
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/class-affiliate-template-builder.php';
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/class-affiliate-preview-handler.php'; // v1.4.0
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/class-affiliate-tracker.php'; // v1.4.7
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/csv-import.php';
        require_once AFFILIATE_PRO_PLUGIN_DIR . 'includes/shortcodes.php';
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
            dirname(AFFILIATE_PRO_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Inicializa o plugin
     */
    public function init() {
        // IMPORTANTE: Registrar CPT e Taxonomia ANTES de tudo
        // Isso garante que estarão disponíveis quando admin_menu for chamado
        $products = Affiliate_Pro_Products::get_instance();
        $products->register_post_type();
        $products->register_taxonomy();

        // Agora inicializar outras classes
        Affiliate_Pro_Settings::get_instance();
        Affiliate_Template_Builder::get_instance();
        Affiliate_Pro_CSV_Import::get_instance();
        Affiliate_Pro_Shortcodes::get_instance();
        Affiliate_Pro_Tracker::get_instance(); // v1.4.7 - Click tracking
    }

    /**
     * Ativação do plugin
     */
    public function activate() {
        // Registrar post type e taxonomia
        Affiliate_Pro_Products::get_instance()->register_post_type();
        Affiliate_Pro_Products::get_instance()->register_taxonomy();

        // Criar opções padrão
        $default_settings = Affiliate_Pro_Settings::get_default_settings();
        if (!get_option('affiliate_pro_settings')) {
            add_option('affiliate_pro_settings', $default_settings);
        }

        // Register preview endpoint rules (v1.4.4)
        Affiliate_Preview_Handler::register_preview_endpoint();

        // Create click tracking table (v1.4.7)
        Affiliate_Pro_Tracker::create_table();

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
            if (has_shortcode($post->post_content, 'affiliate_product') ||
                has_shortcode($post->post_content, 'affiliate_products')) {
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
                AFFILIATE_PRO_PLUGIN_URL . 'public/affiliate-pro.css',
                array(),
                AFFILIATE_PRO_VERSION
            );

            // Adicionar CSS dinâmico baseado nas configurações
            $dynamic_css = Affiliate_Pro_Settings::get_dynamic_css();
            wp_add_inline_style('affiliate-pro-style', $dynamic_css);

            // JavaScript (se necessário)
            wp_enqueue_script(
                'affiliate-pro-script',
                AFFILIATE_PRO_PLUGIN_URL . 'public/affiliate-pro.js',
                array('jquery'),
                AFFILIATE_PRO_VERSION,
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
            AFFILIATE_PRO_PLUGIN_URL . 'admin/admin-style.css',
            array('wp-color-picker'),
            AFFILIATE_PRO_VERSION
        );

        // JavaScript do admin
        wp_enqueue_script(
            'affiliate-pro-admin-script',
            AFFILIATE_PRO_PLUGIN_URL . 'admin/admin-script.js',
            array('jquery', 'wp-color-picker'),
            AFFILIATE_PRO_VERSION,
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
 * Inicializa o plugin
 *
 * @return Affiliate_Pro_Plugin
 */
function affiliate_pro() {
    return Affiliate_Pro_Plugin::get_instance();
}

// Inicializar o plugin
affiliate_pro();
