<?php
/**
 * Classe responsável pela geração de CSS dinâmico
 * v1.9.4: Extraída de PAP_Settings para melhor organização
 *
 * @package PAP
 * @since 1.9.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe PAP_Template_CSS
 * Gera CSS dinâmico baseado nas configurações do plugin
 *
 * @package PAP
 * @since 1.9.4
 */
class PAP_Template_CSS {

    /**
     * Gera CSS dinâmico baseado nas configurações
     *
     * @param array $settings Configurações do plugin
     * @return string CSS gerado
     * @since 1.9.4
     */
    public static function generate($settings) {
        // Variáveis CSS no :root para fácil customização (v1.6.5)
        $css = "
        /* Afiliados Pro - CSS Dinâmico v1.9.4 */

        :root {
            --affiliate-primary-color: {$settings['primary_color']};
            --affiliate-secondary-color: {$settings['secondary_color']};
            --affiliate-accent-color: {$settings['accent_color']};
            --affiliate-card-bg: {$settings['card_bg_color']};
            --affiliate-text-color: {$settings['text_color']};
            --affiliate-image-bg: {$settings['card_image_background']};
            --affiliate-card-radius: {$settings['card_border_radius']}px;
            --affiliate-card-gap: {$settings['card_gap']}px;
            --affiliate-button-start: {$settings['button_color_start']};
            --affiliate-button-end: {$settings['button_color_end']};
            --affiliate-button-text: {$settings['button_text_color']};
            --affiliate-price-color: {$settings['price_color']};
        }

        /* Grade de produtos com gap dinâmico */
        .affiliate-products-grid {
            gap: var(--affiliate-card-gap);
        }

        /* Cards com cores e bordas personalizadas */
        .affiliate-product-card {
            background: var(--affiliate-card-bg);
            border-radius: var(--affiliate-card-radius);
            color: var(--affiliate-text-color);
            margin: 0;
            padding: 0;
        }

        .affiliate-product-card .product-content {
            padding: 16px;
        }
        ";

        // Sombra nos cards (condicional)
        if ($settings['card_shadow']) {
            $css .= "
        .affiliate-product-card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .affiliate-product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
            ";
        } else {
            $css .= "
        .affiliate-product-card {
            box-shadow: none;
        }

        .affiliate-product-card:hover {
            box-shadow: none;
        }
            ";
        }

        $css .= "
        /* Títulos com cor primária (v1.5.6) */
        .affiliate-product-card .product-title {
            color: var(--affiliate-primary-color);
        }

        .affiliate-product-card .product-title a {
            color: var(--affiliate-primary-color);
        }

        /* Texto auxiliar com cor secundária (v1.5.6) */
        .affiliate-product-card .product-excerpt,
        .affiliate-product-card .product-description {
            color: var(--affiliate-text-color);
        }

        /* Imagens com fundo controlado (v1.5.6) */
        .affiliate-product-card .product-image {
            height: 220px;
            background: var(--affiliate-image-bg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .affiliate-product-card .product-image img {
            max-height: 220px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        /* Badge da loja com cor de destaque (v1.5.6) */
        .affiliate-product-card .store-badge {
            background: var(--affiliate-accent-color);
            color: #fff;
        }

        /* Preço com cor personalizada (v1.6.3: aplica-se também ao texto alternativo Sem preco) */
        .affiliate-product-card .product-price {
            color: var(--affiliate-price-color) !important;
            font-weight: 600;
        }

        /* Botões base (v1.5.5) */
        .affiliate-product-card .product-button,
        .affiliate-product-card .affiliate-btn-flat,
        .affiliate-product-card .affiliate-btn-outline,
        .affiliate-product-card .affiliate-btn-gradient {
            width: auto;
            min-width: 120px;
            max-width: 90%;
            text-align: center;
            display: inline-block;
            transition: all 0.3s ease;
            padding: 10px 18px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 6px;
        }

        /* Estilo: Preenchido (Flat) - v1.5.6 */
        .affiliate-product-card .affiliate-btn-flat {
            background: var(--button-color-start, var(--affiliate-button-start));
            color: var(--button-text-color, var(--affiliate-button-text));
            border: 2px solid var(--button-color-start, var(--affiliate-button-start));";

        // v1.6.5: Sombra nos botões condicional
        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);";
        }

        $css .= "
        }

        .affiliate-product-card .affiliate-btn-flat:hover {
            opacity: 0.9;
            transform: translateY(-2px);";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);";
        }

        $css .= "
        }

        /* Estilo: Contorno (Outline) - v1.5.6 */
        .affiliate-product-card .affiliate-btn-outline {
            background: transparent;
            color: var(--button-color-start, var(--affiliate-button-start));
            border: 2px solid var(--button-color-start, var(--affiliate-button-start));
            box-shadow: none;
        }

        .affiliate-product-card .affiliate-btn-outline:hover {
            background: var(--button-color-start, var(--affiliate-button-start));
            color: var(--button-text-color, var(--affiliate-button-text));
            transform: translateY(-2px);";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);";
        }

        $css .= "
        }

        /* Estilo: Gradiente - v1.5.6 */
        .affiliate-product-card .affiliate-btn-gradient {
            background: linear-gradient(135deg, var(--button-color-start, var(--affiliate-button-start)) 0%, var(--button-color-end, var(--affiliate-button-end)) 100%);
            color: var(--button-text-color, var(--affiliate-button-text));
            border: none;";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);";
        }

        $css .= "
        }

        .affiliate-product-card .affiliate-btn-gradient:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);";

        if ($settings['shadow_button']) {
            $css .= "
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);";
        }

        $css .= "
        }
        ";

        // Título clicável (condicional)
        if ($settings['title_clickable']) {
            $css .= "
        .affiliate-product-card .product-title a {
            cursor: pointer;
            transition: color 0.2s;
        }

        .affiliate-product-card .product-title a:hover {
            color: var(--affiliate-primary-color);
        }
            ";
        } else {
            $css .= "
        .affiliate-product-card .product-title a {
            pointer-events: none;
            cursor: default;
        }
            ";
        }

        // Badge da loja (condicional)
        if (!$settings['show_store_badge']) {
            $css .= "
        .affiliate-product-card .store-badge {
            display: none !important;
        }
            ";
        }

        // CSS customizado adicional
        if (!empty($settings['custom_css'])) {
            $css .= "\n/* CSS Customizado */\n" . $settings['custom_css'];
        }

        return $css;
    }
}
