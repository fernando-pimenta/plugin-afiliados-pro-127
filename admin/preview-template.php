<?php
/**
 * Afiliados Pro - Preview Template
 *
 * Template displayed in the iframe for live preview
 *
 * @package AfiliadorsPro
 * @version 1.4.0
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

// Determine if CSS should be forced
$important = !empty($settings['force_css']) ? ' !important' : '';

// Build dynamic inline styles based on settings
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Afiliados Pro</title>
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
            background: #f5f5f5;
        }

        /* CSS Variables */
        :root {
            --affiliate-template-primary: <?php echo esc_attr($settings['primary_color']); ?>;
            --affiliate-template-button: <?php echo esc_attr($settings['button_color']); ?>;
            --affiliate-template-radius: <?php echo esc_attr($border_radius); ?>;
        }

        /* Product Card */
        .affiliate-product-card {
            background: #fff<?php echo $important; ?>;
            border: 1px solid #e0e0e0<?php echo $important; ?>;
            border-radius: var(--affiliate-template-radius)<?php echo $important; ?>;
            padding: 20px<?php echo $important; ?>;
            margin-bottom: 16px<?php echo $important; ?>;
            transition: all 0.3s ease<?php echo $important; ?>;
            max-width: 400px;

            <?php
            // Apply card shadow based on settings (v1.4.0)
            if (!empty($settings['shadow_card'])) {
                echo "box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1){$important};";
            } else {
                echo "box-shadow: none{$important};";
            }
            ?>
        }

        .affiliate-product-card:hover {
            transform: translateY(-3px)<?php echo $important; ?>;
            <?php
            if (!empty($settings['shadow_card'])) {
                echo "box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15){$important};";
            }
            ?>
        }

        /* Product Title */
        .affiliate-title {
            color: var(--affiliate-template-primary)<?php echo $important; ?>;
            font-size: 1.3em<?php echo $important; ?>;
            font-weight: 600<?php echo $important; ?>;
            margin-bottom: 12px<?php echo $important; ?>;
            line-height: 1.4<?php echo $important; ?>;
        }

        /* Product Description */
        .affiliate-description {
            color: #666<?php echo $important; ?>;
            font-size: 0.95em<?php echo $important; ?>;
            line-height: 1.6<?php echo $important; ?>;
            margin-bottom: 15px<?php echo $important; ?>;
        }

        /* Product Price */
        .affiliate-price {
            font-size: 1.5em<?php echo $important; ?>;
            font-weight: 700<?php echo $important; ?>;
            color: #2c3e50<?php echo $important; ?>;
            margin-bottom: 15px<?php echo $important; ?>;
        }

        /* Product Button */
        .affiliate-btn {
            border-radius: var(--affiliate-template-radius)<?php echo $important; ?>;
            padding: 12px 24px<?php echo $important; ?>;
            text-decoration: none<?php echo $important; ?>;
            display: inline-block<?php echo $important; ?>;
            transition: all 0.3s ease<?php echo $important; ?>;
            font-weight: 500<?php echo $important; ?>;
            cursor: pointer<?php echo $important; ?>;
            border: none<?php echo $important; ?>;

            <?php
            // Button style based on settings
            if ($settings['button_style'] === 'filled') {
                echo "background-color: {$settings['button_color']}{$important};";
                echo "border: 2px solid {$settings['button_color']}{$important};";
                echo "color: #fff{$important};";
            } elseif ($settings['button_style'] === 'outline') {
                echo "background-color: transparent{$important};";
                echo "border: 2px solid {$settings['button_color']}{$important};";
                echo "color: {$settings['button_color']}{$important};";
            } elseif ($settings['button_style'] === 'gradient') {
                echo "background: linear-gradient(135deg, {$settings['button_color']} 0%, {$settings['primary_color']} 100%){$important};";
                echo "border: none{$important};";
                echo "color: #fff{$important};";
            }

            // Apply button shadow based on settings (v1.4.0)
            if (!empty($settings['shadow_button'])) {
                echo "box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15){$important};";
            } else {
                echo "box-shadow: none{$important};";
            }
            ?>
        }

        .affiliate-btn:hover {
            <?php
            if ($settings['button_style'] === 'filled') {
                echo "opacity: 0.9{$important};";
                echo "transform: translateY(-2px){$important};";
            } elseif ($settings['button_style'] === 'outline') {
                echo "background-color: {$settings['button_color']}{$important};";
                echo "color: #fff{$important};";
            } elseif ($settings['button_style'] === 'gradient') {
                echo "opacity: 0.95{$important};";
                echo "transform: translateY(-2px){$important};";
            }

            // Enhanced shadow on hover if button shadow is enabled
            if (!empty($settings['shadow_button'])) {
                echo "box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2){$important};";
            }
            ?>
        }

        /* Preview Label */
        .preview-label {
            background: #2271b1;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-bottom: 15px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="preview-label">
        📱 Pré-visualização ao Vivo
    </div>

    <div class="affiliate-product-card">
        <h3 class="affiliate-title">Produto Exemplo Premium</h3>
        <p class="affiliate-description">
            Esta é uma descrição do produto de exemplo. Aqui você pode ver como o card
            ficará com as configurações atuais do Template Builder.
        </p>
        <div class="affiliate-price">R$ 199,90</div>
        <button class="affiliate-btn">Ver Produto</button>
    </div>

    <div class="affiliate-product-card">
        <h3 class="affiliate-title">Outro Produto de Teste</h3>
        <p class="affiliate-description">
            Segundo produto para demonstrar como múltiplos cards aparecem com suas configurações.
        </p>
        <div class="affiliate-price">R$ 349,90</div>
        <button class="affiliate-btn">Comprar Agora</button>
    </div>
</body>
</html>
