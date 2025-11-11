<?php
/**
 * Afiliados Pro - Preview Template
 *
 * Completely standalone preview template without WP dependencies
 *
 * @package AfiliadorsPro
 * @version 1.4.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get settings (should be passed from parent handler)
if (!isset($settings)) {
    $settings = Affiliate_Template_Builder::get_template_settings();
}

// Map border radius values
$radius_map = [
    'none' => '0px',
    'small' => '4px',
    'medium' => '8px',
    'large' => '16px',
];

$border_radius = isset($radius_map[$settings['border_radius']])
    ? $radius_map[$settings['border_radius']]
    : '8px';

// Gradient secondary color
$gradient_secondary = !empty($settings['gradient_color'])
    ? $settings['gradient_color']
    : $settings['primary_color'];

// Determine if CSS should be forced
$important = !empty($settings['force_css']) ? ' !important' : '';

// Card shadow
$use_card_shadow = !empty($settings['shadow_card']);

// Button shadow
$use_button_shadow = !empty($settings['shadow_button']);

// Gap between cards
$card_gap = isset($settings['card_gap']) ? absint($settings['card_gap']) : 20;

// Functional settings (v1.4.4)
$button_text = !empty($settings['button_text']) ? $settings['button_text'] : 'Ver Produto';
$show_price = !empty($settings['show_price']);
$clickable_title = !empty($settings['clickable_title']);
$show_store_badge = !empty($settings['show_store_badge']);

// Placeholder image URL (online, reliable)
$placeholder_img = 'https://via.placeholder.com/300x200?text=Produto+Exemplo';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Preview - Afiliados Pro v1.4.4</title>
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

/* Container dos cards (v1.4.4 - dynamic gap) */
.preview-products-container {
    display: flex;
    gap: <?php echo $card_gap; ?>px;
    flex-wrap: wrap;
    max-width: 900px;
    margin: 0 auto;
}

/* Product Card */
.affiliate-product-card {
    flex: 1 1 calc(50% - <?php echo $card_gap / 2; ?>px);
    min-width: 280px;
    background: #fff<?php echo $important; ?>;
    border: 1px solid #e0e0e0<?php echo $important; ?>;
    border-radius: <?php echo esc_attr($border_radius); ?><?php echo $important; ?>;
    padding: 16px<?php echo $important; ?>;
    transition: all 0.3s ease<?php echo $important; ?>;
    position: relative<?php echo $important; ?>;

    <?php
    // Apply card shadow
    if ($use_card_shadow) {
        echo "box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1){$important};";
    } else {
        echo "box-shadow: none{$important};";
    }
    ?>
}

.affiliate-product-card:hover {
    transform: translateY(-3px)<?php echo $important; ?>;
    <?php
    if ($use_card_shadow) {
        echo "box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15){$important};";
    }
    ?>
}

/* Store Badge (v1.4.4) */
.store-badge {
    position: absolute<?php echo $important; ?>;
    top: 10px<?php echo $important; ?>;
    right: 10px<?php echo $important; ?>;
    background: <?php echo esc_attr($settings['primary_color']); ?><?php echo $important; ?>;
    color: #fff<?php echo $important; ?>;
    padding: 4px 10px<?php echo $important; ?>;
    border-radius: 12px<?php echo $important; ?>;
    font-size: 0.75em<?php echo $important; ?>;
    font-weight: 600<?php echo $important; ?>;
}

/* Product Image */
.affiliate-product-card img {
    width: 100%<?php echo $important; ?>;
    height: auto<?php echo $important; ?>;
    border-radius: <?php echo esc_attr($border_radius); ?><?php echo $important; ?>;
    margin-bottom: 12px<?php echo $important; ?>;
    display: block<?php echo $important; ?>;
}

/* Product Title */
.affiliate-title {
    color: <?php echo esc_attr($settings['primary_color']); ?><?php echo $important; ?>;
    font-size: 1.2em<?php echo $important; ?>;
    font-weight: 600<?php echo $important; ?>;
    margin-bottom: 10px<?php echo $important; ?>;
    line-height: 1.4<?php echo $important; ?>;
}

.affiliate-title a {
    color: <?php echo esc_attr($settings['primary_color']); ?><?php echo $important; ?>;
    text-decoration: none<?php echo $important; ?>;
}

.affiliate-title a:hover {
    text-decoration: underline<?php echo $important; ?>;
}

/* Product Description */
.affiliate-description {
    color: #555<?php echo $important; ?>;
    font-size: 0.9em<?php echo $important; ?>;
    line-height: 1.5<?php echo $important; ?>;
    margin-bottom: 12px<?php echo $important; ?>;
}

/* Product Price */
.affiliate-price {
    font-size: 1.4em<?php echo $important; ?>;
    font-weight: 700<?php echo $important; ?>;
    color: #2c3e50<?php echo $important; ?>;
    margin-bottom: 12px<?php echo $important; ?>;
}

/* Product Button */
.affiliate-btn {
    display: inline-block<?php echo $important; ?>;
    margin-top: 10px<?php echo $important; ?>;
    padding: 10px 18px<?php echo $important; ?>;
    border-radius: <?php echo esc_attr($border_radius); ?><?php echo $important; ?>;
    cursor: pointer<?php echo $important; ?>;
    transition: all 0.3s ease<?php echo $important; ?>;
    text-decoration: none<?php echo $important; ?>;
    font-weight: 500<?php echo $important; ?>;
    font-size: 0.95em<?php echo $important; ?>;
    border: none<?php echo $important; ?>;

    <?php
    // Button style based on settings
    if ($settings['button_style'] === 'filled') {
        echo "background: {$settings['button_color']}{$important};";
        echo "color: #fff{$important};";
        echo "border: 2px solid {$settings['button_color']}{$important};";
    } elseif ($settings['button_style'] === 'outline') {
        echo "background: transparent{$important};";
        echo "color: {$settings['button_color']}{$important};";
        echo "border: 2px solid {$settings['button_color']}{$important};";
    } elseif ($settings['button_style'] === 'gradient') {
        echo "background: linear-gradient(135deg, {$settings['button_color']}, {$gradient_secondary}){$important};";
        echo "color: #fff{$important};";
        echo "border: none{$important};";
    }

    // Apply button shadow
    if ($use_button_shadow) {
        echo "box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15){$important};";
    } else {
        echo "box-shadow: none{$important};";
    }
    ?>
}

.affiliate-btn:hover {
    opacity: 0.9<?php echo $important; ?>;
    transform: scale(1.02)<?php echo $important; ?>;

    <?php
    // Enhanced shadow on hover
    if ($use_button_shadow) {
        echo "box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2){$important};";
    }
    ?>
}

/* Responsive */
@media (max-width: 650px) {
    .affiliate-product-card {
        flex: 1 1 100%;
    }
}

/* Custom CSS (v1.4.4) */
<?php if (!empty($settings['custom_css'])): ?>
<?php echo $settings['custom_css']; ?>
<?php endif; ?>
</style>
</head>
<body>
    <div class="preview-header">
        📱 Pré-visualização ao Vivo - v1.4.4 (Endpoint Público)
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

                <?php if ($clickable_title): ?>
                    <h3 class="affiliate-title">
                        <a href="#">Produto Exemplo <?php echo $i; ?></a>
                    </h3>
                <?php else: ?>
                    <h3 class="affiliate-title">
                        Produto Exemplo <?php echo $i; ?>
                    </h3>
                <?php endif; ?>

                <p class="affiliate-description">
                    Esta é uma descrição breve do produto <?php echo $i; ?>. Aqui você pode ver como o card ficará com as configurações atuais do Template Builder.
                </p>

                <?php if ($show_price): ?>
                    <p class="affiliate-price">
                        R$ <?php echo ($i === 1) ? '199,90' : '349,90'; ?>
                    </p>
                <?php endif; ?>

                <button class="affiliate-btn">
                    <?php echo esc_html($button_text); ?>
                </button>
            </div>
        <?php endfor; ?>
    </div>
</body>
</html>
