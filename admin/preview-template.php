<?php
/**
 * Afiliados Pro - Preview Template
 *
 * Clean preview template without WP admin dependencies
 *
 * @package AfiliadorsPro
 * @version 1.4.3
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

// Placeholder image URL (online, reliable)
$placeholder_img = 'https://via.placeholder.com/300x200?text=Produto+Exemplo';
?>
<h3 style="color: #444; margin-bottom: 15px; font-size: 1.1em;">
    Pré-visualização ao Vivo - v1.4.3
</h3>

<style>
    /* Container dos cards (v1.4.3 - dynamic gap) */
    .preview-products-container {
        display: flex;
        gap: <?php echo $card_gap; ?>px;
        flex-wrap: wrap;
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

    /* Product Image */
    .affiliate-product-card img {
        width: 100%<?php echo $important; ?>;
        height: auto<?php echo $important; ?>;
        border-radius: <?php echo esc_attr($border_radius); ?><?php echo $important; ?>;
        margin-bottom: 10px<?php echo $important; ?>;
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

    /* Product Description */
    .affiliate-description {
        color: #555<?php echo $important; ?>;
        font-size: 0.9em<?php echo $important; ?>;
        line-height: 1.5<?php echo $important; ?>;
        margin-bottom: 10px<?php echo $important; ?>;
    }

    /* Product Price */
    .affiliate-price {
        font-size: 1.4em<?php echo $important; ?>;
        font-weight: 700<?php echo $important; ?>;
        color: #2c3e50<?php echo $important; ?>;
        margin-bottom: 10px<?php echo $important; ?>;
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
</style>

<div class="preview-products-container">
    <?php for ($i = 1; $i <= 2; $i++): ?>
        <div class="affiliate-product-card">
            <img src="<?php echo esc_url($placeholder_img); ?>" alt="Produto Exemplo <?php echo $i; ?>">

            <h3 class="affiliate-title">
                Produto Exemplo <?php echo $i; ?>
            </h3>

            <p class="affiliate-description">
                Esta é uma descrição breve do produto <?php echo $i; ?>. Aqui você pode ver como o card ficará com as configurações atuais do Template Builder.
            </p>

            <p class="affiliate-price">
                R$ <?php echo ($i === 1) ? '199,90' : '349,90'; ?>
            </p>

            <button class="affiliate-btn">
                <?php echo ($i === 1) ? 'Ver Produto' : 'Comprar Agora'; ?>
            </button>
        </div>
    <?php endfor; ?>
</div>
