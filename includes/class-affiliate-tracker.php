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

class PAP_Tracker {

    /**
     * Instância única (Singleton)
     *
     * @var PAP_Tracker
     */
    private static $instance = null;

    /**
     * Obtém a instância única
     *
     * @return PAP_Tracker
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
            source_page VARCHAR(255) DEFAULT NULL,
            clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product_id (product_id),
            INDEX idx_clicked_at (clicked_at)
        ) $charset;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Migração: adicionar coluna source_page se não existir
        self::upgrade_table();

        affiliate_pro_log('Affiliate Tracker: Table created successfully');
    }

    /**
     * Faz upgrade da tabela para versões antigas (adiciona source_page se necessário)
     */
    public static function upgrade_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'affiliate_clicks';

        // Verificar se a coluna source_page existe
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SHOW COLUMNS FROM `{$table}` LIKE %s",
            'source_page'
        ));

        // Se não existir, adicionar
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN source_page VARCHAR(255) DEFAULT NULL AFTER source");
            affiliate_pro_log('Affiliate Tracker: Column source_page added to table');
        }
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
            PAP_URL . 'assets/js/affiliate-tracker.js',
            array('jquery'),
            PAP_VERSION,
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
            'permission_callback' => array($this, 'verify_track_permission'),
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
                'source_page' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        affiliate_pro_log('Affiliate Tracker: REST routes registered');
    }

    /**
     * Verifica permissão para rastrear cliques
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function verify_track_permission($request) {
        // Verificar nonce do WordPress REST API
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            affiliate_pro_log('Affiliate Tracker: Nonce verification failed');
            return false;
        }

        // Verificar rate limiting básico (máximo 10 requisições por minuto por IP)
        $ip = $this->get_client_ip();
        $transient_key = 'affiliate_track_rate_' . md5($ip);
        $request_count = get_transient($transient_key);

        if ($request_count !== false && $request_count >= 10) {
            affiliate_pro_log('Affiliate Tracker: Rate limit exceeded for IP ' . $ip);
            return false;
        }

        // Incrementar contador
        if ($request_count === false) {
            set_transient($transient_key, 1, 60); // 60 segundos
        } else {
            set_transient($transient_key, $request_count + 1, 60);
        }

        return true;
    }

    /**
     * Obtém o IP do cliente de forma segura
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Pegar apenas o primeiro IP da lista (o cliente real)
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validar e sanitizar o IP
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        return $ip ? $ip : '0.0.0.0';
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

        $source_page = $request->get_param('source_page');

        $data = array(
            'product_id' => sanitize_text_field($request->get_param('product_id')),
            'source'     => sanitize_text_field($request->get_param('source')),
        );

        // Adicionar source_page se fornecido
        if (!empty($source_page)) {
            $data['source_page'] = sanitize_text_field($source_page);
        }

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            affiliate_pro_log('Affiliate Tracker: Failed to record click - ' . $wpdb->last_error);
            return rest_ensure_response(array(
                'success' => false,
                'message' => 'Failed to record click'
            ));
        }

        affiliate_pro_log(sprintf(
            'Affiliate Tracker: Click recorded - Product: %s, Source: %s, Page: %s',
            $data['product_id'],
            $data['source'],
            isset($data['source_page']) ? $data['source_page'] : 'N/A'
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
