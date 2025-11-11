<?php
/**
 * Afiliados Pro - Rastreamento de Cliques
 *
 * @package Affiliate_Pro
 * @version 1.4.7
 */

if (!defined('ABSPATH')) {
    exit;
}

class Affiliate_Pro_Tracker {

    /**
     * Instância única (Singleton)
     *
     * @var Affiliate_Pro_Tracker
     */
    private static $instance = null;

    /**
     * Obtém a instância única
     *
     * @return Affiliate_Pro_Tracker
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
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Schedule cleanup cron
        add_action('affiliate_tracker_cleanup', array($this, 'cleanup_old_clicks'));
        if (!wp_next_scheduled('affiliate_tracker_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'affiliate_tracker_cleanup');
        }
    }

    /**
     * Cria tabela leve para registrar cliques (chamado na ativação do plugin)
     */
    public static function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'affiliate_clicks';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id VARCHAR(100) NOT NULL,
            source VARCHAR(100) DEFAULT 'button',
            clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product_id (product_id),
            INDEX idx_clicked_at (clicked_at)
        ) $charset;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        affiliate_pro_log('Affiliate Tracker: Table created successfully');
    }

    /**
     * Enfileira script de rastreamento no front-end
     */
    public function enqueue_scripts() {
        // Only load on pages with affiliate content
        if (!is_singular() && !is_archive() && !is_home()) {
            return;
        }

        wp_enqueue_script(
            'affiliate-tracker',
            AFFILIATE_PRO_PLUGIN_URL . 'assets/js/affiliate-tracker.js',
            array('jquery'),
            AFFILIATE_PRO_VERSION,
            true
        );

        wp_localize_script('affiliate-tracker', 'affiliateTracker', array(
            'restUrl' => esc_url_raw(rest_url('affiliate-pro/v1/track')),
            'nonce'   => wp_create_nonce('wp_rest')
        ));

        affiliate_pro_log('Affiliate Tracker: Scripts enqueued');
    }

    /**
     * Registra endpoint REST para gravação de cliques
     */
    public function register_rest_routes() {
        register_rest_route('affiliate-pro/v1', '/track', array(
            'methods'  => 'POST',
            'callback' => array($this, 'record_click'),
            'permission_callback' => '__return_true',
            'args' => array(
                'product_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'source' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'button',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        affiliate_pro_log('Affiliate Tracker: REST routes registered');
    }

    /**
     * Callback: grava clique na tabela
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function record_click($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'affiliate_clicks';

        $data = array(
            'product_id' => sanitize_text_field($request->get_param('product_id')),
            'source'     => sanitize_text_field($request->get_param('source')),
        );

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            affiliate_pro_log('Affiliate Tracker: Failed to record click - ' . $wpdb->last_error);
            return rest_ensure_response(array(
                'success' => false,
                'message' => 'Failed to record click'
            ));
        }

        affiliate_pro_log(sprintf(
            'Affiliate Tracker: Click recorded - Product: %s, Source: %s',
            $data['product_id'],
            $data['source']
        ));

        return rest_ensure_response(array(
            'success' => true,
            'id' => $wpdb->insert_id
        ));
    }

    /**
     * Limpeza automática de cliques antigos (90 dias)
     */
    public function cleanup_old_clicks() {
        global $wpdb;
        $table = $wpdb->prefix . 'affiliate_clicks';

        $deleted = $wpdb->query(
            "DELETE FROM $table WHERE clicked_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );

        if ($deleted !== false) {
            affiliate_pro_log(sprintf('Affiliate Tracker: Cleaned up %d old clicks', $deleted));
        }
    }

    /**
     * Obtém estatísticas de cliques por produto
     *
     * @param string $product_id ID do produto
     * @param int $days Número de dias para análise
     * @return array
     */
    public static function get_product_stats($product_id, $days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'affiliate_clicks';

        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT
                source,
                COUNT(*) as clicks,
                DATE(clicked_at) as click_date
            FROM $table
            WHERE product_id = %s
            AND clicked_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY source, DATE(clicked_at)
            ORDER BY clicked_at DESC",
            $product_id,
            $days
        ));

        return $stats;
    }

    /**
     * Obtém total de cliques por produto
     *
     * @param string $product_id ID do produto
     * @return int
     */
    public static function get_total_clicks($product_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'affiliate_clicks';

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE product_id = %s",
            $product_id
        ));

        return (int) $total;
    }
}
