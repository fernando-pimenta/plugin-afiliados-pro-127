<?php
/**
 * Classe responsável pelo Custom Post Type e Taxonomia
 * v1.7.1: Refatoração gradual - PAP_Products é agora a classe principal
 *
 * @package PAP
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal de Products com prefixo padronizado PAP
 * v1.7.1: Promovida de espelho para classe principal
 *
 * @package PAP
 * @since 1.7.1
 */
class PAP_Products {

    /**
     * Instância única (Singleton)
     *
     * @var PAP_Products
     */
    private static $instance = null;

    /**
     * Nome do Custom Post Type
     *
     * @var string
     */
    private $post_type = 'affiliate_product';

    /**
     * Nome da Taxonomia
     *
     * @var string
     */
    private $taxonomy = 'affiliate_category';

    /**
     * Obtém a instância única
     *
     * @return PAP_Products
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
        // Nota: register_post_type e register_taxonomy são chamados diretamente
        // no arquivo principal antes de admin_menu para garantir ordem correta
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('wp_ajax_duplicate_affiliate_product', array($this, 'ajax_duplicate_product'));
    }

    /**
     * Registra o Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name' => __('Produtos Afiliados', 'afiliados-pro'),
            'singular_name' => __('Produto Afiliado', 'afiliados-pro'),
            'menu_name' => __('Produtos Afiliados', 'afiliados-pro'),
            'add_new' => __('Adicionar Novo', 'afiliados-pro'),
            'add_new_item' => __('Adicionar Novo Produto', 'afiliados-pro'),
            'edit_item' => __('Editar Produto', 'afiliados-pro'),
            'new_item' => __('Novo Produto', 'afiliados-pro'),
            'view_item' => __('Ver Produto', 'afiliados-pro'),
            'search_items' => __('Buscar Produtos', 'afiliados-pro'),
            'not_found' => __('Nenhum produto encontrado', 'afiliados-pro'),
            'not_found_in_trash' => __('Nenhum produto na lixeira', 'afiliados-pro')
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // Controlamos o menu manualmente
            'query_var' => true,
            'rewrite' => array('slug' => 'produto-afiliado'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-cart'
        );

        register_post_type($this->post_type, $args);
    }

    /**
     * Registra a Taxonomia
     */
    public function register_taxonomy() {
        $labels = array(
            'name' => __('Categorias de Afiliados', 'afiliados-pro'),
            'singular_name' => __('Categoria de Afiliado', 'afiliados-pro'),
            'search_items' => __('Buscar Categorias', 'afiliados-pro'),
            'all_items' => __('Todas as Categorias', 'afiliados-pro'),
            'edit_item' => __('Editar Categoria', 'afiliados-pro'),
            'update_item' => __('Atualizar Categoria', 'afiliados-pro'),
            'add_new_item' => __('Adicionar Nova Categoria', 'afiliados-pro'),
            'new_item_name' => __('Nome da Nova Categoria', 'afiliados-pro'),
            'menu_name' => __('Categorias', 'afiliados-pro')
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'categoria-afiliado')
        );

        register_taxonomy($this->taxonomy, array($this->post_type), $args);
    }

    /**
     * Adiciona menu no painel admin
     */
    public function add_admin_menu() {
        // Menu principal
        add_menu_page(
            __('Afiliados', 'afiliados-pro'),
            __('Afiliados', 'afiliados-pro'),
            'manage_options',
            'affiliate-products',
            array($this, 'render_dashboard_page'),
            'dashicons-cart',
            6
        );

        // Submenu - Dashboard (renomeia o primeiro item)
        add_submenu_page(
            'affiliate-products',
            __('Dashboard', 'afiliados-pro'),
            __('Dashboard', 'afiliados-pro'),
            'manage_options',
            'affiliate-products',
            array($this, 'render_dashboard_page')
        );

        // Submenu - Adicionar Produto
        add_submenu_page(
            'affiliate-products',
            __('Adicionar Produto', 'afiliados-pro'),
            __('Adicionar Produto', 'afiliados-pro'),
            'manage_options',
            'post-new.php?post_type=' . $this->post_type
        );

        // Submenu - Gerenciar Produtos
        add_submenu_page(
            'affiliate-products',
            __('Gerenciar Produtos', 'afiliados-pro'),
            __('Gerenciar Produtos', 'afiliados-pro'),
            'manage_options',
            'affiliate-manage-products',
            array($this, 'render_manage_products_page')
        );

        // Submenu - Importar CSV
        add_submenu_page(
            'affiliate-products',
            __('Importar CSV', 'afiliados-pro'),
            __('Importar CSV', 'afiliados-pro'),
            'manage_options',
            'affiliate-import-csv',
            array($this, 'render_import_csv_page')
        );

        // Submenu - Categorias
        add_submenu_page(
            'affiliate-products',
            __('Categorias', 'afiliados-pro'),
            __('Categorias', 'afiliados-pro'),
            'manage_options',
            'edit-tags.php?taxonomy=' . $this->taxonomy . '&post_type=' . $this->post_type
        );

        // REMOVED (v1.4.5.1): Legacy menu - now handled by class-affiliate-template-builder.php
        // Submenu - Aparência e Configurações
        // add_submenu_page(
        //     'affiliate-products',
        //     __('Aparência e Configurações', 'afiliados-pro'),
        //     __('Aparência e Configurações', 'afiliados-pro'),
        //     'manage_options',
        //     'affiliate-settings',
        //     array($this, 'render_settings_page')
        // );
    }

    /**
     * Renderiza a página do Dashboard
     */
    public function render_dashboard_page() {
        $products_count_obj = wp_count_posts($this->post_type);
        $products_count = (is_object($products_count_obj) && isset($products_count_obj->publish)) ? $products_count_obj->publish : 0;

        $categories_count_result = wp_count_terms(array('taxonomy' => $this->taxonomy));
        $categories_count = (!is_wp_error($categories_count_result)) ? $categories_count_result : 0;
        ?>
        <div class="wrap affiliate-pro-dashboard">
            <h1><?php _e('Produtos Afiliados - Dashboard', 'afiliados-pro'); ?></h1>

            <div class="card-container" style="display: flex; gap: 20px; margin: 20px 0;">
                <div class="card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 200px;">
                    <h3><?php _e('Total de Produtos', 'afiliados-pro'); ?></h3>
                    <p style="font-size: 24px; color: #0073aa; margin: 0;"><?php echo esc_html($products_count); ?></p>
                </div>
                <div class="card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 200px;">
                    <h3><?php _e('Categorias', 'afiliados-pro'); ?></h3>
                    <p style="font-size: 24px; color: #0073aa; margin: 0;"><?php echo esc_html($categories_count); ?></p>
                </div>
            </div>

            <h2><?php _e('Ações Rápidas', 'afiliados-pro'); ?></h2>
            <p>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . $this->post_type)); ?>" class="button button-primary"><?php _e('Adicionar Novo Produto', 'afiliados-pro'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-import-csv')); ?>" class="button"><?php _e('Importar CSV', 'afiliados-pro'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manage-products')); ?>" class="button"><?php _e('Gerenciar Produtos', 'afiliados-pro'); ?></a>
            </p>

            <h2><?php _e('Shortcodes Disponíveis – Padrão PAP', 'afiliados-pro'); ?></h2>
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <div style="margin-bottom: 20px; background: #f0f6fc; padding: 15px; border-left: 4px solid #0073aa;">
                    <h3 style="margin-top: 0; color: #0073aa; font-size: 16px;"><?php _e('Preset Personalizado', 'afiliados-pro'); ?></h3>
                    <code style="display: block; padding: 10px; background: #fff; border-left: 3px solid #0073aa; margin: 8px 0;">[pap_preset id="1"]</code>
                    <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('[pap_preset id=&quot;1&quot;]'); alert('Shortcode copiado!');" style="margin-top: 5px;"><?php _e('Copiar', 'afiliados-pro'); ?></button>
                    <p style="color: #666; font-size: 13px; margin: 5px 0 0 0;"><?php _e('Exibe produtos com as configurações salvas no preset #1. Crie seus presets em Aparência e Configurações > Presets', 'afiliados-pro'); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #0073aa; font-size: 16px;"><?php _e('Produto Único', 'afiliados-pro'); ?></h3>
                    <code style="display: block; padding: 10px; background: #f5f5f5; border-left: 3px solid #0073aa; margin: 8px 0;">[pap_product id="123"]</code>
                    <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('[pap_product id=&quot;123&quot;]'); alert('Shortcode copiado!');" style="margin-top: 5px;"><?php _e('Copiar', 'afiliados-pro'); ?></button>
                    <p style="color: #666; font-size: 13px; margin: 5px 0 0 0;"><?php _e('Exibe um produto específico pelo seu ID', 'afiliados-pro'); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #0073aa; font-size: 16px;"><?php _e('Grade com Limite e Colunas', 'afiliados-pro'); ?></h3>
                    <code style="display: block; padding: 10px; background: #f5f5f5; border-left: 3px solid #0073aa; margin: 8px 0;">[pap_products limit="12" columns="4"]</code>
                    <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('[pap_products limit=&quot;12&quot; columns=&quot;4&quot;]'); alert('Shortcode copiado!');" style="margin-top: 5px;"><?php _e('Copiar', 'afiliados-pro'); ?></button>
                    <p style="color: #666; font-size: 13px; margin: 5px 0 0 0;"><?php _e('Exibe grade com 12 produtos em 4 colunas', 'afiliados-pro'); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #0073aa; font-size: 16px;"><?php _e('Categorias e Ordem Aleatória', 'afiliados-pro'); ?></h3>
                    <code style="display: block; padding: 10px; background: #f5f5f5; border-left: 3px solid #0073aa; margin: 8px 0;">[pap_products category="eletronicos,games" orderby="rand" limit="10"]</code>
                    <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('[pap_products category=&quot;eletronicos,games&quot; orderby=&quot;rand&quot; limit=&quot;10&quot;]'); alert('Shortcode copiado!');" style="margin-top: 5px;"><?php _e('Copiar', 'afiliados-pro'); ?></button>
                    <p style="color: #666; font-size: 13px; margin: 5px 0 0 0;"><?php _e('Exibe 10 produtos aleatórios das categorias "eletronicos" e "games"', 'afiliados-pro'); ?></p>
                </div>

                <div style="margin-bottom: 0;">
                    <h3 style="margin-top: 0; color: #0073aa; font-size: 16px;"><?php _e('Layout em Lista', 'afiliados-pro'); ?></h3>
                    <code style="display: block; padding: 10px; background: #f5f5f5; border-left: 3px solid #0073aa; margin: 8px 0;">[pap_products layout="list" columns="3" limit="9"]</code>
                    <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('[pap_products layout=&quot;list&quot; columns=&quot;3&quot; limit=&quot;9&quot;]'); alert('Shortcode copiado!');" style="margin-top: 5px;"><?php _e('Copiar', 'afiliados-pro'); ?></button>
                    <p style="color: #666; font-size: 13px; margin: 5px 0 0 0;"><?php _e('Exibe 9 produtos em formato de lista vertical com 3 colunas', 'afiliados-pro'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza a página de Importar CSV
     */
    public function render_import_csv_page() {
        require_once PAP_DIR . 'admin/admin-import-csv.php';
    }

    /**
     * Renderiza a página de Gerenciar Produtos
     */
    public function render_manage_products_page() {
        require_once PAP_DIR . 'admin/admin-manage-products.php';
    }

    /**
     * Adiciona Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'affiliate_product_details',
            __('Detalhes do Produto Afiliado', 'afiliados-pro'),
            array($this, 'render_meta_box'),
            $this->post_type,
            'normal',
            'high'
        );
    }

    /**
     * Renderiza o Meta Box
     */
    public function render_meta_box($post) {
        wp_nonce_field('affiliate_meta_box_nonce', 'affiliate_meta_nonce');

        $price = get_post_meta($post->ID, '_affiliate_price', true);
        $link = get_post_meta($post->ID, '_affiliate_link', true);
        $image_url = get_post_meta($post->ID, '_affiliate_image_url', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="affiliate_price"><?php _e('Preço (R$)', 'afiliados-pro'); ?></label></th>
                <td>
                    <input type="number" id="affiliate_price" name="affiliate_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" style="width: 200px;">
                    <p class="description"><?php _e('Digite apenas o valor numérico (ex: 99.90)', 'afiliados-pro'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="affiliate_link"><?php _e('Link de Afiliado', 'afiliados-pro'); ?></label></th>
                <td>
                    <input type="url" id="affiliate_link" name="affiliate_link" value="<?php echo esc_attr($link); ?>" style="width: 100%;">
                    <p class="description"><?php _e('URL completa do link de afiliado', 'afiliados-pro'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="affiliate_image_url"><?php _e('URL da Imagem', 'afiliados-pro'); ?></label></th>
                <td>
                    <input type="url" id="affiliate_image_url" name="affiliate_image_url" value="<?php echo esc_attr($image_url); ?>" style="width: 100%;">
                    <p class="description"><?php _e('URL da imagem do produto (opcional se usar imagem destacada)', 'afiliados-pro'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Salva os dados do Meta Box
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['affiliate_meta_nonce']) || !wp_verify_nonce($_POST['affiliate_meta_nonce'], 'affiliate_meta_box_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Salvar preço
        if (isset($_POST['affiliate_price'])) {
            $price = sanitize_text_field($_POST['affiliate_price']);
            if (is_numeric($price) && $price >= 0) {
                update_post_meta($post_id, '_affiliate_price', $price);
                // Limpar cache de preço médio quando um preço for atualizado
                delete_transient('affiliate_pro_avg_price');
            }
        }

        // Salvar link
        if (isset($_POST['affiliate_link'])) {
            $link = esc_url_raw($_POST['affiliate_link']);
            update_post_meta($post_id, '_affiliate_link', $link);
        }

        // Salvar URL da imagem
        if (isset($_POST['affiliate_image_url'])) {
            $image_url = esc_url_raw($_POST['affiliate_image_url']);
            update_post_meta($post_id, '_affiliate_image_url', $image_url);
        }
    }

    /**
     * Duplica um produto via AJAX
     */
    public function ajax_duplicate_product() {
        pap_log('ajax_duplicate_product() - Iniciando duplicação via AJAX');
        pap_log('POST data: ' . print_r($_POST, true));

        // Verificar permissões
        if (!current_user_can('edit_posts')) {
            pap_log('ajax_duplicate_product() - Permissão negada');
            wp_send_json_error(array(
                'message' => __('Você não tem permissão para duplicar produtos', 'afiliados-pro')
            ));
            return;
        }

        // Verificar nonce
        if (!isset($_POST['nonce'])) {
            pap_log('ajax_duplicate_product() - Nonce não enviado');
            wp_send_json_error(array(
                'message' => __('Erro de segurança: nonce não enviado', 'afiliados-pro')
            ));
            return;
        }

        if (!wp_verify_nonce($_POST['nonce'], 'affiliate_pro_admin_nonce')) {
            pap_log('ajax_duplicate_product() - Nonce inválido');
            wp_send_json_error(array(
                'message' => __('Erro de segurança: nonce inválido', 'afiliados-pro')
            ));
            return;
        }

        // Verificar se product_id foi enviado e é válido
        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            pap_log('ajax_duplicate_product() - product_id não enviado ou vazio');
            wp_send_json_error(array(
                'message' => __('Nenhum produto foi selecionado para duplicação', 'afiliados-pro')
            ));
            return;
        }

        $product_id = intval($_POST['product_id']);

        if ($product_id <= 0) {
            pap_log('ajax_duplicate_product() - product_id inválido: ' . $_POST['product_id']);
            wp_send_json_error(array(
                'message' => __('ID do produto inválido', 'afiliados-pro')
            ));
            return;
        }

        pap_log('ajax_duplicate_product() - Verificando produto ID: ' . $product_id);

        // Verificar se o produto existe
        $product = get_post($product_id);

        if (!$product) {
            pap_log('ajax_duplicate_product() - Produto não encontrado. ID: ' . $product_id);
            wp_send_json_error(array(
                'message' => __('Produto não encontrado (ID: ' . $product_id . ')', 'afiliados-pro')
            ));
            return;
        }

        if (is_wp_error($product)) {
            pap_log('ajax_duplicate_product() - Erro ao buscar produto: ' . $product->get_error_message());
            wp_send_json_error(array(
                'message' => __('Erro ao buscar produto: ' . $product->get_error_message(), 'afiliados-pro')
            ));
            return;
        }

        if ($product->post_type !== $this->post_type) {
            pap_log('ajax_duplicate_product() - Post type incorreto. Esperado: ' . $this->post_type . ', Recebido: ' . $product->post_type);
            wp_send_json_error(array(
                'message' => __('O item selecionado não é um produto afiliado', 'afiliados-pro')
            ));
            return;
        }

        pap_log('ajax_duplicate_product() - Produto válido. Iniciando duplicação...');

        // Tentar duplicar
        $new_product_id = $this->duplicate_product($product_id);

        // Verificar se houve erro (WP_Error)
        if (is_wp_error($new_product_id)) {
            pap_log('ajax_duplicate_product() - Erro na duplicação: ' . $new_product_id->get_error_message());
            wp_send_json_error(array(
                'message' => sprintf(__('Falha ao duplicar produto: %s', 'afiliados-pro'), $new_product_id->get_error_message())
            ));
            return;
        }

        // Verificar se o ID é válido
        if (!$new_product_id || !is_numeric($new_product_id) || $new_product_id <= 0) {
            pap_log('ajax_duplicate_product() - ID inválido retornado: ' . print_r($new_product_id, true));
            wp_send_json_error(array(
                'message' => __('Falha ao duplicar produto: ID inválido retornado.', 'afiliados-pro')
            ));
            return;
        }

        // CRÍTICO: Verificar se o post realmente existe no banco de dados antes de retornar sucesso
        $check_new_post = get_post($new_product_id);
        if (!$check_new_post) {
            pap_log('ajax_duplicate_product() - FALHA CRÍTICA: duplicate_product() retornou ID ' . $new_product_id . ', mas get_post() retornou null. O post não existe!');
            wp_send_json_error(array(
                'message' => sprintf(__('Falha ao duplicar produto: Post não encontrado após criação (ID: %d).', 'afiliados-pro'), $new_product_id)
            ));
            return;
        }

        // Verificar se o tipo do post está correto
        if ($check_new_post->post_type !== $this->post_type) {
            pap_log('ajax_duplicate_product() - Post criado com tipo incorreto. Esperado: ' . $this->post_type . ', Encontrado: ' . $check_new_post->post_type);
            wp_send_json_error(array(
                'message' => __('Falha ao duplicar produto: Post criado com tipo incorreto.', 'afiliados-pro')
            ));
            return;
        }

        // Tudo OK! Retornar sucesso
        pap_log('ajax_duplicate_product() - Duplicação VERIFICADA e bem-sucedida. Novo ID: ' . $new_product_id . ', Título: ' . $check_new_post->post_title . ', Status: ' . $check_new_post->post_status);
        wp_send_json_success(array(
            'message' => sprintf(__('Produto duplicado com sucesso! Novo ID: %d', 'afiliados-pro'), $new_product_id),
            'new_id' => $new_product_id,
            'edit_url' => admin_url('post.php?post=' . $new_product_id . '&action=edit'),
            'post_title' => $check_new_post->post_title,
            'post_status' => $check_new_post->post_status
        ));
    }

    /**
     * Duplica um produto
     *
     * @param int $original_id
     * @return int|WP_Error
     */
    public function duplicate_product($original_id) {
        // Validar ID
        if (empty($original_id) || !is_numeric($original_id)) {
            pap_log('duplicate_product() - ID inválido: ' . print_r($original_id, true));
            return new WP_Error('invalid_id', 'ID do produto inválido.');
        }

        $original_id = intval($original_id);

        // Buscar post original
        $original_post = get_post($original_id);

        if (!$original_post) {
            pap_log('duplicate_product() - Post não encontrado. ID: ' . $original_id);
            return new WP_Error('invalid_post', 'Produto original não encontrado.');
        }

        if (is_wp_error($original_post)) {
            pap_log('duplicate_product() - Erro ao buscar post: ' . $original_post->get_error_message());
            return $original_post;
        }

        // Verificar se é do tipo correto
        if ($original_post->post_type !== $this->post_type) {
            pap_log('duplicate_product() - Post type incorreto. Esperado: ' . $this->post_type . ', Recebido: ' . $original_post->post_type);
            return new WP_Error('invalid_post_type', 'O item selecionado não é um produto afiliado.');
        }

        // Preparar dados do novo post
        $new_post_data = array(
            'post_title'    => $original_post->post_title . ' ' . __('(Cópia)', 'afiliados-pro'),
            'post_content'  => $original_post->post_content,
            'post_excerpt'  => $original_post->post_excerpt,
            'post_status'   => 'draft',
            'post_type'     => $this->post_type,
            'post_author'   => get_current_user_id()
        );

        pap_log('duplicate_product() - Tentando criar novo post com dados: ' . print_r($new_post_data, true));

        // Criar novo post (true = retorna WP_Error se falhar)
        $new_post_id = wp_insert_post($new_post_data, true);

        // Verificar se houve erro na criação
        if (is_wp_error($new_post_id)) {
            pap_log('duplicate_product() - Erro ao criar post (WP_Error): ' . $new_post_id->get_error_message());
            return new WP_Error('insert_failed', 'Falha ao criar o produto duplicado: ' . $new_post_id->get_error_message());
        }

        if (!$new_post_id || $new_post_id === 0) {
            pap_log('duplicate_product() - wp_insert_post retornou ID inválido: ' . print_r($new_post_id, true));
            return new WP_Error('insert_failed', 'wp_insert_post retornou ID inválido.');
        }

        // CRÍTICO: Confirmar que o post foi realmente criado no banco de dados
        $check_post = get_post($new_post_id);
        if (!$check_post) {
            pap_log('duplicate_product() - FALHA CRÍTICA: wp_insert_post retornou ID ' . $new_post_id . ', mas get_post() retornou null. O post não existe no banco de dados!');
            return new WP_Error('missing_post', 'Post não foi criado de fato no banco de dados. ID retornado: ' . $new_post_id);
        }

        if ($check_post->post_type !== $this->post_type) {
            pap_log('duplicate_product() - AVISO: Post criado com tipo incorreto. Esperado: ' . $this->post_type . ', Criado: ' . $check_post->post_type);
            return new WP_Error('wrong_post_type', 'Post criado com tipo incorreto.');
        }

        // Log de sucesso na criação
        pap_log('duplicate_product() - Post criado e VERIFICADO com sucesso. ID original: ' . $original_id . ', Novo ID: ' . $new_post_id . ', Tipo: ' . $check_post->post_type . ', Status: ' . $check_post->post_status);

        // Copiar meta fields
        $meta_fields = array('_affiliate_price', '_affiliate_link', '_affiliate_image_url');
        foreach ($meta_fields as $meta_key) {
            $meta_value = get_post_meta($original_id, $meta_key, true);
            if ($meta_value !== '' && $meta_value !== false) {
                $result = update_post_meta($new_post_id, $meta_key, $meta_value);
                pap_log('Copiando meta ' . $meta_key . ' = ' . $meta_value . ' (resultado: ' . ($result ? 'OK' : 'FALHOU') . ')');
            }
        }

        // Copiar taxonomias
        $taxonomies = get_object_taxonomies($this->post_type);
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_post_terms($original_id, $taxonomy, array('fields' => 'slugs'));
                if (!is_wp_error($terms) && !empty($terms)) {
                    $result = wp_set_post_terms($new_post_id, $terms, $taxonomy);
                    if (is_wp_error($result)) {
                        pap_log('Erro ao copiar termos da taxonomia ' . $taxonomy . ': ' . $result->get_error_message());
                    } else {
                        pap_log('Copiando termos da taxonomia ' . $taxonomy . ': ' . implode(', ', $terms));
                    }
                }
            }
        }

        // Copiar imagem destacada
        $thumbnail_id = get_post_thumbnail_id($original_id);
        if ($thumbnail_id) {
            $result = set_post_thumbnail($new_post_id, $thumbnail_id);
            pap_log('Copiando thumbnail ID ' . $thumbnail_id . ' (resultado: ' . ($result ? 'OK' : 'FALHOU') . ')');
        }

        pap_log('duplicate_product() - Duplicação concluída com sucesso. Novo produto ID: ' . $new_post_id);

        return $new_post_id;
    }

    /**
     * Obtém o nome do post type
     *
     * @return string
     */
    public function get_post_type() {
        return $this->post_type;
    }

    /**
     * Obtém o nome da taxonomia
     *
     * @return string
     */
    public function get_taxonomy() {
        return $this->taxonomy;
    }
}
