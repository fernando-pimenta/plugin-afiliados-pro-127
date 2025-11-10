<?php
/**
 * Classe responsável pelos shortcodes do plugin
 *
 * @package Affiliate_Pro
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Affiliate_Pro_Shortcodes {

    /**
     * Instância única (Singleton)
     *
     * @var Affiliate_Pro_Shortcodes
     */
    private static $instance = null;

    /**
     * Obtém a instância única
     *
     * @return Affiliate_Pro_Shortcodes
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
        add_shortcode('affiliate_product', array($this, 'single_product_shortcode'));
        add_shortcode('affiliate_products', array($this, 'products_grid_shortcode'));
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

        $atts = shortcode_atts(array(
            'limit' => 6,
            'category' => '',
            'layout' => $settings['default_layout'],
            'columns' => $settings['default_columns']
        ), $atts);

        $args = array(
            'post_type' => 'affiliate_product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish'
        );

        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'affiliate_category',
                    'field' => 'slug',
                    'terms' => sanitize_title($atts['category'])
                )
            );
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

        ob_start();
        ?>
        <div class="affiliate-product-card">
            <?php if ($image) : ?>
                <div class="product-image">
                    <?php echo $image; ?>
                    <?php echo $store_badge; ?>
                </div>
            <?php endif; ?>
            <div class="product-content">
                <?php if ($settings['title_clickable'] && !empty($link)) : ?>
                    <h3 class="product-title">
                        <a href="<?php echo esc_url($link); ?>"<?php echo $link_attrs; ?>>
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                    </h3>
                <?php else : ?>
                    <h3 class="product-title"><?php echo esc_html($post->post_title); ?></h3>
                <?php endif; ?>

                <p class="product-price"><?php echo esc_html($price_formatted); ?></p>

                <?php if ($excerpt) : ?>
                    <p class="product-excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>

                <?php if (!empty($link)) : ?>
                    <a href="<?php echo esc_url($link); ?>" class="product-button"<?php echo $link_attrs; ?>>
                        <?php echo esc_html($button_text); ?>
                    </a>
                <?php else : ?>
                    <span class="product-button product-button-disabled">
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
