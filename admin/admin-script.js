/**
 * Admin Scripts - Plugin Afiliados Pro
 * @version 1.8.9
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // =============================================================================
        // Color Pickers
        // =============================================================================

        if ($.fn.wpColorPicker) {
            $('.color-picker').wpColorPicker();
        }

        // =============================================================================
        // Range Sliders com atualização de valor
        // =============================================================================

        $('.range-slider').on('input', function() {
            const outputId = $(this).data('output');
            const value = $(this).val();
            $('#' + outputId).text(value + ($(this).attr('name').includes('radius') || $(this).attr('name').includes('gap') ? 'px' : ''));
        });

        // =============================================================================
        // Seções Colapsáveis
        // =============================================================================

        $('.section-title').on('click', function() {
            $(this).closest('.affiliate-settings-section').toggleClass('collapsed');
        });

        // Expandir todas as seções por padrão na primeira visita
        if (!localStorage.getItem('affiliate_pro_sections_collapsed')) {
            $('.affiliate-settings-section').removeClass('collapsed');
        }

        // Salvar estado das seções
        $('.section-title').on('click', function() {
            const section = $(this).data('section');
            const isCollapsed = $(this).closest('.affiliate-settings-section').hasClass('collapsed');
            localStorage.setItem('affiliate_pro_section_' + section, isCollapsed ? '1' : '0');
        });

        // Restaurar estado das seções
        $('.section-title').each(function() {
            const section = $(this).data('section');
            const isCollapsed = localStorage.getItem('affiliate_pro_section_' + section) === '1';
            if (isCollapsed) {
                $(this).closest('.affiliate-settings-section').addClass('collapsed');
            }
        });

        // =============================================================================
        // Gerenciar Produtos - Selecionar todos os checkboxes
        // =============================================================================

        $('#cb-select-all').on('change', function() {
            $('input[name="product_ids[]"]').prop('checked', this.checked);
        });

        // =============================================================================
        // Duplicar produto via AJAX
        // =============================================================================

        $('.duplicate-product').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const productId = button.data('id');

            // Validação 1: Verificar se o botão tem o atributo data-id
            if (typeof productId === 'undefined' || productId === null || productId === '') {
                console.error('Affiliate Pro: ID do produto não encontrado no atributo data-id');
                alert('Erro: ID do produto não encontrado. Por favor, recarregue a página.');
                return false;
            }

            // Validação 2: Verificar se o ID é um número válido
            const productIdNum = parseInt(productId, 10);
            if (isNaN(productIdNum) || productIdNum <= 0) {
                console.error('Affiliate Pro: ID do produto inválido:', productId);
                alert('Erro: ID do produto inválido (' + productId + ')');
                return false;
            }

            // Validação 3: Verificar se o nonce existe
            if (typeof affiliateProAdmin === 'undefined' || !affiliateProAdmin.nonce) {
                console.error('Affiliate Pro: Nonce de segurança não encontrado');
                alert('Erro de segurança: nonce não encontrado. Por favor, recarregue a página.');
                return false;
            }

            console.log('Affiliate Pro: Iniciando duplicação do produto ID:', productIdNum);

            // Confirmar ação
            if (!confirm(affiliateProAdmin.strings.confirm_duplicate)) {
                return false;
            }

            // Desabilitar botão e mostrar feedback
            const originalText = button.text();
            button.prop('disabled', true).text('Duplicando...');

            // Fazer requisição AJAX
            $.ajax({
                url: affiliateProAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'duplicate_affiliate_product',
                    product_id: productIdNum,
                    nonce: affiliateProAdmin.nonce
                },
                timeout: 30000, // 30 segundos de timeout
                success: function(response) {
                    console.log('Affiliate Pro: Resposta do servidor:', response);

                    if (response.success) {
                        const message = response.data && response.data.message
                            ? response.data.message
                            : 'Produto duplicado com sucesso!';

                        const newId = response.data && response.data.new_id
                            ? response.data.new_id
                            : null;

                        console.log('Affiliate Pro: Duplicação bem-sucedida. Novo ID:', newId);
                        alert(message);

                        // Recarregar página para mostrar o novo produto
                        location.reload();
                    } else {
                        // Erro retornado pelo servidor
                        const errorMsg = response.data && response.data.message
                            ? response.data.message
                            : (response.data || 'Erro desconhecido ao duplicar produto');

                        console.error('Affiliate Pro: Erro na duplicação:', errorMsg);
                        alert('Erro: ' + errorMsg);

                        // Reabilitar botão
                        button.prop('disabled', false).text(originalText);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Affiliate Pro: Falha na requisição AJAX');
                    console.error('Status:', textStatus);
                    console.error('Erro:', errorThrown);
                    console.error('Resposta:', jqXHR.responseText);

                    let errorMessage = 'Falha na comunicação com o servidor';

                    if (textStatus === 'timeout') {
                        errorMessage = 'Tempo limite excedido. O servidor demorou muito para responder.';
                    } else if (textStatus === 'error') {
                        errorMessage = 'Erro de conexão com o servidor';
                    } else if (textStatus === 'parsererror') {
                        errorMessage = 'Erro ao processar resposta do servidor';
                    }

                    alert(errorMessage + '\n\nDetalhes técnicos: ' + textStatus);

                    // Reabilitar botão
                    button.prop('disabled', false).text(originalText);
                }
            });
        });

        // =============================================================================
        // Copiar shortcode para área de transferência
        // =============================================================================

        $('.copy-shortcode').on('click', function(e) {
            e.preventDefault();
            const shortcode = $(this).data('shortcode');
            const button = $(this);

            // Usar Clipboard API se disponível
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(shortcode).then(function() {
                    showCopyFeedback(button);
                }).catch(function(err) {
                    fallbackCopyToClipboard(shortcode, button);
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopyToClipboard(shortcode, button);
            }
        });

        /**
         * Método fallback para copiar texto
         */
        function fallbackCopyToClipboard(text, button) {
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(text).select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopyFeedback(button);
                } else {
                    alert('Erro ao copiar shortcode. Copie manualmente: ' + text);
                }
            } catch (err) {
                alert('Erro ao copiar shortcode. Copie manualmente: ' + text);
            }

            tempInput.remove();
        }

        /**
         * Mostra feedback visual de cópia
         */
        function showCopyFeedback(button) {
            const originalText = button.html();
            button.addClass('copied').html('✓');

            // Mostrar tooltip
            const tooltip = $('<div class="copy-tooltip">' + affiliateProAdmin.strings.copied + '</div>');
            button.parent().css('position', 'relative').append(tooltip);

            setTimeout(function() {
                tooltip.addClass('show');
            }, 10);

            setTimeout(function() {
                button.removeClass('copied').html(originalText);
                tooltip.removeClass('show');
                setTimeout(function() {
                    tooltip.remove();
                }, 200);
            }, 1500);
        }

        // =============================================================================
        // Confirmação antes de excluir
        // =============================================================================

        // REMOVIDO v1.8.9: Confirmação duplicada
        // A confirmação já está no onclick dos botões HTML em admin-manage-products.php
        // Manter esse código aqui causava dupla confirmação ao excluir/mover para lixeira

        // $('a[href*="action=trash"]').on('click', function(e) {
        //     if (!confirm(affiliateProAdmin.strings.confirm_delete)) {
        //         e.preventDefault();
        //         return false;
        //     }
        // });

        // =============================================================================
        // Preview das cores em tempo real (opcional)
        // =============================================================================

        $('.color-picker').on('change', function() {
            // Aqui pode adicionar preview em tempo real se desejar
            // Por enquanto, as mudanças serão aplicadas após salvar
        });

    });

})(jQuery);
