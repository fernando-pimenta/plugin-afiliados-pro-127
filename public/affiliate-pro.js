/**
 * Frontend Scripts - Plugin Afiliados Pro
 * @version 1.2
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // =============================================================================
        // Lazy Loading de Imagens (opcional)
        // =============================================================================

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    }
                });
            });

            $('.affiliate-product-card .product-image img[data-src]').each(function() {
                imageObserver.observe(this);
            });
        }

        // =============================================================================
        // Tracking de Cliques (opcional - para analytics)
        // =============================================================================

        $('.affiliate-product-card .product-button').on('click', function() {
            const productTitle = $(this).closest('.affiliate-product-card').find('.product-title').text().trim();
            const productLink = $(this).attr('href');

            // Google Analytics 4 (se disponível)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'affiliate_click', {
                    'product_name': productTitle,
                    'product_link': productLink
                });
            }

            // Google Analytics Universal (se disponível)
            if (typeof ga !== 'undefined') {
                ga('send', 'event', 'Affiliate Product', 'Click', productTitle);
            }

            // Facebook Pixel (se disponível)
            if (typeof fbq !== 'undefined') {
                fbq('track', 'Lead', {
                    content_name: productTitle
                });
            }

            // Console log para debug
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('Affiliate Pro - Click tracked:', {
                    product: productTitle,
                    link: productLink
                });
            }
        });

        // =============================================================================
        // Animação ao Scroll (fade in)
        // =============================================================================

        if ('IntersectionObserver' in window) {
            const cardObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, {
                threshold: 0.1
            });

            $('.affiliate-product-card').each(function() {
                cardObserver.observe(this);
            });
        } else {
            // Fallback: mostrar todos os cards imediatamente
            $('.affiliate-product-card').addClass('is-visible');
        }

        // =============================================================================
        // Tooltip de Preço (hover)
        // =============================================================================

        $('.affiliate-product-card .product-price').on('mouseenter', function() {
            const price = $(this).text().trim();
            if (price && price !== 'Consulte o preço') {
                // Pode adicionar tooltip customizado aqui se desejar
            }
        });

        // =============================================================================
        // Modo Escuro (detecção automática)
        // =============================================================================

        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // Usuário prefere modo escuro
            // CSS já trata disso, mas pode adicionar lógica extra aqui
        }

        // Escutar mudanças no tema
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                const newColorScheme = e.matches ? 'dark' : 'light';
                console.log('Affiliate Pro - Color scheme changed to:', newColorScheme);
            });
        }

        // =============================================================================
        // Responsividade - Ajuste de layout em tempo real
        // =============================================================================

        function adjustCardLayout() {
            const windowWidth = $(window).width();
            const grids = $('.affiliate-products-grid');

            grids.each(function() {
                const grid = $(this);
                const columns = grid.data('columns');

                // Ajustar número de colunas com base na largura da tela
                if (windowWidth < 480) {
                    grid.attr('data-active-columns', 1);
                } else if (windowWidth < 768) {
                    grid.attr('data-active-columns', Math.min(2, columns));
                } else if (windowWidth < 1024) {
                    grid.attr('data-active-columns', Math.min(3, columns));
                } else {
                    grid.attr('data-active-columns', columns);
                }
            });
        }

        // Executar no carregamento
        adjustCardLayout();

        // Executar ao redimensionar (com debounce)
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(adjustCardLayout, 250);
        });

        // =============================================================================
        // Fallback para imagens quebradas
        // =============================================================================

        $('.affiliate-product-card .product-image img').on('error', function() {
            const placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300"%3E%3Crect width="300" height="300" fill="%23f0f0f1"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-family="sans-serif" font-size="16"%3ESem imagem%3C/text%3E%3C/svg%3E';
            $(this).attr('src', placeholder);
        });

        // =============================================================================
        // Accessibility - Navegação por teclado melhorada
        // =============================================================================

        $('.affiliate-product-card').on('keydown', function(e) {
            // Enter ou Espaço no card inteiro deve ativar o botão
            if (e.key === 'Enter' || e.key === ' ') {
                const button = $(this).find('.product-button');
                if (button.length && !$(e.target).is('a, button')) {
                    e.preventDefault();
                    button[0].click();
                }
            }
        });

        // =============================================================================
        // Performance - Remover animações se preferência reduzida
        // =============================================================================

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // Desabilitar animações CSS
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    .affiliate-product-card,
                    .affiliate-product-card:hover,
                    .affiliate-product-card .product-image img,
                    .affiliate-product-card .product-button {
                        animation: none !important;
                        transition: none !important;
                    }
                `)
                .appendTo('head');
        }

        // =============================================================================
        // Debug Mode (apenas em localhost)
        // =============================================================================

        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('Affiliate Pro v1.2 - Frontend scripts loaded');
            console.log('Found products:', $('.affiliate-product-card').length);
        }

    });

})(jQuery);
