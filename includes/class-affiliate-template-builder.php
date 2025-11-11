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
        add_action('wp_head', array($this, 'apply_template_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Registra o menu Template Builder
     */
    public function register_template_builder_menu() {
        add_submenu_page(
            'affiliate-products',
            __('Aparência', 'afiliados-pro'),
            __('Aparência', 'afiliados-pro'),
            'manage_options',
            'affiliate-template-builder',
            array($this, 'render_template_builder_page')
        );
    }

    /**
     * Renderiza a página do Template Builder
     */
    public function render_template_builder_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para acessar esta página.', 'afiliados-pro'));
        }

        $settings = self::get_template_settings();

        // Mensagem de sucesso
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Configurações salvas com sucesso!', 'afiliados-pro') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Template Builder - Aparência dos Produtos', 'afiliados-pro'); ?></h1>
            <p><?php _e('Personalize a aparência visual dos cards de produtos afiliados exibidos no front-end.', 'afiliados-pro'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('affiliate_template_save', 'affiliate_template_nonce'); ?>
                <input type="hidden" name="action" value="affiliate_template_save">

                <table class="form-table" role="presentation">
                    <tbody>
                        <!-- Cor Primária -->
                        <tr>
                            <th scope="row">
                                <label for="primary_color"><?php _e('Cor Primária', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color']); ?>" class="regular-text">
                                <p class="description"><?php _e('Cor principal usada nos títulos e elementos destacados.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Cor do Botão -->
                        <tr>
                            <th scope="row">
                                <label for="button_color"><?php _e('Cor do Botão', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="color" id="button_color" name="button_color" value="<?php echo esc_attr($settings['button_color']); ?>" class="regular-text">
                                <p class="description"><?php _e('Cor de fundo dos botões de ação.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Cor Secundária (Gradiente) - v1.4.1 -->
                        <tr>
                            <th scope="row">
                                <label for="gradient_color"><?php _e('Cor Secundária (Gradiente)', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="color" id="gradient_color" name="gradient_color" value="<?php echo esc_attr($settings['gradient_color']); ?>" class="regular-text">
                                <p class="description"><?php _e('Usada apenas quando o estilo de botão é "Gradiente".', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Estilo do Card -->
                        <tr>
                            <th scope="row">
                                <label for="card_style"><?php _e('Estilo do Card', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <select id="card_style" name="card_style" class="regular-text">
                                    <option value="modern" <?php selected($settings['card_style'], 'modern'); ?>><?php _e('Moderno', 'afiliados-pro'); ?></option>
                                    <option value="classic" <?php selected($settings['card_style'], 'classic'); ?>><?php _e('Clássico', 'afiliados-pro'); ?></option>
                                    <option value="minimal" <?php selected($settings['card_style'], 'minimal'); ?>><?php _e('Minimalista', 'afiliados-pro'); ?></option>
                                    <option value="cards" <?php selected($settings['card_style'], 'cards'); ?>><?php _e('Cards', 'afiliados-pro'); ?></option>
                                </select>
                                <p class="description"><?php _e('Define o estilo visual geral dos cards de produtos.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Estilo do Botão -->
                        <tr>
                            <th scope="row">
                                <label for="button_style"><?php _e('Estilo do Botão', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <select id="button_style" name="button_style" class="regular-text">
                                    <option value="filled" <?php selected($settings['button_style'], 'filled'); ?>><?php _e('Preenchido', 'afiliados-pro'); ?></option>
                                    <option value="outline" <?php selected($settings['button_style'], 'outline'); ?>><?php _e('Contorno', 'afiliados-pro'); ?></option>
                                    <option value="gradient" <?php selected($settings['button_style'], 'gradient'); ?>><?php _e('Gradiente', 'afiliados-pro'); ?></option>
                                </select>
                                <p class="description"><?php _e('Aparência dos botões de ação nos cards.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Raio da Borda -->
                        <tr>
                            <th scope="row">
                                <label for="border_radius"><?php _e('Raio da Borda', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <select id="border_radius" name="border_radius" class="regular-text">
                                    <option value="none" <?php selected($settings['border_radius'], 'none'); ?>><?php _e('Nenhum (0px)', 'afiliados-pro'); ?></option>
                                    <option value="small" <?php selected($settings['border_radius'], 'small'); ?>><?php _e('Pequeno (4px)', 'afiliados-pro'); ?></option>
                                    <option value="medium" <?php selected($settings['border_radius'], 'medium'); ?>><?php _e('Médio (8px)', 'afiliados-pro'); ?></option>
                                    <option value="large" <?php selected($settings['border_radius'], 'large'); ?>><?php _e('Grande (16px)', 'afiliados-pro'); ?></option>
                                </select>
                                <p class="description"><?php _e('Arredondamento dos cantos dos cards e botões.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Sombra do Card (v1.4.0) -->
                        <tr>
                            <th scope="row">
                                <label for="shadow_card"><?php _e('Sombra do Card', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="shadow_card" name="shadow_card" value="1" <?php checked($settings['shadow_card'], true); ?>>
                                    <?php _e('Ativar sombra nos cards de produtos', 'afiliados-pro'); ?>
                                </label>
                                <p class="description"><?php _e('Ativa ou desativa sombra no card do produto independentemente.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Sombra do Botão (v1.4.0) -->
                        <tr>
                            <th scope="row">
                                <label for="shadow_button"><?php _e('Sombra do Botão', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="shadow_button" name="shadow_button" value="1" <?php checked($settings['shadow_button'], true); ?>>
                                    <?php _e('Ativar sombra nos botões "Ver Produto"', 'afiliados-pro'); ?>
                                </label>
                                <p class="description"><?php _e('Ativa ou desativa sombra no botão "Ver Produto" independentemente.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Layout Padrão -->
                        <tr>
                            <th scope="row">
                                <label for="layout_default"><?php _e('Layout Padrão', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <select id="layout_default" name="layout_default" class="regular-text">
                                    <option value="grid" <?php selected($settings['layout_default'], 'grid'); ?>><?php _e('Grade', 'afiliados-pro'); ?></option>
                                    <option value="list" <?php selected($settings['layout_default'], 'list'); ?>><?php _e('Lista', 'afiliados-pro'); ?></option>
                                    <option value="carousel" <?php selected($settings['layout_default'], 'carousel'); ?>><?php _e('Carrossel', 'afiliados-pro'); ?></option>
                                    <option value="masonry" <?php selected($settings['layout_default'], 'masonry'); ?>><?php _e('Masonry', 'afiliados-pro'); ?></option>
                                </select>
                                <p class="description"><?php _e('Layout padrão para shortcodes quando não especificado.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Número de Colunas -->
                        <tr>
                            <th scope="row">
                                <label for="columns"><?php _e('Número de Colunas', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="columns" name="columns" value="<?php echo esc_attr($settings['columns']); ?>" min="2" max="4" class="small-text">
                                <p class="description"><?php _e('Quantidade de colunas no layout de grade (entre 2 e 4).', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Espaçamento entre Cards (v1.4.2) -->
                        <tr>
                            <th scope="row">
                                <label for="card_gap"><?php _e('Espaçamento entre Cards', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="card_gap" name="card_gap" min="0" max="100" value="<?php echo esc_attr($settings['card_gap']); ?>" class="small-text"> px
                                <p class="description"><?php _e('Define o espaço horizontal e vertical entre os cards de produtos.', 'afiliados-pro'); ?></p>
                            </td>
                        </tr>

                        <!-- Forçar CSS do Template Builder -->
                        <tr>
                            <th scope="row">
                                <label for="force_css"><?php _e('Forçar CSS do Template Builder', 'afiliados-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="force_css" name="force_css" value="1" <?php checked($settings['force_css'], true); ?>>
                                    <?php _e('Ativar esta opção para aplicar as cores e estilos do Template Builder com prioridade sobre o tema ativo.', 'afiliados-pro'); ?>
                                </label>
                                <p class="description" style="color: #d63638; font-weight: 500;">
                                    ⚠️ <?php _e('Use esta opção apenas se o tema estiver sobrescrevendo as cores do Template Builder.', 'afiliados-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Salvar Configurações', 'afiliados-pro'), 'primary', 'submit'); ?>
            </form>

            <!-- Preview Section (v1.4.3 - Manual Control) -->
            <div class="card" style="margin-top: 30px;">
                <h2><?php _e('Pré-visualização ao Vivo', 'afiliados-pro'); ?></h2>
                <p class="description">
                    <?php _e('As alterações são aplicadas automaticamente nesta visualização. Clique em <strong>Gerar Pré-visualização</strong> para atualizar a exibição conforme suas configurações.', 'afiliados-pro'); ?>
                </p>

                <button id="generate-preview" class="button button-primary" type="button" style="margin-bottom: 10px; margin-top: 10px;">
                    <?php _e('Gerar Pré-visualização', 'afiliados-pro'); ?>
                </button>

                <div id="affiliate-preview-container" style="border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 4px;">
                    <iframe id="affiliate-preview-frame"
                        src="<?php echo esc_url(admin_url('admin.php?page=affiliate-preview')); ?>"
                        style="width: 100%; height: 500px; border: 0; display: block;"
                        title="<?php esc_attr_e('Pré-visualização do Template', 'afiliados-pro'); ?>">
                    </iframe>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Salva as configurações do template
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

        // Sanitizar e validar dados
        $settings = array();

        // Cores
        $settings['primary_color'] = isset($_POST['primary_color']) ? sanitize_hex_color($_POST['primary_color']) : '#283593';
        $settings['button_color'] = isset($_POST['button_color']) ? sanitize_hex_color($_POST['button_color']) : '#ffa70a';
        $settings['gradient_color'] = isset($_POST['gradient_color']) ? sanitize_hex_color($_POST['gradient_color']) : '#025C95'; // v1.4.1

        // Estilos
        $allowed_card_styles = array('modern', 'classic', 'minimal', 'cards');
        $settings['card_style'] = isset($_POST['card_style']) && in_array($_POST['card_style'], $allowed_card_styles)
            ? sanitize_text_field($_POST['card_style'])
            : 'modern';

        $allowed_button_styles = array('filled', 'outline', 'gradient');
        $settings['button_style'] = isset($_POST['button_style']) && in_array($_POST['button_style'], $allowed_button_styles)
            ? sanitize_text_field($_POST['button_style'])
            : 'filled';

        // Borda
        $allowed_border_radius = array('none', 'small', 'medium', 'large');
        $settings['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], $allowed_border_radius)
            ? sanitize_text_field($_POST['border_radius'])
            : 'medium';

        // Sombra separada para card e botão (v1.4.0)
        $settings['shadow_card'] = isset($_POST['shadow_card']) ? boolval($_POST['shadow_card']) : false;
        $settings['shadow_button'] = isset($_POST['shadow_button']) ? boolval($_POST['shadow_button']) : false;

        // Layout
        $allowed_layouts = array('grid', 'list', 'carousel', 'masonry');
        $settings['layout_default'] = isset($_POST['layout_default']) && in_array($_POST['layout_default'], $allowed_layouts)
            ? sanitize_text_field($_POST['layout_default'])
            : 'grid';

        // Colunas
        $columns = isset($_POST['columns']) ? absint($_POST['columns']) : 3;
        $settings['columns'] = max(2, min(4, $columns)); // Entre 2 e 4

        // Gap entre cards (v1.4.2)
        $card_gap = isset($_POST['card_gap']) ? absint($_POST['card_gap']) : 20;
        $settings['card_gap'] = max(0, min(100, $card_gap)); // Entre 0 e 100

        // Forçar CSS
        $settings['force_css'] = isset($_POST['force_css']) ? boolval($_POST['force_css']) : false;

        // Salvar no banco de dados
        update_option($this->option_name, $settings);

        // Registrar log (se debug ativo)
        affiliate_pro_log('Template Builder: Configurações salvas com sucesso');

        // Redirecionar com mensagem de sucesso
        wp_redirect(add_query_arg(
            array('page' => 'affiliate-template-builder', 'settings-updated' => 'true'),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Retorna as configurações do template (mescladas com defaults)
     *
     * @return array
     */
    public static function get_template_settings() {
        $defaults = array(
            'primary_color' => '#283593',
            'button_color' => '#ffa70a',
            'gradient_color' => '#025C95', // v1.4.1
            'card_style' => 'modern',
            'button_style' => 'filled',
            'border_radius' => 'medium',
            'shadow_card' => true, // v1.4.0
            'shadow_button' => true, // v1.4.0
            'layout_default' => 'grid',
            'columns' => 3,
            'card_gap' => 20, // v1.4.2
            'force_css' => false,
        );

        $settings = get_option('affiliate_template_settings', array());
        return wp_parse_args($settings, $defaults);
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
        /* Afiliados Pro - Template Builder v1.4.0 */

        :root {
            --affiliate-template-primary: {$settings['primary_color']};
            --affiliate-template-button: {$settings['button_color']};
            --affiliate-template-radius: {$border_radius};
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
