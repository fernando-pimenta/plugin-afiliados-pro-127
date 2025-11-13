<?php
/**
 * Classe responsável pelos shortcodes do plugin
 * v1.7.1: Refatoração gradual - PAP_Shortcodes é agora a classe principal
 *
 * @package PAP
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal de Shortcodes com prefixo padronizado PAP
 * v1.7.1: Promovida de espelho para classe principal
 *
 * @package PAP
 * @since 1.7.1
 */
class PAP_Shortcodes {

    /**
     * Instância única (Singleton)
     *
     * @var PAP_Shortcodes
     */
    private static $instance = null;

    /**
     * Obtém a instância única
     *
     * @return PAP_Shortcodes
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
     * v1.7.0: Adicionados shortcodes com prefixo pap_ (compatibilidade total mantida)
     */
    private function init_hooks() {
        // Shortcodes originais (mantidos para compatibilidade)
        add_shortcode('affiliate_product', array($this, 'single_product_shortcode'));
        add_shortcode('affiliate_products', array($this, 'products_grid_shortcode'));
        add_shortcode('afiliados_pro', array($this, 'preset_shortcode')); // v1.6.0

        // v1.7.0: Novos shortcodes com prefixo padronizado pap_ (Plugin Afiliados Pro)
        add_shortcode('pap_product', array($this, 'single_product_shortcode'));
        add_shortcode('pap_products', array($this, 'products_grid_shortcode'));
        add_shortcode('pap_preset', array($this, 'preset_shortcode'));
    }

    /**
     * Shortcode para exibir um único produto
     *
     * @param array $atts
     * @return string
     */
    public function single_product_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts);

        $post_id = intval($atts['id']);
        if (!$post_id) {
            return '<p>' . __('ID do produto não informado.', 'afiliados-pro') . '</p>';
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'affiliate_product') {
            return '<p>' . __('Produto não encontrado.', 'afiliados-pro') . '</p>';
        }

        return $this->render_product_card($post);
    }

    /**
     * Shortcode para exibir uma grade de produtos
     *
     * @param array $atts
     * @return string
     */
    public function products_grid_shortcode($atts) {
        $settings = Affiliate_Pro_Settings::get_settings();

        // Obter configurações do Template Builder para fallback
        $builder_settings = Affiliate_Template_Builder::get_template_settings();

        $atts = shortcode_atts(array(
            'limit' => 6,
            'category' => '',
            'layout' => '', // Vazio para permitir fallback
            'columns' => '', // Vazio para permitir fallback
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts);

        // Aplicar fallback do Template Builder se não especificado
        if (empty($atts['layout'])) {
            $atts['layout'] = !empty($builder_settings['layout_default']) ? $builder_settings['layout_default'] : $settings['default_layout'];
        }

        if (empty($atts['columns'])) {
            $atts['columns'] = !empty($builder_settings['columns']) ? $builder_settings['columns'] : $settings['default_columns'];
        }

        // Whitelist para orderby (prevenir SQL injection)
        $allowed_orderby = array('date', 'title', 'rand', 'menu_order', 'ID', 'modified');
        if (!in_array($atts['orderby'], $allowed_orderby)) {
            $atts['orderby'] = 'date';
        }

        // Whitelist para order
        $allowed_order = array('ASC', 'DESC');
        $atts['order'] = strtoupper($atts['order']);
        if (!in_array($atts['order'], $allowed_order)) {
            $atts['order'] = 'DESC';
        }

        $args = array(
            'post_type' => 'affiliate_product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );

        // Suporte a múltiplas categorias separadas por vírgula
        if (!empty($atts['category'])) {
            $categories = array_map('trim', explode(',', $atts['category']));
            $categories = array_map('sanitize_title', $categories);

            // Verificar se as categorias existem
            $valid_categories = array();
            foreach ($categories as $category_slug) {
                if (term_exists($category_slug, 'affiliate_category')) {
                    $valid_categories[] = $category_slug;
                }
            }

            // Só adicionar tax_query se houver categorias válidas
            if (!empty($valid_categories)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'affiliate_category',
                        'field' => 'slug',
                        'terms' => $valid_categories
                    )
                );
            }
        }

        $query = new WP_Query($args);
        $output = '';

        if ($query->have_posts()) {
            $grid_class = 'affiliate-products-grid';
            if ($atts['layout'] === 'list') {
                $grid_class .= ' layout-list';
            }

            $output .= '<div class="' . esc_attr($grid_class) . '" data-columns="' . esc_attr($atts['columns']) . '">';
            while ($query->have_posts()) {
                $query->the_post();
                $output .= $this->render_product_card(get_post());
            }
            $output .= '</div>';
            wp_reset_postdata();
        } else {
            $output = '<p>' . __('Nenhum produto encontrado.', 'afiliados-pro') . '</p>';
        }

        return $output;
    }

    /**
     * Shortcode para exibir produtos com preset personalizado (v1.6.0)
     * v1.6.5: Corrigido filtro e passagem de parâmetros
     * v1.6.6: Corrigida prioridade de layout/columns (preset > Template Builder)
     *
     * @param array $atts
     * @return string
     */
    public function preset_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'limit' => 6,
            'category' => '',
            'layout' => '',
            'columns' => '',
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts);

        $preset_id = intval($atts['id']);

        // Verificar se o preset existe
        if ($preset_id <= 0) {
            return '<p>' . __('ID do preset não informado. Use: [afiliados_pro id="1"]', 'afiliados-pro') . '</p>';
        }

        $preset = Affiliate_Template_Builder::get_preset_by_id($preset_id);

        if (!$preset || !isset($preset['settings'])) {
            return '<p>' . sprintf(__('Preset #%d não encontrado.', 'afiliados-pro'), $preset_id) . '</p>';
        }

        // Criar callback específico para este preset
        $filter_callback = function($value) use ($preset) {
            return $preset['settings'];
        };

        // Aplicar configurações do preset temporariamente
        add_filter('option_affiliate_pro_settings', $filter_callback, 99);

        // Renderizar produtos com as configurações do preset
        // Passar atts como string formatada para o shortcode interno
        $shortcode_atts = array(
            'limit' => $atts['limit'],
            'category' => $atts['category'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );

        // Adicionar layout e columns (prioridade: shortcode > preset > fallback)
        if (!empty($atts['layout'])) {
            $shortcode_atts['layout'] = $atts['layout'];
        } elseif (!empty($preset['settings']['default_layout'])) {
            $shortcode_atts['layout'] = $preset['settings']['default_layout'];
        }

        if (!empty($atts['columns'])) {
            $shortcode_atts['columns'] = $atts['columns'];
        } elseif (!empty($preset['settings']['default_columns'])) {
            $shortcode_atts['columns'] = $preset['settings']['default_columns'];
        }

        $output = $this->products_grid_shortcode($shortcode_atts);

        // Remover APENAS o filtro que adicionamos
        remove_filter('option_affiliate_pro_settings', $filter_callback, 99);

        return $output;
    }

    /**
     * Renderiza o card de um produto
     *
     * @param WP_Post $post
     * @return string
     */
    private function render_product_card($post) {
        $settings = Affiliate_Pro_Settings::get_settings();

        $price = get_post_meta($post->ID, '_affiliate_price', true);
        $link = get_post_meta($post->ID, '_affiliate_link', true);
        $image_url = get_post_meta($post->ID, '_affiliate_image_url', true);

        // Determinar imagem a usar
        $image = '';
        if (has_post_thumbnail($post->ID)) {
            $image = get_the_post_thumbnail($post->ID, 'medium');
        } elseif (!empty($image_url)) {
            $image = '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($post->post_title) . '" />';
        }

        $excerpt = wp_trim_words($post->post_content, 20, '...');

        // Formatar preço
        if (!empty($price)) {
            $price_formatted = str_replace('{valor}', number_format(floatval($price), 2, ',', '.'), $settings['price_format']);
        } else {
            $price_formatted = $settings['price_placeholder'];
        }

        // Atributos do link
        $link_attrs = '';
        if ($settings['open_in_new_tab']) {
            $link_attrs .= ' target="_blank" rel="nofollow noopener"';
        }

        // Badge da loja (se ativado)
        $store_badge = '';
        if ($settings['show_store_badge'] && !empty($link)) {
            $store_name = $this->get_store_name_from_url($link);
            if ($store_name) {
                $store_badge = '<span class="store-badge">' . esc_html($store_name) . '</span>';
            }
        }

        // Texto do botão
        $button_text = !empty($link) ? $settings['button_text'] : $settings['button_text_disabled'];

        // v1.5.5: Determinar classe do botão baseada no estilo
        $button_style = $settings['button_style'] ?? 'gradient';
        $button_class = 'product-button affiliate-btn-' . esc_attr($button_style);

        // v1.5.6: Variáveis CSS inline para cores do botão
        $button_color_start = $settings['button_color_start'] ?? '#6a82fb';
        $button_color_end = $settings['button_color_end'] ?? '#fc5c7d';
        $button_text_color = $settings['button_text_color'] ?? '#ffffff';
        $price_color = $settings['price_color'] ?? '#111111';
        $card_image_background = $settings['card_image_background'] ?? '#f9f9f9';
        $card_bg_color = $settings['card_bg_color'] ?? '#ffffff';

        // v1.5.8.3: Variáveis CSS para card e botão
        $card_inline_style = sprintf(
            'style="--affiliate-card-bg: %s; --affiliate-image-bg: %s; --affiliate-price-color: %s;"',
            esc_attr($card_bg_color),
            esc_attr($card_image_background),
            esc_attr($price_color)
        );

        $button_inline_style = sprintf(
            'style="--button-color-start: %s; --button-color-end: %s; --button-text-color: %s;"',
            esc_attr($button_color_start),
            esc_attr($button_color_end),
            esc_attr($button_text_color)
        );

        ob_start();
        ?>
        <div class="affiliate-product-card" <?php echo $card_inline_style; ?>>
            <?php if ($image) : ?>
                <div class="product-image">
                    <?php echo $image; ?>
                    <?php echo $store_badge; ?>
                </div>
            <?php endif; ?>
            <div class="product-content">
                <?php if ($settings['title_clickable'] && !empty($link)) : ?>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url($link); ?>"<?php echo $link_attrs; ?>
                           data-aff-id="<?php echo esc_attr($post->ID); ?>"
                           data-source="title">
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                    </h3>
                <?php else : ?>
                    <h3 class="product-title"
                        data-aff-id="<?php echo esc_attr($post->ID); ?>"
                        data-source="title">
                        <?php echo esc_html($post->post_title); ?>
                    </h3>
                <?php endif; ?>

                <?php if ($excerpt) : ?>
                    <p class="product-excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>

                <?php if (!empty($settings['show_price'])) : ?>
                    <p class="product-price"><?php echo esc_html($price_formatted); ?></p>
                <?php endif; ?>

                <?php if (!empty($link)) : ?>
                    <a href="<?php echo esc_url($link); ?>"
                       class="<?php echo esc_attr($button_class); ?>"<?php echo $link_attrs; ?>
                       <?php echo $button_inline_style; ?>
                       data-aff-id="<?php echo esc_attr($post->ID); ?>"
                       data-source="button">
                        <?php echo esc_html($button_text); ?>
                    </a>
                <?php else : ?>
                    <span class="<?php echo esc_attr($button_class); ?> product-button-disabled" <?php echo $button_inline_style; ?>>
                        <?php echo esc_html($button_text); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Extrai o nome da loja a partir da URL
     *
     * @param string $url
     * @return string|false
     */
    private function get_store_name_from_url($url) {
        $stores = array(
            'shopee.com' => 'Shopee',
            'shp.ee' => 'Shopee',
            'amazon.com' => 'Amazon',
            'amzn.to' => 'Amazon',
            'mercadolivre.com' => 'Mercado Livre',
            'magazineluiza.com' => 'Magazine Luiza',
            'americanas.com' => 'Americanas',
            'aliexpress.com' => 'AliExpress',
            'submarino.com' => 'Submarino'
        );

        foreach ($stores as $domain => $name) {
            if (strpos($url, $domain) !== false) {
                return $name;
            }
        }

        return false;
    }
}

/**
 * Classe de compatibilidade com prefixo legado (v1.7.1)
 * Mantida para retrocompatibilidade: herda todos os métodos de PAP_Shortcodes
 *
 * AVISO DE DEPRECAÇÃO (v1.7.3):
 * Esta classe está obsoleta e será removida em versões futuras.
 * Use PAP_Shortcodes::get_instance() ao invés de Affiliate_Pro_Shortcodes::get_instance()
 *
 * @package Affiliate_Pro
 * @since 1.0
 * @deprecated 1.7.3 Use PAP_Shortcodes ao invés. Será removida na v2.0.0
 */
class Affiliate_Pro_Shortcodes extends PAP_Shortcodes {
    /**
     * Herança completa para compatibilidade com código legado
     *
     * @deprecated 1.7.3
     */
}
