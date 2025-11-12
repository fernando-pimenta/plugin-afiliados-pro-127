<?php
/**
 * Afiliados Pro - Preview Template
 *
 * Completely standalone preview template with v1.5.4 button style support
 *
 * @package AfiliadorsPro
 * @version 1.5.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get settings (should be passed from parent handler)
if (!isset($settings)) {
    $settings = Affiliate_Template_Builder::get_template_settings();
}

// v1.5.3: Fallbacks para compatibilidade com chaves antigas
if (!isset($settings['accent_color']) && isset($settings['highlight_color'])) {
    $settings['accent_color'] = $settings['highlight_color'];
}
if (!isset($settings['card_bg_color']) && isset($settings['card_background_color'])) {
    $settings['card_bg_color'] = $settings['card_background_color'];
}
if (!isset($settings['price_placeholder']) && isset($settings['price_text_empty'])) {
    $settings['price_placeholder'] = $settings['price_text_empty'];
}

// Extract settings with default values (using null coalescing operator)
$primary_color = $settings['primary_color'] ?? '#283593';
$button_color = $settings['button_color_start'] ?? ($settings['accent_color'] ?? '#ffa70a');
$gradient_color = $settings['button_color_end'] ?? ($settings['secondary_color'] ?? '#fc5c7d');
$accent_color = $settings['accent_color'] ?? '#ffa70a';
$card_bg_color = $settings['card_bg_color'] ?? '#ffffff';
$text_color = $settings['text_color'] ?? '#1a1a1a';
$card_border_radius = $settings['card_border_radius'] ?? 12;
$card_shadow = $settings['card_shadow'] ?? true;
$card_gap = $settings['card_gap'] ?? 20;

// Functional settings
$button_text = $settings['button_text'] ?? 'Ver oferta';
$title_clickable = $settings['title_clickable'] ?? true;
$open_in_new_tab = $settings['open_in_new_tab'] ?? true;
$show_store_badge = $settings['show_store_badge'] ?? true;
$price_format = $settings['price_format'] ?? 'R$ {valor}';
$price_placeholder = $settings['price_placeholder'] ?? 'Consulte o preço';

// Legacy settings (for old installations)
$button_style = $settings['button_style'] ?? 'gradient';
$show_price = $settings['show_price'] ?? true;

// Convert border radius to px
$border_radius = $card_border_radius . 'px';

// Link target
$link_target = $open_in_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';

// Important flag (legacy)
$important = '';

// Local placeholder image
$placeholder_img = AFFILIATE_PRO_PLUGIN_URL . 'assets/img/placeholder.svg';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Preview - Afiliados Pro v1.5.4</title>
<style>
/* Reset básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    padding: 20px;
    background: #f7f7f7;
}

/* Preview Header */
.preview-header {
    text-align: center;
    background: #2271b1;
    color: #fff;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 0.9em;
    margin-bottom: 20px;
    font-weight: 500;
}

/* Container dos cards */
.preview-products-container {
    display: flex;
    gap: <?php echo absint($card_gap); ?>px;
    flex-wrap: wrap;
    max-width: 900px;
    margin: 0 auto;
}

/* Product Card */
.affiliate-product-card {
    flex: 1 1 calc(50% - <?php echo absint($card_gap) / 2; ?>px);
    min-width: 280px;
    background: <?php echo esc_attr($card_bg_color); ?>;
    color: <?php echo esc_attr($text_color); ?>;
    border: 1px solid #e0e0e0;
    border-radius: <?php echo esc_attr($border_radius); ?>;
    padding: 16px;
    transition: all 0.3s ease;
    position: relative;
    <?php if ($card_shadow): ?>
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    <?php else: ?>
    box-shadow: none;
    <?php endif; ?>
}

.affiliate-product-card:hover {
    transform: translateY(-3px);
    <?php if ($card_shadow): ?>
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    <?php endif; ?>
}

/* Store Badge */
.store-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: <?php echo esc_attr($accent_color); ?>;
    color: #fff;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75em;
    font-weight: 600;
}

/* Product Image */
.affiliate-product-card img {
    width: 100%;
    height: auto;
    border-radius: <?php echo esc_attr($border_radius); ?>;
    margin-bottom: 12px;
    display: block;
}

/* Product Title */
.affiliate-title {
    color: <?php echo esc_attr($primary_color); ?>;
    font-size: 1.2em;
    font-weight: 600;
    margin-bottom: 10px;
    line-height: 1.4;
}

.affiliate-title a {
    color: <?php echo esc_attr($primary_color); ?>;
    text-decoration: none;
}

.affiliate-title a:hover {
    text-decoration: underline;
}

/* Product Description */
.affiliate-description {
    color: <?php echo esc_attr($text_color); ?>;
    font-size: 0.9em;
    line-height: 1.5;
    margin-bottom: 12px;
}

/* Product Price */
.affiliate-price {
    font-size: 1.4em;
    font-weight: 700;
    color: <?php echo esc_attr($accent_color); ?>;
    margin-bottom: 12px;
}

/* Price Empty Text */
.price-empty {
    font-size: 0.95em;
    font-style: italic;
    color: #888;
    margin-bottom: 12px;
}

/* Product Button - Base (v1.5.4) */
.affiliate-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 18px;
    border-radius: <?php echo esc_attr($border_radius); ?>;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95em;
    <?php if ($button_style === 'gradient'): ?>
    /* Estilo: Gradiente */
    background: linear-gradient(135deg, <?php echo esc_attr($button_color); ?>, <?php echo esc_attr($gradient_color); ?>);
    color: #fff;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    <?php elseif ($button_style === 'flat'): ?>
    /* Estilo: Preenchido */
    background: <?php echo esc_attr($button_color); ?>;
    color: #fff;
    border: 2px solid <?php echo esc_attr($button_color); ?>;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    <?php elseif ($button_style === 'outline'): ?>
    /* Estilo: Contorno */
    background: transparent;
    color: <?php echo esc_attr($button_color); ?>;
    border: 2px solid <?php echo esc_attr($button_color); ?>;
    box-shadow: none;
    <?php else: ?>
    /* Fallback para gradiente */
    background: linear-gradient(135deg, <?php echo esc_attr($button_color); ?>, <?php echo esc_attr($gradient_color); ?>);
    color: #fff;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    <?php endif; ?>
}

.affiliate-btn:hover {
    transform: scale(1.02);
    <?php if ($button_style === 'gradient'): ?>
    filter: brightness(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    <?php elseif ($button_style === 'flat'): ?>
    opacity: 0.9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    <?php elseif ($button_style === 'outline'): ?>
    background: <?php echo esc_attr($button_color); ?>;
    color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    <?php else: ?>
    filter: brightness(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    <?php endif; ?>
}

/* Responsive */
@media (max-width: 650px) {
    .affiliate-product-card {
        flex: 1 1 100%;
    }
}

/* Custom CSS */
<?php if (!empty($settings['custom_css'])): ?>
<?php echo wp_strip_all_tags($settings['custom_css']); ?>
<?php endif; ?>
</style>
</head>
<body>
    <div class="preview-header">
        📱 Pré-visualização ao Vivo - v1.5.4 (Sincronizado com affiliate_pro_settings + button_style)
    </div>

    <div class="preview-products-container">
        <?php for ($i = 1; $i <= 2; $i++): ?>
            <div class="affiliate-product-card">
                <?php if ($show_store_badge): ?>
                    <div class="store-badge">
                        <?php echo ($i === 1) ? 'Amazon' : 'Mercado Livre'; ?>
                    </div>
                <?php endif; ?>

                <img src="<?php echo esc_url($placeholder_img); ?>" alt="Produto Exemplo <?php echo $i; ?>">

                <?php if ($title_clickable): ?>
                    <h3 class="affiliate-title">
                        <a href="#"<?php echo $link_target; ?>
                           data-aff-id="preview-<?php echo $i; ?>"
                           data-source="title">Produto Exemplo <?php echo $i; ?></a>
                    </h3>
                <?php else: ?>
                    <h3 class="affiliate-title"
                        data-aff-id="preview-<?php echo $i; ?>"
                        data-source="title">
                        Produto Exemplo <?php echo $i; ?>
                    </h3>
                <?php endif; ?>

                <p class="affiliate-description">
                    Esta é uma descrição breve do produto <?php echo $i; ?>. Aqui você pode ver como o card ficará com as configurações atuais do Template Builder.
                </p>

                <?php if ($show_price): ?>
                    <p class="affiliate-price">
                        <?php
                        $price_value = ($i === 1) ? '199,90' : '349,90';
                        echo str_replace('{valor}', $price_value, esc_html($price_format));
                        ?>
                    </p>
                <?php else: ?>
                    <p class="price-empty">
                        <?php echo esc_html($price_placeholder); ?>
                    </p>
                <?php endif; ?>

                <button class="affiliate-btn"
                        data-aff-id="preview-<?php echo $i; ?>"
                        data-source="button">
                    <?php echo esc_html($button_text); ?>
                </button>
            </div>
        <?php endfor; ?>
    </div>
</body>
</html>
