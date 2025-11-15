<?php
/**
 * Classe responsável pelo Template Builder
 * v1.7.2: Refatoração gradual - PAP_Template_Builder é agora a classe principal
 *
 * @package PAP
 * @since 1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal de Template Builder com prefixo padronizado PAP
 * v1.7.2: Promovida de espelho para classe principal
 *
 * @package PAP
 * @since 1.7.2
 */
class PAP_Template_Builder {

    /**
     * Instância única (Singleton)
     *
     * @var PAP_Template_Builder
     */
    private static $instance = null;

    /**
     * Obtém a instância única
     *
     * @return PAP_Template_Builder
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor
     * v1.9.4: Removed legacy migration (now runs only on activation)
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'register_template_builder_menu'));
        add_action('admin_post_affiliate_template_save', array($this, 'save_template_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        // v1.6.0: Sistema de Presets
        add_action('admin_post_affiliate_preset_save', array($this, 'save_preset'));
        add_action('admin_post_affiliate_preset_delete', array($this, 'delete_preset'));
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

        require_once PAP_DIR . 'admin/admin-stats.php';
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

            <!-- Tab Navigation (v1.6.0 - Adicionado Presets) -->
            <h2 class="nav-tab-wrapper">
                <a href="?page=affiliate-template-builder&tab=appearance" class="nav-tab <?php echo $active_tab === 'appearance' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Aparência', 'afiliados-pro'); ?>
                </a>
                <a href="?page=affiliate-template-builder&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Configurações', 'afiliados-pro'); ?>
                </a>
                <a href="?page=affiliate-template-builder&tab=presets" class="nav-tab <?php echo $active_tab === 'presets' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Presets', 'afiliados-pro'); ?>
                </a>
            </h2>

            <?php
            // Render active tab
            if ($active_tab === 'appearance') {
                $this->render_appearance_tab();
            } elseif ($active_tab === 'presets') {
                $this->render_presets_tab();
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
                <input type="hidden" name="current_tab" value="appearance">

                <!-- v1.4.6: Split Layout Container -->
                <div class="affiliate-builder-container">
                    <!-- Left: Preview Pane -->
                    <div class="affiliate-preview-pane">
                        <h3>🖼️ <?php _e('Pré-visualização', 'afiliados-pro'); ?></h3>
                        <iframe id="affiliate-preview-frame"
                            src="about:blank"
                            data-preview-url="<?php echo esc_url(PAP_Preview_Handler::get_preview_url()); ?>"
                            style="width:100%;height:500px;border:1px solid #ccc;border-radius:8px;background:#fff;">
                        </iframe>
                        <button id="generate-preview" class="button button-primary" type="button" style="margin-top:10px;">
                            <?php _e('Gerar Pré-visualização', 'afiliados-pro'); ?>
                        </button>
                    </div>

                    <!-- Right: Controls Pane -->
                    <div class="affiliate-controls-pane">
                        <h3>🎨 <?php _e('Personalização dos Cards', 'afiliados-pro'); ?></h3>

                        <!-- Group 1: Visual Identity Colors (v1.6.2: Reordenado conforme hierarquia visual) -->
                        <fieldset>
                            <legend><strong><?php _e('Identidade Visual', 'afiliados-pro'); ?></strong></legend>

                            <!-- 1. Fundo da Área da Imagem -->
                            <div class="color-field-compact">
                                <input type="color" id="card_image_background" name="card_image_background" value="<?php echo esc_attr($settings['card_image_background']); ?>">
                                <div class="color-field-labels">
                                    <label for="card_image_background"><?php _e('Fundo da Área da Imagem', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Cor de fundo atrás da imagem do produto', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 2. Fundo do Card -->
                            <div class="color-field-compact">
                                <input type="color" id="card_background_color" name="card_background_color" value="<?php echo esc_attr($settings['card_background_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="card_background_color"><?php _e('Fundo do Card', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Cor de fundo dos cards', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 3. Cor Primária -->
                            <div class="color-field-compact">
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="primary_color"><?php _e('Cor Primária', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Títulos e elementos destacados', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 4. Cor do Texto -->
                            <div class="color-field-compact">
                                <input type="color" id="text_color" name="text_color" value="<?php echo esc_attr($settings['text_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="text_color"><?php _e('Cor do Texto', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Texto nos cards', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 5. Cor do Preço -->
                            <div class="color-field-compact">
                                <input type="color" id="price_color" name="price_color" value="<?php echo esc_attr($settings['price_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="price_color"><?php _e('Cor do Preço', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Cor do valor do produto', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 6. Cor do Botão -->
                            <div class="color-field-compact">
                                <input type="color" id="button_color" name="button_color" value="<?php echo esc_attr($settings['button_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="button_color"><?php _e('Cor do Botão', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Cor dos botões de ação', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 7. Cor Secundária (Gradiente) -->
                            <div class="color-field-compact">
                                <input type="color" id="gradient_color" name="gradient_color" value="<?php echo esc_attr($settings['gradient_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="gradient_color"><?php _e('Cor Secundária (Gradiente)', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Para botões com gradiente', 'afiliados-pro'); ?></span>
                                </div>
                            </div>

                            <!-- 8. Cor de Destaque (Badge) -->
                            <div class="color-field-compact">
                                <input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr($settings['accent_color']); ?>">
                                <div class="color-field-labels">
                                    <label for="accent_color"><?php _e('Cor de Destaque (Badge)', 'afiliados-pro'); ?></label>
                                    <span class="description"><?php _e('Usada em badges, preços e destaques', 'afiliados-pro'); ?></span>
                                </div>
                            </div>
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
                                    <option value="medium" <?php selected($settings['border_radius'], 'medium'); ?>><?php _e('Médio (12px)', 'afiliados-pro'); ?></option>
                                    <option value="large" <?php selected($settings['border_radius'], 'large'); ?>><?php _e('Grande (20px)', 'afiliados-pro'); ?></option>
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
                <input type="hidden" name="current_tab" value="settings">

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
     * Render Presets Tab (v1.6.0)
     */
    private function render_presets_tab() {
        $presets = self::get_presets();

        // Mensagens
        if (isset($_GET['preset-saved']) && $_GET['preset-saved'] === 'true') {
            $preset_id = isset($_GET['preset-id']) ? intval($_GET['preset-id']) : 0;
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(__('Preset salvo com sucesso! Use o shortcode: %s', 'afiliados-pro'), '<code>[pap_preset id="' . $preset_id . '"]</code>') . '</p></div>';
        }

        if (isset($_GET['preset-deleted']) && $_GET['preset-deleted'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Preset excluído com sucesso!', 'afiliados-pro') . '</p></div>';
        }

        if (isset($_GET['error'])) {
            $error = sanitize_text_field($_GET['error']);
            $error_messages = array(
                'empty_name' => __('Por favor, insira um nome para o preset.', 'afiliados-pro'),
                'invalid_id' => __('ID do preset inválido.', 'afiliados-pro'),
                'not_found' => __('Preset não encontrado.', 'afiliados-pro'),
            );
            $message = isset($error_messages[$error]) ? $error_messages[$error] : __('Ocorreu um erro.', 'afiliados-pro');
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        ?>
        <div class="tab-content">
            <p><?php _e('Salve múltiplas personalizações de aparência e use cada uma com seu próprio shortcode.', 'afiliados-pro'); ?></p>

            <!-- Formulário para salvar novo preset -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;"><?php _e('Salvar Configuração Atual como Preset', 'afiliados-pro'); ?></h3>
                <p class="description"><?php _e('As configurações de aparência e funcionalidades atuais serão salvas neste preset.', 'afiliados-pro'); ?></p>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 15px;">
                    <?php wp_nonce_field('affiliate_preset_save', 'affiliate_preset_nonce'); ?>
                    <input type="hidden" name="action" value="affiliate_preset_save">

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="preset_name"><?php _e('Nome do Preset', 'afiliados-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="preset_name" name="preset_name" class="regular-text" placeholder="<?php _e('Ex: Layout Azul Moderno', 'afiliados-pro'); ?>" required>
                                    <p class="description"><?php _e('Dê um nome descritivo para identificar este preset facilmente.', 'afiliados-pro'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <?php submit_button(__('Salvar Preset', 'afiliados-pro'), 'primary', 'submit', false); ?>
                </form>
            </div>

            <!-- Lista de presets salvos -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3 style="margin-top: 0;"><?php _e('Presets Salvos', 'afiliados-pro'); ?></h3>

                <?php if (empty($presets)) : ?>
                    <p><?php _e('Nenhum preset salvo ainda. Salve suas primeiras configurações acima!', 'afiliados-pro'); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;"><?php _e('ID', 'afiliados-pro'); ?></th>
                                <th><?php _e('Nome', 'afiliados-pro'); ?></th>
                                <th><?php _e('Shortcode', 'afiliados-pro'); ?></th>
                                <th><?php _e('Criado em', 'afiliados-pro'); ?></th>
                                <th style="width: 150px;"><?php _e('Ações', 'afiliados-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presets as $preset_id => $preset) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($preset_id); ?></strong></td>
                                    <td><?php echo esc_html($preset['name']); ?></td>
                                    <td>
                                        <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[pap_preset id="<?php echo esc_attr($preset_id); ?>"]</code>
                                        <button type="button" class="button button-small copy-shortcode" data-shortcode='[pap_preset id="<?php echo esc_attr($preset_id); ?>"]' style="margin-left: 5px;">
                                            <?php _e('Copiar', 'afiliados-pro'); ?>
                                        </button>
                                    </td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($preset['timestamp']))); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=affiliate_preset_delete&preset_id=' . $preset_id), 'delete_preset_' . $preset_id)); ?>"
                                           class="button button-small"
                                           onclick="return confirm('<?php _e('Tem certeza que deseja excluir este preset?', 'afiliados-pro'); ?>');"
                                           style="color: #b32d2e;">
                                            <?php _e('Excluir', 'afiliados-pro'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Instruções de uso -->
            <div style="background: #f0f6fc; border-left: 4px solid #0073aa; padding: 15px; margin-top: 20px;">
                <h4 style="margin-top: 0; color: #0073aa;"><?php _e('Como usar os Presets', 'afiliados-pro'); ?></h4>
                <ol style="margin-bottom: 0;">
                    <li><?php _e('Configure a aparência e funcionalidades nas abas "Aparência" e "Configurações"', 'afiliados-pro'); ?></li>
                    <li><?php _e('Volte para esta aba e salve um novo preset com um nome descritivo', 'afiliados-pro'); ?></li>
                    <li><?php _e('Use o shortcode gerado em qualquer página ou post para exibir produtos com aquele preset', 'afiliados-pro'); ?></li>
                    <li><?php _e('Você pode ter quantos presets quiser e usar diferentes em páginas diferentes!', 'afiliados-pro'); ?></li>
                </ol>
            </div>
        </div>
        <?php
    }

    /**
     * Salva as configurações do template
     * v1.5.2: Migrado para usar affiliate_pro_settings para persistência correta
     * v1.6.4: Corrigido reset de checkboxes entre abas - só atualiza campos presentes no POST
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

        // v1.6.4: Identificar qual aba está sendo salva
        $current_tab = isset($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : '';

        // v1.5.2: Obter configurações atuais do sistema unificado
        $current_settings = PAP_Settings::get_settings();

        // Mapear campos do Template Builder para PAP_Settings
        $settings = $current_settings;

        // Mapear cores
        if (isset($_POST['primary_color'])) {
            $settings['primary_color'] = sanitize_hex_color($_POST['primary_color']);
        }
        if (isset($_POST['card_background_color'])) {
            $settings['card_bg_color'] = sanitize_hex_color($_POST['card_background_color']);
        }
        if (isset($_POST['card_image_background'])) {
            $settings['card_image_background'] = sanitize_hex_color($_POST['card_image_background']);
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

        // Mapear sombra (v1.6.4: checkboxes da aba Aparência)
        if ($current_tab === 'appearance' || empty($current_tab)) {
            // Salvando da aba Aparência OU sem identificação de aba (compatibilidade)
            // Só atualiza se os campos estiverem presentes no POST
            if (isset($_POST['shadow_card']) || isset($_POST['shadow_button']) || isset($_POST['force_css'])) {
                $settings['card_shadow'] = isset($_POST['shadow_card']) ? boolval($_POST['shadow_card']) : false;
                $settings['shadow_button'] = isset($_POST['shadow_button']) ? boolval($_POST['shadow_button']) : false;
                $settings['force_css'] = isset($_POST['force_css']) ? boolval($_POST['force_css']) : false;
            }
        }
        // Se salvando APENAS da aba Configurações, manter valores atuais (já estão em $settings)

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

        // Mapear configurações funcionais (v1.6.4: checkboxes da aba Configurações)
        if ($current_tab === 'settings' || empty($current_tab)) {
            // Salvando da aba Configurações OU sem identificação de aba (compatibilidade)
            // Só atualiza se os campos estiverem presentes no POST
            if (isset($_POST['button_text']) || isset($_POST['clickable_title']) || isset($_POST['open_in_new_tab']) ||
                isset($_POST['show_store_badge']) || isset($_POST['show_price']) || isset($_POST['custom_css'])) {

                if (isset($_POST['button_text'])) {
                    $settings['button_text'] = sanitize_text_field($_POST['button_text']);
                }

                // Checkboxes: aplicar lógica padrão (ausente = false)
                $settings['title_clickable'] = isset($_POST['clickable_title']) ? boolval($_POST['clickable_title']) : false;
                $settings['open_in_new_tab'] = isset($_POST['open_in_new_tab']) ? boolval($_POST['open_in_new_tab']) : false;
                $settings['show_store_badge'] = isset($_POST['show_store_badge']) ? boolval($_POST['show_store_badge']) : false;
                $settings['show_price'] = isset($_POST['show_price']) ? boolval($_POST['show_price']) : false;

                if (isset($_POST['custom_css'])) {
                    $settings['custom_css'] = wp_strip_all_tags($_POST['custom_css']);
                }
            }
        }
        // Se salvando APENAS da aba Aparência, manter valores atuais (já estão em $settings)

        // Mapear formato de preço (campos da aba Configurações)
        if ($current_tab === 'settings' || empty($current_tab)) {
            if (isset($_POST['price_format'])) {
                $settings['price_format'] = sanitize_text_field($_POST['price_format']);
            }
            if (isset($_POST['price_text_empty'])) {
                $settings['price_placeholder'] = sanitize_text_field($_POST['price_text_empty']);
            }
        }

        // v1.5.2: Salvar no sistema unificado affiliate_pro_settings
        update_option('affiliate_pro_settings', $settings);

        // Registrar log (se debug ativo)
        pap_log('Template Builder: Configurações salvas com sucesso em affiliate_pro_settings');

        // Redirecionar com mensagem de sucesso
        wp_redirect(add_query_arg(
            array('page' => 'affiliate-template-builder', 'settings-updated' => 'true'),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Retorna as configurações do template (mescladas com defaults)
     * v1.5.2: Sincronizado com PAP_Settings para persistência correta
     * v1.5.3: Adiciona mapeamento reverso para compatibilidade com formulários antigos
     *
     * @return array
     */
    public static function get_template_settings() {
        // v1.5.2: Agora usa as configurações unificadas do PAP_Settings
        // Isso garante que o front-end e o admin sempre estejam sincronizados
        $settings = PAP_Settings::get_settings();

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
            'shadow_card' => false,
            'force_css' => false,
            'show_price' => true,
            'clickable_title' => false,
            'open_in_new_tab' => true,
            'show_store_badge' => false,
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
            PAP_URL . 'assets/css/affiliate-template.css',
            array(),
            PAP_VERSION
        );

        // Enqueue JS with jQuery dependency
        wp_enqueue_script(
            'affiliate-preview-js',
            PAP_URL . 'assets/js/template-preview.js',
            array('jquery'),
            PAP_VERSION,
            true
        );

        // Log asset loading
        pap_log('Template Builder: Assets enqueued for page ' . $hook);
    }

    /**
     * Obtém todos os presets salvos (v1.6.0)
     *
     * @return array
     */
    public static function get_presets() {
        return get_option('affiliate_pro_presets', array());
    }

    /**
     * Obtém um preset específico por ID (v1.6.0)
     *
     * @param int $preset_id
     * @return array|false
     */
    public static function get_preset_by_id($preset_id) {
        $presets = self::get_presets();
        return isset($presets[$preset_id]) ? $presets[$preset_id] : false;
    }

    /**
     * Salva um novo preset (v1.6.0)
     */
    public function save_preset() {
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
        }

        // Verificar nonce
        if (!isset($_POST['affiliate_preset_nonce']) || !wp_verify_nonce($_POST['affiliate_preset_nonce'], 'affiliate_preset_save')) {
            wp_die(__('Erro de segurança. Tente novamente.', 'afiliados-pro'));
        }

        // Nome do preset
        $preset_name = isset($_POST['preset_name']) ? sanitize_text_field($_POST['preset_name']) : '';
        if (empty($preset_name)) {
            wp_redirect(add_query_arg(
                array('page' => 'affiliate-template-builder', 'tab' => 'presets', 'error' => 'empty_name'),
                admin_url('admin.php')
            ));
            exit;
        }

        // Obter configurações atuais
        $current_settings = PAP_Settings::get_settings();

        // Obter presets existentes
        $presets = self::get_presets();

        // Gerar novo ID (incremental)
        $new_id = 1;
        if (!empty($presets)) {
            $new_id = max(array_keys($presets)) + 1;
        }

        // Criar novo preset
        $presets[$new_id] = array(
            'name' => $preset_name,
            'settings' => $current_settings,
            'timestamp' => current_time('mysql')
        );

        // Salvar no banco
        update_option('affiliate_pro_presets', $presets);

        // Log
        pap_log('Preset criado com sucesso. ID: ' . $new_id . ', Nome: ' . $preset_name);

        // Redirecionar com sucesso
        wp_redirect(add_query_arg(
            array('page' => 'affiliate-template-builder', 'tab' => 'presets', 'preset-saved' => 'true', 'preset-id' => $new_id),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Exclui um preset (v1.6.0)
     */
    public function delete_preset() {
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
        }

        // Verificar nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_preset_' . $_GET['preset_id'])) {
            wp_die(__('Erro de segurança. Tente novamente.', 'afiliados-pro'));
        }

        $preset_id = isset($_GET['preset_id']) ? intval($_GET['preset_id']) : 0;

        if ($preset_id <= 0) {
            wp_redirect(add_query_arg(
                array('page' => 'affiliate-template-builder', 'tab' => 'presets', 'error' => 'invalid_id'),
                admin_url('admin.php')
            ));
            exit;
        }

        // Obter presets
        $presets = self::get_presets();

        // Verificar se existe
        if (!isset($presets[$preset_id])) {
            wp_redirect(add_query_arg(
                array('page' => 'affiliate-template-builder', 'tab' => 'presets', 'error' => 'not_found'),
                admin_url('admin.php')
            ));
            exit;
        }

        // Remover preset
        unset($presets[$preset_id]);

        // Salvar no banco
        update_option('affiliate_pro_presets', $presets);

        // Log
        pap_log('Preset excluído com sucesso. ID: ' . $preset_id);

        // Redirecionar com sucesso
        wp_redirect(add_query_arg(
            array('page' => 'affiliate-template-builder', 'tab' => 'presets', 'preset-deleted' => 'true'),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Carrega as configurações de um preset (v1.6.0)
     *
     * @param int $preset_id
     * @return bool
     */
    public static function load_preset($preset_id) {
        $preset = self::get_preset_by_id($preset_id);

        if (!$preset || !isset($preset['settings'])) {
            return false;
        }

        // Atualizar configurações atuais com as do preset
        update_option('affiliate_pro_settings', $preset['settings']);

        return true;
    }
}
