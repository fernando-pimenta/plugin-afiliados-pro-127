<?php
/**
 * Template da página de Aparência e Configurações
 *
 * @package Affiliate_Pro
 * @since 1.2
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = Affiliate_Pro_Settings::get_settings();

// Mensagens de feedback
if (isset($_GET['settings-updated'])) {
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Configurações salvas com sucesso!', 'afiliados-pro') . '</p></div>';
}

if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Configurações restauradas para os padrões!', 'afiliados-pro') . '</p></div>';
}
?>

<div class="wrap affiliate-pro-settings">
    <h1><?php _e('Aparência e Configurações', 'afiliados-pro'); ?></h1>
    <p><?php _e('Personalize a aparência dos cards de produtos afiliados e configure o comportamento do plugin.', 'afiliados-pro'); ?></p>

    <form method="post" action="options.php" class="affiliate-settings-form">
        <?php settings_fields('affiliate_pro_settings_group'); ?>

        <!-- Seção 1: Identidade Visual dos Cards -->
        <div class="affiliate-settings-section">
            <h2 class="section-title" data-section="visual">
                <span class="dashicons dashicons-art"></span>
                <?php _e('Identidade Visual dos Cards', 'afiliados-pro'); ?>
                <span class="toggle-indicator"></span>
            </h2>
            <div class="section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Cor Primária', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[primary_color]" value="<?php echo esc_attr($settings['primary_color']); ?>" class="color-picker" data-default-color="#283593">
                            <p class="description"><?php _e('Cor principal do tema', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor Secundária', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[secondary_color]" value="<?php echo esc_attr($settings['secondary_color']); ?>" class="color-picker" data-default-color="#3949ab">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Destaque', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[accent_color]" value="<?php echo esc_attr($settings['accent_color']); ?>" class="color-picker" data-default-color="#ffa70a">
                            <p class="description"><?php _e('Usada para preços e elementos de destaque', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Fundo do Card', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[card_bg_color]" value="<?php echo esc_attr($settings['card_bg_color']); ?>" class="color-picker" data-default-color="#ffffff">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor do Texto', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[text_color]" value="<?php echo esc_attr($settings['text_color']); ?>" class="color-picker" data-default-color="#1a1a1a">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Arredondamento das Bordas', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="range" name="affiliate_pro_settings[card_border_radius]" min="0" max="30" value="<?php echo esc_attr($settings['card_border_radius']); ?>" class="range-slider" data-output="border-radius-value">
                            <span class="range-value" id="border-radius-value"><?php echo esc_html($settings['card_border_radius']); ?>px</span>
                            <p class="description"><?php _e('0 = sem arredondamento, 30 = muito arredondado', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Sombra nos Cards', 'afiliados-pro'); ?></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" name="affiliate_pro_settings[card_shadow]" value="1" <?php checked($settings['card_shadow'], true); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Adiciona sombra aos cards para dar profundidade', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Seção 2: Botão de Ação -->
        <div class="affiliate-settings-section">
            <h2 class="section-title" data-section="button">
                <span class="dashicons dashicons-button"></span>
                <?php _e('Botão de Ação', 'afiliados-pro'); ?>
                <span class="toggle-indicator"></span>
            </h2>
            <div class="section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Texto do Botão', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[button_text]" value="<?php echo esc_attr($settings['button_text']); ?>" class="regular-text">
                            <p class="description"><?php _e('Texto exibido no botão quando há link disponível', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Estilo do Botão', 'afiliados-pro'); ?></th>
                        <td>
                            <select name="affiliate_pro_settings[button_style]">
                                <option value="gradient" <?php selected($settings['button_style'] ?? 'gradient', 'gradient'); ?>><?php _e('Gradiente', 'afiliados-pro'); ?></option>
                                <option value="flat" <?php selected($settings['button_style'] ?? 'gradient', 'flat'); ?>><?php _e('Preenchido', 'afiliados-pro'); ?></option>
                                <option value="outline" <?php selected($settings['button_style'] ?? 'gradient', 'outline'); ?>><?php _e('Contorno', 'afiliados-pro'); ?></option>
                            </select>
                            <p class="description"><?php _e('Define o estilo visual do botão principal nos cards de produto', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor Inicial do Gradiente', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[button_color_start]" value="<?php echo esc_attr($settings['button_color_start']); ?>" class="color-picker" data-default-color="#6a82fb">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor Final do Gradiente', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[button_color_end]" value="<?php echo esc_attr($settings['button_color_end']); ?>" class="color-picker" data-default-color="#fc5c7d">
                            <p class="description"><?php _e('O botão terá um gradiente entre essas duas cores', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Texto Quando Indisponível', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[button_text_disabled]" value="<?php echo esc_attr($settings['button_text_disabled']); ?>" class="regular-text">
                            <p class="description"><?php _e('Texto exibido quando não há link de afiliado', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Seção 3: Layout da Grade -->
        <div class="affiliate-settings-section">
            <h2 class="section-title" data-section="layout">
                <span class="dashicons dashicons-grid-view"></span>
                <?php _e('Layout da Grade', 'afiliados-pro'); ?>
                <span class="toggle-indicator"></span>
            </h2>
            <div class="section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Layout Padrão', 'afiliados-pro'); ?></th>
                        <td>
                            <select name="affiliate_pro_settings[default_layout]">
                                <option value="grid" <?php selected($settings['default_layout'], 'grid'); ?>><?php _e('Grade', 'afiliados-pro'); ?></option>
                                <option value="list" <?php selected($settings['default_layout'], 'list'); ?>><?php _e('Lista', 'afiliados-pro'); ?></option>
                            </select>
                            <p class="description"><?php _e('Como os produtos serão exibidos por padrão', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Colunas Padrão', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="range" name="affiliate_pro_settings[default_columns]" min="2" max="4" value="<?php echo esc_attr($settings['default_columns']); ?>" class="range-slider" data-output="columns-value">
                            <span class="range-value" id="columns-value"><?php echo esc_html($settings['default_columns']); ?></span>
                            <p class="description"><?php _e('Número de colunas na grade (2-4)', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Espaçamento entre Cards', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="range" name="affiliate_pro_settings[card_gap]" min="0" max="40" value="<?php echo esc_attr($settings['card_gap']); ?>" class="range-slider" data-output="gap-value">
                            <span class="range-value" id="gap-value"><?php echo esc_html($settings['card_gap']); ?>px</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Seção 4: Exibição de Preços -->
        <div class="affiliate-settings-section">
            <h2 class="section-title" data-section="price">
                <span class="dashicons dashicons-tag"></span>
                <?php _e('Exibição de Preços', 'afiliados-pro'); ?>
                <span class="toggle-indicator"></span>
            </h2>
            <div class="section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Formato do Preço', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[price_format]" value="<?php echo esc_attr($settings['price_format']); ?>" class="regular-text">
                            <p class="description"><?php _e('Use {valor} onde o preço deve aparecer. Exemplo: R$ {valor}', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Texto Quando Sem Preço', 'afiliados-pro'); ?></th>
                        <td>
                            <input type="text" name="affiliate_pro_settings[price_placeholder]" value="<?php echo esc_attr($settings['price_placeholder']); ?>" class="regular-text">
                            <p class="description"><?php _e('Texto exibido quando o produto não tem preço definido', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Seção 5: Outros Ajustes -->
        <div class="affiliate-settings-section">
            <h2 class="section-title" data-section="misc">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php _e('Outros Ajustes', 'afiliados-pro'); ?>
                <span class="toggle-indicator"></span>
            </h2>
            <div class="section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Título Clicável', 'afiliados-pro'); ?></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" name="affiliate_pro_settings[title_clickable]" value="1" <?php checked($settings['title_clickable'], true); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Permitir que o título do produto seja clicável', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Abrir em Nova Aba', 'afiliados-pro'); ?></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" name="affiliate_pro_settings[open_in_new_tab]" value="1" <?php checked($settings['open_in_new_tab'], true); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Links de afiliado abrem em nova aba', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Mostrar Badge da Loja', 'afiliados-pro'); ?></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" name="affiliate_pro_settings[show_store_badge]" value="1" <?php checked($settings['show_store_badge'], true); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Exibe o nome da loja (Shopee, Amazon, etc.) na imagem do produto', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('CSS Customizado', 'afiliados-pro'); ?></th>
                        <td>
                            <textarea name="affiliate_pro_settings[custom_css]" rows="8" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                            <p class="description"><?php _e('Adicione CSS personalizado para ajustes finos (opcional)', 'afiliados-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- FUTURE: Template Builder integration point -->
        <!-- Futuramente aqui será adicionado o construtor visual de templates -->

        <p class="submit">
            <?php submit_button(__('💾 Salvar Alterações', 'afiliados-pro'), 'primary', 'submit', false); ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=affiliate_pro_reset_settings'), 'affiliate_pro_reset_settings')); ?>"
               class="button button-secondary"
               onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja restaurar todas as configurações para os valores padrão?', 'afiliados-pro'); ?>');">
                <?php _e('🔄 Restaurar Padrões', 'afiliados-pro'); ?>
            </a>
        </p>
    </form>
</div>
