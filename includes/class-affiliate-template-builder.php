<?php
/**
 * Classe responsável pelo Template Builder
 *
 * @package Affiliate_Pro
 * @since 1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Affiliate_Template_Builder {

    /**
     * Instância única (Singleton)
     *
     * @var Affiliate_Template_Builder
     */
    private static $instance = null;

    /**
     * Nome da opção no banco de dados
     *
     * @var string
     */
    private $option_name = 'affiliate_template_settings';

    /**
     * Obtém a instância única
     *
     * @return Affiliate_Template_Builder
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
        $this->migrate_legacy_settings(); // v1.4.2
    }

    /**
     * Migra configurações legacy para novos campos (v1.4.2)
     */
    private function migrate_legacy_settings() {
        $settings = get_option($this->option_name, array());

        // Migrar campo 'shadow' legado para 'shadow_card'
        if (isset($settings['shadow']) && !isset($settings['shadow_card'])) {
            $settings['shadow_card'] = $settings['shadow'];
            unset($settings['shadow']);
            update_option($this->option_name, $settings);
            affiliate_pro_log('Template Builder: Migrated legacy shadow to shadow_card');
        }
    }

    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'register_template_builder_menu'));
        add_action('admin_post_affiliate_template_save', array($this, 'save_template_settings'));
        // v1.5.2: Removido apply_template_styles - agora usa Affiliate_Pro_Settings::get_dynamic_css()
        // add_action('wp_head', array($this, 'apply_template_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Registra o menu Template Builder (v1.4.4 - Tabbed Interface)
     */
    public function register_template_builder_menu() {
        add_submenu_page(
            'affiliate-products',
            __('Aparência e Configurações', 'afiliados-pro'),
            __('Aparência e Configurações', 'afiliados-pro'),
            'manage_options',
            'affiliate-template-builder',
            array($this, 'render_template_builder_page')
        );

        // Submenu Estatísticas (v1.4.8)
        add_submenu_page(
            'affiliate-products',
            __('Estatísticas', 'afiliados-pro'),
            __('Estatísticas', 'afiliados-pro'),
            'manage_options',
            'affiliate-stats',
            array($this, 'render_stats_page')
        );
    }

    /**
     * Renderiza a página de Estatísticas (v1.4.8)
     */
    public function render_stats_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para acessar esta página.', 'afiliados-pro'));
        }

        require_once AFFILIATE_PRO_PLUGIN_DIR . 'admin/admin-stats.php';
    }

    /**
     * Renderiza a página do Template Builder (v1.4.4 - Tabbed Interface)
     */
    public function render_template_builder_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para acessar esta página.', 'afiliados-pro'));
        }

        // Get active tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'appearance';

        // Mensagem de sucesso
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Configurações salvas com sucesso!', 'afiliados-pro') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Afiliados Pro - Aparência e Configurações', 'afiliados-pro'); ?></h1>

            <!-- Tab Navigation (v1.4.4) -->
            <h2 class="nav-tab-wrapper">
                <a href="?page=affiliate-template-builder&tab=appearance" class="nav-tab <?php echo $active_tab === 'appearance' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Aparência', 'afiliados-pro'); ?>
                </a>
                <a href="?page=affiliate-template-builder&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Configurações', 'afiliados-pro'); ?>
                </a>
            </h2>

            <?php
            // Render active tab
            if ($active_tab === 'appearance') {
                $this->render_appearance_tab();
            } else {
                $this->render_settings_tab();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render Appearance Tab (v1.4.6 - Split Layout)
     */
    private function render_appearance_tab() {
        $settings = self::get_template_settings();
        ?>
        <div class="tab-content">
            <p><?php _e('Personalize a aparência visual dos cards de produtos afiliados exibidos no front-end.', 'afiliados-pro'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('affiliate_template_save', 'affiliate_template_nonce'); ?>
                <input type="hidden" name="action" value="affiliate_template_save">

                <!-- v1.4.6: Split Layout Container -->
                <div class="affiliate-builder-container">
                    <!-- Left: Preview Pane -->
                    <div class="affiliate-preview-pane">
                        <h3>🖼️ <?php _e('Pré-visualização', 'afiliados-pro'); ?></h3>
                        <iframe id="affiliate-preview-frame"
                            src="about:blank"
                            data-preview-url="<?php echo esc_url(Affiliate_Preview_Handler::get_preview_url()); ?>"
                            style="width:100%;height:800px;border:1px solid #ccc;border-radius:8px;background:#fff;">
                        </iframe>
                        <button id="generate-preview" class="button button-primary" type="button" style="margin-top:10px;">
                            <?php _e('Gerar Pré-visualização', 'afiliados-pro'); ?>
                        </button>
                    </div>

                    <!-- Right: Controls Pane -->
                    <div class="affiliate-controls-pane">
                        <h3>🎨 <?php _e('Personalização dos Cards', 'afiliados-pro'); ?></h3>

                        <!-- Group 1: Visual Identity Colors -->
                        <fieldset>
                            <legend><strong><?php _e('Identidade Visual', 'afiliados-pro'); ?></strong></legend>

                            <p>
                                <label for="card_background_color"><?php _e('Fundo do Card', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="card_background_color" name="card_background_color" value="<?php echo esc_attr($settings['card_background_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Cor de fundo dos cards', 'afiliados-pro'); ?></span>
                            </p>

                            <p>
                                <label for="text_color"><?php _e('Cor do Texto', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="text_color" name="text_color" value="<?php echo esc_attr($settings['text_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Texto nos cards', 'afiliados-pro'); ?></span>
                            </p>

                            <p>
                                <label for="price_color"><?php _e('Cor do Preço', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="price_color" name="price_color" value="<?php echo esc_attr($settings['price_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Cor do valor do produto', 'afiliados-pro'); ?></span>
                            </p>

                            <p>
                                <label for="primary_color"><?php _e('Cor Primária', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Títulos e elementos destacados', 'afiliados-pro'); ?></span>
                            </p>

                            <p>
                                <label for="button_color"><?php _e('Cor do Botão', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="button_color" name="button_color" value="<?php echo esc_attr($settings['button_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Cor dos botões de ação', 'afiliados-pro'); ?></span>
                            </p>

                            <p>
                                <label for="gradient_color"><?php _e('Cor Secundária (Gradiente)', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="gradient_color" name="gradient_color" value="<?php echo esc_attr($settings['gradient_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Para botões com gradiente', 'afiliados-pro'); ?></span>
                            </p>

                            <p>
                                <label for="accent_color"><?php _e('Cor de Destaque (Badge)', 'afiliados-pro'); ?></label><br>
                                <input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr($settings['accent_color']); ?>" style="width:60px;height:40px;">
                                <span class="description"><?php _e('Usada em badges, preços e destaques', 'afiliados-pro'); ?></span>
                            </p>
                        </fieldset>

                        <!-- Group 2: Card Structure -->
                        <fieldset>
                            <legend><strong><?php _e('Estrutura dos Cards', 'afiliados-pro'); ?></strong></legend>

                            <p>
                                <label for="card_style"><?php _e('Estilo do Card', 'afiliados-pro'); ?></label><br>
                                <select id="card_style" name="card_style" class="regular-text">
                                    <option value="modern" <?php selected($settings['card_style'], 'modern'); ?>><?php _e('Moderno', 'afiliados-pro'); ?></option>
                                    <option value="classic" <?php selected($settings['card_style'], 'classic'); ?>><?php _e('Clássico', 'afiliados-pro'); ?></option>
                                    <option value="minimal" <?php selected($settings['card_style'], 'minimal'); ?>><?php _e('Minimalista', 'afiliados-pro'); ?></option>
                                    <option value="cards" <?php selected($settings['card_style'], 'cards'); ?>><?php _e('Cards', 'afiliados-pro'); ?></option>
                                </select>
                            </p>

                            <p>
                                <label for="border_radius"><?php _e('Raio da Borda', 'afiliados-pro'); ?></label><br>
                                <select id="border_radius" name="border_radius" class="regular-text">
                                    <option value="none" <?php selected($settings['border_radius'], 'none'); ?>><?php _e('Nenhum (0px)', 'afiliados-pro'); ?></option>
                                    <option value="small" <?php selected($settings['border_radius'], 'small'); ?>><?php _e('Pequeno (4px)', 'afiliados-pro'); ?></option>
                                    <option value="medium" <?php selected($settings['border_radius'], 'medium'); ?>><?php _e('Médio (8px)', 'afiliados-pro'); ?></option>
                                    <option value="large" <?php selected($settings['border_radius'], 'large'); ?>><?php _e('Grande (16px)', 'afiliados-pro'); ?></option>
                                </select>
                            </p>

                            <p>
                                <label>
                                    <input type="checkbox" id="shadow_card" name="shadow_card" value="1" <?php checked($settings['shadow_card'], true); ?>>
                                    <?php _e('Sombra nos cards', 'afiliados-pro'); ?>
                                </label>
                            </p>

                            <p>
                                <label for="card_gap"><?php _e('Espaçamento entre Cards', 'afiliados-pro'); ?></label><br>
                                <input type="number" id="card_gap" name="card_gap" min="0" max="100" value="<?php echo esc_attr($settings['card_gap']); ?>" class="small-text"> px
                            </p>

                            <p>
                                <label for="layout_default"><?php _e('Layout Padrão', 'afiliados-pro'); ?></label><br>
                                <select id="layout_default" name="layout_default" class="regular-text">
                                    <option value="grid" <?php selected($settings['layout_default'], 'grid'); ?>><?php _e('Grade', 'afiliados-pro'); ?></option>
                                    <option value="list" <?php selected($settings['layout_default'], 'list'); ?>><?php _e('Lista', 'afiliados-pro'); ?></option>
                                    <option value="carousel" <?php selected($settings['layout_default'], 'carousel'); ?>><?php _e('Carrossel', 'afiliados-pro'); ?></option>
                                    <option value="masonry" <?php selected($settings['layout_default'], 'masonry'); ?>><?php _e('Masonry', 'afiliados-pro'); ?></option>
                                </select>
                            </p>

                            <p>
                                <label for="columns"><?php _e('Número de Colunas', 'afiliados-pro'); ?></label><br>
                                <input type="number" id="columns" name="columns" value="<?php echo esc_attr($settings['columns']); ?>" min="2" max="4" class="small-text">
                            </p>
                        </fieldset>

                        <!-- Group 3: Content Elements -->
                        <fieldset>
                            <legend><strong><?php _e('Elementos de Conteúdo', 'afiliados-pro'); ?></strong></legend>

                            <p>
                                <label for="button_style"><?php _e('Estilo do Botão', 'afiliados-pro'); ?></label><br>
                                <select id="button_style" name="button_style" class="regular-text">
                                    <option value="filled" <?php selected($settings['button_style'], 'filled'); ?>><?php _e('Preenchido', 'afiliados-pro'); ?></option>
                                    <option value="outline" <?php selected($settings['button_style'], 'outline'); ?>><?php _e('Contorno', 'afiliados-pro'); ?></option>
                                    <option value="gradient" <?php selected($settings['button_style'], 'gradient'); ?>><?php _e('Gradiente', 'afiliados-pro'); ?></option>
                                </select>
                            </p>

                            <p>
                                <label>
                                    <input type="checkbox" id="shadow_button" name="shadow_button" value="1" <?php checked($settings['shadow_button'], true); ?>>
                                    <?php _e('Sombra nos botões', 'afiliados-pro'); ?>
                                </label>
                            </p>

                            <p>
                                <label>
                                    <input type="checkbox" id="force_css" name="force_css" value="1" <?php checked($settings['force_css'], true); ?>>
                                    <?php _e('Forçar CSS do Template Builder', 'afiliados-pro'); ?>
                                </label><br>
                                <span class="description" style="color: #d63638;">
                                    ⚠️ <?php _e('Use apenas se o tema sobrescrever os estilos', 'afiliados-pro'); ?>
                                </span>
                            </p>
                        </fieldset>

                        <?php submit_button(__('Salvar Configurações', 'afiliados-pro'), 'primary', 'submit'); ?>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render Settings Tab (v1.4.4)
     */
    private function render_settings_tab() {
        $settings = self::get_template_settings();
        ?>
        <div class="tab-content">
            <p><?php _e('Configure o comportamento e opções funcionais dos produtos afiliados.', 'afiliados-pro'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('affiliate_template_save', 'affiliate_template_nonce'); ?>
                <input type="hidden" name="action" value="affiliate_template_save">

                <table class="form-table" role="presentation">
                    <tbody>
                        <!-- Texto do Botão -->
                        <tr>
                            <th scope="row">
                                <label for="button_text"><?php _e('Texto do Botão', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="button_text" name="button_text" value="<?php echo esc_attr($settings['button_text']); ?>" class="regular-text" placeholder="Ex: Ver Produto">
                                <p class="description"><?php _e('Texto exibido no botão de ação dos cards de produtos.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Exibir Preço -->
                        <tr>
                            <th scope="row">
                                <label for="show_price"><?php _e('Exibir Preço', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="show_price" name="show_price" value="1" <?php checked($settings['show_price'], true); ?>>
                                    <?php _e('Mostrar o preço nos cards de produtos', 'afiliados-pro'); ?>
                                </label>
                                <p class="description"><?php _e('Ativa ou desativa a exibição do preço do produto.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Título Clicável -->
                        <tr>
                            <th scope="row">
                                <label for="clickable_title"><?php _e('Título Clicável', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="clickable_title" name="clickable_title" value="1" <?php checked($settings['clickable_title'], true); ?>>
                                    <?php _e('Tornar o título do produto clicável (link para a página do produto)', 'afiliados-pro'); ?>
                                </label>
                                <p class="description"><?php _e('Se ativo, o título será um link para a página individual do produto.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Abrir em nova aba (v1.4.5) -->
                        <tr>
                            <th scope="row">
                                <label for="open_in_new_tab"><?php _e('Abrir em nova aba', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="open_in_new_tab" name="open_in_new_tab" value="1" <?php checked($settings['open_in_new_tab'], true); ?>>
                                    <?php _e('Abrir o link do produto em uma nova aba', 'afiliados-pro'); ?>
                                </label>
                                <p class="description"><?php _e('Quando ativo, os links dos produtos abrem em uma nova aba do navegador.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Mostrar Badge da Loja -->
                        <tr>
                            <th scope="row">
                                <label for="show_store_badge"><?php _e('Mostrar Badge da Loja', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="show_store_badge" name="show_store_badge" value="1" <?php checked($settings['show_store_badge'], true); ?>>
                                    <?php _e('Exibir o selo/badge do marketplace (ex: Amazon, Mercado Livre)', 'afiliados-pro'); ?>
                                </label>
                                <p class="description"><?php _e('Mostra visualmente de qual loja o produto é proveniente.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Exibição de Preços (v1.4.5) -->
                <h3 style="margin-top: 30px;">💰 <?php _e('Exibição de Preços', 'afiliados-pro'); ?></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                        <!-- Formato do Preço -->
                        <tr>
                            <th scope="row">
                                <label for="price_format"><?php _e('Formato do Preço', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="price_format" name="price_format" value="<?php echo esc_attr($settings['price_format']); ?>" class="regular-text" placeholder="R$ {valor}">
                                <p class="description"><?php _e('Use {valor} onde o preço deve aparecer. Exemplo: R$ {valor} ou USD {valor}', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Texto Quando Sem Preço -->
                        <tr>
                            <th scope="row">
                                <label for="price_text_empty"><?php _e('Texto Quando Sem Preço', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="price_text_empty" name="price_text_empty" value="<?php echo esc_attr($settings['price_text_empty']); ?>" class="regular-text" placeholder="Consulte o preço">
                                <p class="description"><?php _e('Texto exibido quando o produto não tem preço definido.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h3 style="margin-top: 30px;"><?php _e('Personalização Avançada', 'afiliados-pro'); ?></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                        <!-- CSS Personalizado -->
                        <tr>
                            <th scope="row">
                                <label for="custom_css"><?php _e('CSS Personalizado', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <textarea id="custom_css" name="custom_css" rows="8" style="width: 100%; font-family: monospace;" placeholder="/* Adicione seu CSS customizado aqui */"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                                <p class="description"><?php _e('Adicione CSS personalizado para estilizar os cards de produtos de forma avançada.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Salvar Configurações', 'afiliados-pro'), 'primary', 'submit'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Salva as configurações do template
     * v1.5.2: Migrado para usar affiliate_pro_settings para persistência correta
     */
    public function save_template_settings() {
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
        }

        // Verificar nonce
        if (!isset($_POST['affiliate_template_nonce']) || !wp_verify_nonce($_POST['affiliate_template_nonce'], 'affiliate_template_save')) {
            wp_die(__('Erro de segurança. Tente novamente.', 'afiliados-pro'));
        }

        // v1.5.2: Obter configurações atuais do sistema unificado
        $current_settings = Affiliate_Pro_Settings::get_settings();

        // Mapear campos do Template Builder para Affiliate_Pro_Settings
        $settings = $current_settings;

        // Mapear cores
        if (isset($_POST['primary_color'])) {
            $settings['primary_color'] = sanitize_hex_color($_POST['primary_color']);
        }
        if (isset($_POST['card_background_color'])) {
            $settings['card_bg_color'] = sanitize_hex_color($_POST['card_background_color']);
        }
        if (isset($_POST['text_color'])) {
            $settings['text_color'] = sanitize_hex_color($_POST['text_color']);
        }
        // v1.5.8: Salvar price_color (cor do preço)
        if (isset($_POST['price_color'])) {
            $settings['price_color'] = sanitize_hex_color($_POST['price_color']);
        }
        // v1.5.7: Corrigido - salvar button_color como button_color_start (não como accent_color)
        if (isset($_POST['button_color'])) {
            $settings['button_color_start'] = sanitize_hex_color($_POST['button_color']);
        }
        // v1.5.7: Salvar gradient_color como button_color_end
        if (isset($_POST['gradient_color'])) {
            $settings['button_color_end'] = sanitize_hex_color($_POST['gradient_color']);
        }
        // v1.5.7: Salvar accent_color (cor de destaque/badge)
        if (isset($_POST['accent_color'])) {
            $settings['accent_color'] = sanitize_hex_color($_POST['accent_color']);
        }

        // Mapear bordas (converter de texto para número)
        if (isset($_POST['border_radius'])) {
            $radius_map = array(
                'none' => 0,
                'small' => 4,
                'medium' => 12,
                'large' => 20,
            );
            $border_key = sanitize_text_field($_POST['border_radius']);
            if (isset($radius_map[$border_key])) {
                $settings['card_border_radius'] = $radius_map[$border_key];
            }
        }

        // Mapear sombra
        if (isset($_POST['shadow_card'])) {
            $settings['card_shadow'] = boolval($_POST['shadow_card']);
        }

        // Mapear layout
        if (isset($_POST['layout_default'])) {
            $allowed_layouts = array('grid', 'list');
            $layout = sanitize_text_field($_POST['layout_default']);
            if (in_array($layout, $allowed_layouts)) {
                $settings['default_layout'] = $layout;
            }
        }

        // Mapear colunas
        if (isset($_POST['columns'])) {
            $columns = absint($_POST['columns']);
            $settings['default_columns'] = max(2, min(4, $columns));
        }

        // Mapear gap
        if (isset($_POST['card_gap'])) {
            $card_gap = absint($_POST['card_gap']);
            $settings['card_gap'] = max(0, min(100, $card_gap));
        }

        // Mapear configurações funcionais
        if (isset($_POST['button_text'])) {
            $settings['button_text'] = sanitize_text_field($_POST['button_text']);
        }
        if (isset($_POST['clickable_title'])) {
            $settings['title_clickable'] = boolval($_POST['clickable_title']);
        }
        if (isset($_POST['open_in_new_tab'])) {
            $settings['open_in_new_tab'] = boolval($_POST['open_in_new_tab']);
        }
        if (isset($_POST['show_store_badge'])) {
            $settings['show_store_badge'] = boolval($_POST['show_store_badge']);
        }
        if (isset($_POST['custom_css'])) {
            $settings['custom_css'] = wp_strip_all_tags($_POST['custom_css']);
        }

        // Mapear formato de preço
        if (isset($_POST['price_format'])) {
            $settings['price_format'] = sanitize_text_field($_POST['price_format']);
        }
        if (isset($_POST['price_text_empty'])) {
            $settings['price_placeholder'] = sanitize_text_field($_POST['price_text_empty']);
        }

        // v1.5.2: Salvar no sistema unificado affiliate_pro_settings
        update_option('affiliate_pro_settings', $settings);

        // Registrar log (se debug ativo)
        affiliate_pro_log('Template Builder: Configurações salvas com sucesso em affiliate_pro_settings');

        // Redirecionar com mensagem de sucesso
        wp_redirect(add_query_arg(
            array('page' => 'affiliate-template-builder', 'settings-updated' => 'true'),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Retorna as configurações do template (mescladas com defaults)
     * v1.5.2: Sincronizado com Affiliate_Pro_Settings para persistência correta
     * v1.5.3: Adiciona mapeamento reverso para compatibilidade com formulários antigos
     *
     * @return array
     */
    public static function get_template_settings() {
        // v1.5.2: Agora usa as configurações unificadas do Affiliate_Pro_Settings
        // Isso garante que o front-end e o admin sempre estejam sincronizados
        $settings = Affiliate_Pro_Settings::get_settings();

        // v1.5.3: Mapear chaves unificadas de volta para chaves antigas do Template Builder
        // Isso previne "Undefined array key" warnings nos formulários antigos

        // Mapear accent_color -> highlight_color (para exibição no form)
        if (!isset($settings['highlight_color']) && isset($settings['accent_color'])) {
            $settings['highlight_color'] = $settings['accent_color'];
        }

        // Mapear card_bg_color -> card_background_color
        if (!isset($settings['card_background_color']) && isset($settings['card_bg_color'])) {
            $settings['card_background_color'] = $settings['card_bg_color'];
        }

        // Mapear button_color_start -> button_color
        if (!isset($settings['button_color']) && isset($settings['button_color_start'])) {
            $settings['button_color'] = $settings['button_color_start'];
        } elseif (!isset($settings['button_color']) && isset($settings['accent_color'])) {
            $settings['button_color'] = $settings['accent_color'];
        }

        // Mapear button_color_end -> gradient_color
        if (!isset($settings['gradient_color']) && isset($settings['button_color_end'])) {
            $settings['gradient_color'] = $settings['button_color_end'];
        }

        // Mapear card_border_radius (número) -> border_radius (texto)
        if (!isset($settings['border_radius']) && isset($settings['card_border_radius'])) {
            $radius = intval($settings['card_border_radius']);
            if ($radius === 0) {
                $settings['border_radius'] = 'none';
            } elseif ($radius <= 4) {
                $settings['border_radius'] = 'small';
            } elseif ($radius <= 12) {
                $settings['border_radius'] = 'medium';
            } else {
                $settings['border_radius'] = 'large';
            }
        }

        // Mapear card_shadow -> shadow_card
        if (!isset($settings['shadow_card']) && isset($settings['card_shadow'])) {
            $settings['shadow_card'] = $settings['card_shadow'];
        }

        // Mapear default_layout -> layout_default
        if (!isset($settings['layout_default']) && isset($settings['default_layout'])) {
            $settings['layout_default'] = $settings['default_layout'];
        }

        // Mapear default_columns -> columns
        if (!isset($settings['columns']) && isset($settings['default_columns'])) {
            $settings['columns'] = $settings['default_columns'];
        }

        // Mapear title_clickable -> clickable_title
        if (!isset($settings['clickable_title']) && isset($settings['title_clickable'])) {
            $settings['clickable_title'] = $settings['title_clickable'];
        }

        // Mapear price_placeholder -> price_text_empty
        if (!isset($settings['price_text_empty']) && isset($settings['price_placeholder'])) {
            $settings['price_text_empty'] = $settings['price_placeholder'];
        }

        // v1.5.4: Mapear valores antigos de button_style
        if (isset($settings['button_style'])) {
            if ($settings['button_style'] === 'filled') {
                $settings['button_style'] = 'flat';
            }
        }

        // Defaults para campos que não existem no sistema unificado
        $settings = wp_parse_args($settings, array(
            'card_style' => 'modern',
            'button_style' => 'gradient',
            'shadow_button' => false,
            'force_css' => false,
            'show_price' => true,
        ));

        return $settings;
    }

    /**
     * Enfileira assets CSS e JS (v1.4.0)
     */
    public function enqueue_assets($hook) {
        // Only load on Template Builder page
        if ($hook !== 'afiliados_page_affiliate-template-builder') {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'affiliate-template-css',
            AFFILIATE_PRO_PLUGIN_URL . 'assets/css/affiliate-template.css',
            array(),
            AFFILIATE_PRO_VERSION
        );

        // Enqueue JS with jQuery dependency
        wp_enqueue_script(
            'affiliate-preview-js',
            AFFILIATE_PRO_PLUGIN_URL . 'assets/js/template-preview.js',
            array('jquery'),
            AFFILIATE_PRO_VERSION,
            true
        );

        // Log asset loading
        affiliate_pro_log('Template Builder: Assets enqueued for page ' . $hook);
    }

    /**
     * Aplica os estilos personalizados no front-end
     */
    public function apply_template_styles() {
        $settings = self::get_template_settings();

        // Mapa de raio de borda
        $radius_map = array(
            'none' => '0px',
            'small' => '4px',
            'medium' => '8px',
            'large' => '16px',
        );

        $border_radius = isset($radius_map[$settings['border_radius']]) ? $radius_map[$settings['border_radius']] : '8px';

        // Determinar se deve forçar CSS
        $important = !empty($settings['force_css']) ? ' !important' : '';

        // Gerar CSS dinâmico
        $css = "
        /* Afiliados Pro - Template Builder v1.5.8 */

        :root {
            --affiliate-template-primary: {$settings['primary_color']};
            --affiliate-template-button: {$settings['button_color']};
            --affiliate-template-radius: {$border_radius};
            --affiliate-price-color: {$settings['price_color']};
        }

        /* Refinamento Visual - Cards de Produtos */
        .affiliate-product-card {
            border: 1px solid #e0e0e0{$important};
            padding: 12px{$important};
            margin-bottom: 16px{$important};
            border-radius: var(--affiliate-template-radius){$important};
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out{$important};
            ";

        // Aplicar sombra do card (v1.4.2 - usa apenas shadow_card)
        $use_card_shadow = !empty($settings['shadow_card']);
        if ($use_card_shadow) {
            $css .= "box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1){$important};";
        } else {
            $css .= "box-shadow: none{$important};";
        }

        $css .= "
        }

        .affiliate-product-card:hover {
            transform: translateY(-3px){$important};";

        if (!empty($use_card_shadow)) {
            $css .= "
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15){$important};";
        }

        $css .= "
        }

        /* Títulos dos produtos */
        .affiliate-product-card .product-title,
        .affiliate-product-card .product-title a {
            color: var(--affiliate-template-primary){$important};
        }

        /* Botões - Cor corrigida */
        .affiliate-product-card .product-button,
        .affiliate-btn {
            border-radius: var(--affiliate-template-radius){$important};
            padding: 10px 20px{$important};
            text-decoration: none{$important};
            display: inline-block{$important};
            transition: all 0.3s ease{$important};";

        // Estilos de botão com cor corrigida
        if ($settings['button_style'] === 'filled') {
            $css .= "
            background-color: {$settings['button_color']}{$important};
            border: 2px solid {$settings['button_color']}{$important};
            color: #fff{$important};";
        } elseif ($settings['button_style'] === 'outline') {
            $css .= "
            background-color: transparent{$important};
            border: 2px solid {$settings['button_color']}{$important};
            color: {$settings['button_color']}{$important};";
        } elseif ($settings['button_style'] === 'gradient') {
            // v1.4.1: Use gradient_color as secondary, fallback to primary_color
            $gradient_secondary = !empty($settings['gradient_color']) ? $settings['gradient_color'] : $settings['primary_color'];
            $css .= "
            background: linear-gradient(135deg, {$settings['button_color']} 0%, {$gradient_secondary} 100%){$important};
            border: none{$important};
            color: #fff{$important};";
        }

        // Aplicar sombra do botão (v1.4.0)
        $use_button_shadow = isset($settings['shadow_button']) ? $settings['shadow_button'] : false;
        if (!empty($use_button_shadow)) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15){$important};";
        } else {
            $css .= "
            box-shadow: none{$important};";
        }

        $css .= "
        }

        .affiliate-product-card .product-button:hover,
        .affiliate-btn:hover {";

        if ($settings['button_style'] === 'filled') {
            $css .= "
            opacity: 0.9{$important};
            transform: translateY(-2px){$important};";
        } elseif ($settings['button_style'] === 'outline') {
            $css .= "
            background-color: {$settings['button_color']}{$important};
            color: #fff{$important};";
        } elseif ($settings['button_style'] === 'gradient') {
            $css .= "
            opacity: 0.95{$important};
            transform: translateY(-2px){$important};";
        }

        // Sombra intensificada no hover (v1.4.0)
        if (!empty($use_button_shadow)) {
            $css .= "
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2){$important};";
        }

        $css .= "
        }

        /* Estilos de Card específicos */";

        // Estilos por tipo de card
        if ($settings['card_style'] === 'modern') {
            $css .= "
        .affiliate-product-card {
            background: linear-gradient(to bottom, #fff 0%, #f8f9fa 100%){$important};
        }";
        } elseif ($settings['card_style'] === 'classic') {
            $css .= "
        .affiliate-product-card {
            background: #fff{$important};
            border-width: 2px{$important};
            border-color: #dee2e6{$important};
        }";
        } elseif ($settings['card_style'] === 'minimal') {
            $css .= "
        .affiliate-product-card {
            background: #fff{$important};
            border: none{$important};
            border-bottom: 3px solid {$settings['primary_color']}{$important};
        }";
        } elseif ($settings['card_style'] === 'cards') {
            $css .= "
        .affiliate-product-card {
            background: #fff{$important};
            padding: 20px{$important};
        }";
        }

        $css .= "
        ";

        // Inserir CSS no head
        echo '<style type="text/css" id="affiliate-template-builder-styles">' . $css . '</style>';
    }

    /**
     * Retorna o mapa de raio de borda
     *
     * @return array
     */
    public static function get_border_radius_map() {
        return array(
            'none' => '0px',
            'small' => '4px',
            'medium' => '8px',
            'large' => '16px',
        );
    }
}
