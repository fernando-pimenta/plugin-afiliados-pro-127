=== Plugin Afiliados Pro ===
Contributors: fernandopimenta
Donate link: https://fernandopimenta.blog.br/doar
Tags: affiliate, affiliates, products, csv-import, catalog, ecommerce, shopee, amazon, marketplace
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin WordPress profissional para gerenciamento e exibição de produtos afiliados com importação CSV, shortcodes personalizáveis e painel de aparência visual.

== Description ==

O **Plugin Afiliados Pro** é uma solução completa para WordPress que permite criar, gerenciar e exibir produtos afiliados de forma profissional e atrativa. Ideal para sites de comparação, blogs de review, e portais de cupons.

= Principais Recursos =

* 🎨 **Painel de Aparência Visual** - Personalize cores, bordas, botões e layout sem tocar em código
* 📊 **Dashboard Completo** - Visualize estatísticas e gerencie produtos facilmente
* 📁 **Importação CSV** - Importe centenas de produtos de uma só vez
* 🎯 **Shortcodes Flexíveis** - Exiba produtos individuais ou grades personalizadas
* 🏷️ **Sistema de Categorias** - Organize produtos por categorias hierárquicas
* 🔄 **Duplicação de Produtos** - Clone produtos com um clique
* 📱 **Totalmente Responsivo** - Visual perfeito em desktop, tablet e mobile
* 🌐 **Pronto para Tradução** - Suporte completo a i18n
* ⚡ **Otimizado** - Carregamento condicional de CSS/JS

= Ideal Para =

* Sites de comparação de preços
* Blogs de review de produtos
* Portais de cupons e ofertas
* Sites de afiliados Shopee, Amazon, Magazine Luiza, etc.
* Catálogos de produtos afiliados

= Shortcodes Disponíveis =

**Produto único:**
`[affiliate_product id="123"]` ou `[pap_product id="123"]`

**Grade de produtos:**
`[affiliate_products limit="6" category="eletronicos" columns="3"]` ou `[pap_products limit="6" category="eletronicos" columns="3"]`

**Preset personalizado (v1.6.0+):**
`[afiliados_pro id="1"]` ou `[pap_preset id="1"]`

**Nota:** A partir da v1.7.0, os shortcodes com prefixo `pap_` são os oficiais. Os shortcodes antigos continuam funcionando para total compatibilidade.

= Suporte e Documentação =

Para documentação completa, visite [fernandopimenta.blog.br](https://fernandopimenta.blog.br)

== Installation ==

= Instalação Automática =

1. Acesse o painel do WordPress
2. Vá em **Plugins → Adicionar Novo**
3. Pesquise por "Plugin Afiliados Pro"
4. Clique em **Instalar Agora** e depois **Ativar**

= Instalação Manual =

1. Baixe o arquivo ZIP do plugin
2. Acesse **Plugins → Adicionar Novo → Enviar Plugin**
3. Selecione o arquivo ZIP e clique em **Instalar Agora**
4. Ative o plugin

= Após a Ativação =

1. Acesse **Afiliados → Dashboard** no menu do WordPress
2. Adicione seu primeiro produto ou importe via CSV
3. Configure a aparência em **Afiliados → Aparência e Configurações**
4. Use os shortcodes para exibir produtos em suas páginas

== Frequently Asked Questions ==

= Como importar produtos via CSV? =

Acesse **Afiliados → Importar CSV** e use o seguinte formato:

`Título,Descrição,Preço,Link de Afiliado,URL da Imagem,Categoria`

A primeira linha deve conter os cabeçalhos e será ignorada na importação.

= Como personalizar as cores dos cards? =

Acesse **Afiliados → Aparência e Configurações** e personalize:
- Cores primária, secundária e de destaque
- Cor de fundo dos cards
- Cores dos botões (gradiente)
- Arredondamento das bordas
- E muito mais!

= Os shortcodes são responsivos? =

Sim! O plugin é totalmente responsivo e se adapta automaticamente a diferentes tamanhos de tela (desktop, tablet e mobile).

= Posso usar CSS personalizado? =

Sim! Na página **Aparência e Configurações** há um campo para adicionar CSS customizado.

= O plugin funciona com Page Builders? =

Sim! Os shortcodes funcionam perfeitamente com Elementor, WPBakery, Gutenberg e outros page builders.

= Como rastrear cliques nos links de afiliado? =

O plugin inclui suporte nativo para Google Analytics, Facebook Pixel e outros sistemas de tracking via eventos JavaScript.

= O plugin afeta a performance do site? =

Não! O CSS e JavaScript são carregados apenas nas páginas que usam os shortcodes, mantendo seu site rápido.

== Screenshots ==

1. Dashboard principal com estatísticas e ações rápidas
2. Página de gerenciamento de produtos com filtros avançados
3. Painel de Aparência e Configurações completo
4. Grade de produtos no frontend (layout grid)
5. Card de produto individual com design moderno
6. Interface de importação CSV
7. Meta box de detalhes do produto
8. Layout lista (alternativo ao grid)

== Changelog ==

= 1.7.0 (2025-11-13) =
* 🧱 **BASE**: Introdução do padrão pap_ (Plugin Afiliados Pro)
* 🧩 **NOVOS SHORTCODES**: [pap_product], [pap_products], [pap_preset]
* ⚙️ **CLASSES**: PAP_Plugin, PAP_Products, PAP_Settings, PAP_Template_Builder, PAP_Shortcodes
* 🔧 **CONSTANTES**: PAP_VERSION, PAP_DIR, PAP_URL, PAP_BASENAME
* 🔄 **FUNÇÕES**: pap(), pap_log() como aliases compatíveis
* 🧠 **COMPATIBILIDADE**: Total retroatividade com shortcodes e funções antigas
* 🚀 **PREPARAÇÃO**: Base para refatoração completa nas próximas versões

= 1.6.7 (2025-11-13) =
* 🎨 **VISUAL**: Centralização global dos cards renderizados por shortcodes
* ⚙️ **CSS**: Ajustado para alinhar os cards ao centro no front-end
* 🧩 **COMPATÍVEL**: Funciona com todos os shortcodes ([affiliate_product], [affiliate_products], [afiliados_pro])
* ✅ **MANTIDO**: Nenhuma alteração estrutural, lógica ou funcional

= 1.5.9.7 (2025-11-28) =
* 🎨 **UX MELHORADA**: Layout compacto dos campos de cor na seção Identidade Visual
* 🔄 **REORGANIZAÇÃO**: Color picker à esquerda, labels e descrições à direita
* 📏 **ESPAÇAMENTO**: Redução do espaçamento vertical para layout mais denso
* 🎯 **ALINHAMENTO**: Textos alinhados verticalmente ao centro do color picker
* ✨ **VISUAL**: Color pickers maiores (50x50px) com bordas arredondadas e hover effects
* 📱 **RESPONSIVO**: Layout mantém funcionalidade em todas as resoluções
* ⚡ **USABILIDADE**: Interface mais intuitiva e organizada para seleção de cores

= 1.5.9.6 (2025-11-28) =
* 🔧 **FIX**: Corrigido salvamento de checkboxes shadow_card (Sombra nos cards)
* ✅ **SHADOW_BUTTON**: Adicionado salvamento completo de shadow_button (Sombra nos botões)
* ✅ **FORCE_CSS**: Adicionado salvamento completo de force_css (Forçar CSS)
* 💾 **DEFAULTS**: Adicionado shadow_button e force_css aos defaults das configurações
* 📝 **SANITIZAÇÃO**: Adicionada sanitização de shadow_button e force_css no Affiliate_Pro_Settings
* 🎯 **CONSISTÊNCIA**: Todos os checkboxes agora salvam corretamente quando marcados ou desmarcados

= 1.5.9.5 (2025-11-28) =
* 🔧 **FIX CRÍTICO**: Corrigido salvamento de checkboxes desmarcados no Template Builder
* ✅ **TÍTULO CLICÁVEL**: Agora desabilita corretamente quando desmarcado
* ✅ **ABRIR EM NOVA ABA**: Agora desabilita corretamente quando desmarcado
* ✅ **BADGE DA LOJA**: Agora oculta corretamente quando desmarcado
* 💾 **SALVAMENTO**: Adicionado else { false } para todos os checkboxes
* 📝 **COMPORTAMENTO**: Checkboxes agora salvam false ao serem desmarcados

= 1.5.9.4 (2025-11-28) =
* ⚙️ **FUNCIONAL**: Opção "Exibir Preço" agora aplicada corretamente no front-end
* 💰 **CORREÇÃO**: Salvamento de show_price implementado no Template Builder
* 🔧 **FIX**: Condicionais aplicadas para exibir/ocultar preço conforme configuração
* 📝 **DEFAULTS**: Adicionado show_price aos defaults das configurações
* ✅ **TODAS OPÇÕES FUNCIONAIS**: Texto do botão, formato de preço, badge, título clicável, nova aba e CSS personalizado já estavam funcionando corretamente
* 🎨 **INTERFACE**: Nenhuma mudança visual, apenas correção funcional

= 1.5.9.3 (2025-11-27) =
* 📏 **VISUAL**: Altura do preview expandida (600px) com overflow-y auto
* 🧱 **UX**: Preview centralizado e painel mantido à esquerda
* 🧩 **CONSISTÊNCIA**: Dois cards no preview mantidos
* ⚡ **RESPONSIVO**: Layout equilibrado em zoom 100% com centralização
* 🎯 **PROPORÇÕES**: Preview com justify-content center e margens automáticas
* ✅ **SEM MUDANÇAS FUNCIONAIS**: Apenas refinamento visual

= 1.5.9.2 (2025-11-27) =
* 🎨 **VISUAL**: Preview ampliado (~420px) e lado invertido (direita)
* 🧱 **UX**: Painel de personalização à esquerda
* ⚡ **RESPONSIVO**: Layout equilibrado em zoom 100%
* ✨ **POLIMENTO**: Fade-in leve e margens refinadas
* 📐 **PROPORÇÕES**: Preview 58% (max-width 640px), Controles 42% (max-width 480px)
* ✅ **SEM MUDANÇAS FUNCIONAIS**: Apenas refinamento visual e reposicionamento

= 1.5.9.1 (2025-11-27) =
* 🎯 **CORREÇÃO UX**: Preview e painel agora lado a lado em 100% de zoom
* 📏 **AJUSTE**: Proporções refinadas (preview ocupa ~48% da largura com max-width 480px)
* ⚡ **RESPONSIVO**: Stack vertical automático abaixo de 1024px
* 🧱 **VISUAL**: Padding reduzido (12px) e altura limitada (340px) para proporção realista
* 🔍 **ESCALA**: Transform scale(0.9) no preview para melhor ajuste visual
* ✅ **SEM ALTERAÇÕES FUNCIONAIS**: Apenas refinamento visual e proporções

= 1.5.9 (2025-11-12) =
* 🎨 **UX OTIMIZADA**: Redesenho completo do Template Builder com layout split-pane moderno
* 📐 **LAYOUT**: Preview 55% + Controles 42% com responsividade em <960px
* 📏 **PREVIEW COMPACTO**: Altura da pré-visualização reduzida de 800px para 500px
* ✨ **VISUAL**: Painéis com bordas arredondadas (12px), fundos brancos e sombras suaves
* 🎯 **ESPAÇAMENTO**: Margens otimizadas (10px entre campos) para melhor densidade visual
* 🎨 **COLOR PICKERS**: Tamanho padronizado (60px × 35px) para consistência
* 📱 **RESPONSIVE**: Empilhamento automático em telas menores (<960px)
* 🔧 **FIELDSETS**: Fundos (#fafafa) e bordas para melhor agrupamento visual

= 1.5.8.6 (2025-11-25) =
* 🖼️ **CORREÇÃO IMPORTANTE**: Adicionado campo "Fundo da Área da Imagem" no Template Builder (page=affiliate-template-builder)
* 📍 **LOCAL CORRETO**: Campo agora aparece em WordPress Admin → Afiliados → Aparência e Configurações → Template Builder
* 💾 **PERSISTÊNCIA**: Salvamento do campo card_image_background implementado no Template Builder
* ✅ **SINCRONIZAÇÃO**: Campo aparece logo após "Fundo do Card" na seção Identidade Visual

= 1.5.8.5 (2025-11-25) =
* 🖼️ **CORREÇÃO**: Melhorado campo "Fundo da Área da Imagem" para melhor visibilidade
* 📝 **MELHORIA**: Adicionada descrição ao campo "Cor de Fundo do Card"
* ✅ **UX**: Título do campo alterado para "Fundo da Área da Imagem" (mais descritivo)
* 🔧 **FIX**: Removido operador coalescente redundante no value do campo

= 1.5.8.4 (2025-11-24) =
* 🖼️ **MELHORIA**: Campo "Fundo da Imagem" reposicionado para dentro da seção Identidade Visual
* 🎨 **ORGANIZAÇÃO**: Agrupados campos de fundo (Card + Imagem) para melhor experiência no painel
* ✅ **UX**: Interface da aba Aparência reorganizada com ordem mais intuitiva
* 📋 **ORDEM**: Cor de Fundo do Card → Fundo da Imagem → Cor do Texto → Cor do Preço

= 1.5.8.3 (2025-11-23) =
* 🎨 **CORREÇÃO**: Fundo do card agora aplica corretamente no front-end
* 🧩 **MELHORIA**: Variáveis CSS --affiliate-card-bg, --affiliate-image-bg e --affiliate-price-color aplicadas diretamente no card
* ✅ **VERIFICADO**: Campo "Fundo da Imagem" confirmado presente no painel (linhas 81-87 admin-settings.php)
* 💾 **SINCRONIZAÇÃO**: Preview e front-end 100% idênticos em cores de fundo

= 1.5.8.2 (2025-11-22) =
* 🧩 **CORREÇÃO**: Reorganizada ordem dos elementos no front-end (Título → Descrição → Preço → Botão)
* 🎨 **MELHORIA**: Layout do front-end agora idêntico ao preview
* ✅ **VERIFICADO**: Campo "Fundo da Imagem" já presente no painel desde v1.5.8

= 1.5.8.1 (2025-11-21) =
* 💰 **CORREÇÃO**: Cor do preço agora aplica corretamente no preview (usava accent_color)
* 🖼️ **MELHORIA**: Variável --image-bg adicionada ao shortcode para suporte completo
* 🎨 **MELHORIA**: Preview e front-end 100% sincronizados em todas as cores
* ✅ **VERIFICADO**: Campo "Fundo da Imagem" já está funcional no painel

= 1.5.8 (2025-11-20) =
* 💰 **NOVO**: Adicionado campo "Cor do Preço" (price_color)
* 🧩 **CORREÇÃO**: Removida cor de destaque redundante (highlight_color)
* 🎨 **MELHORIA**: Preview e front-end aplicam cor de preço via --price-color
* ✅ **MELHORIA**: Sistema de cores consolidado e limpo
* 📊 **MELHORIA**: Cor do preço independente e personalizável
* 🔄 **SINCRONIZAÇÃO**: Template Builder, admin e front 100% sincronizados

= 1.5.7 (2025-11-19) =
* 🎨 **CORREÇÃO CRÍTICA**: Cores de botão e destaque agora salvam e persistem corretamente
* 🧩 **CORREÇÃO**: Template Builder não salva mais `button_color` como `accent_color`
* 💾 **NOVO**: Adicionado salvamento de `gradient_color` como `button_color_end`
* 🧱 **NOVO**: Campo "Cor de Destaque (Badge)" adicionado ao Template Builder
* ✅ **CORREÇÃO**: Separação completa entre button_color_start e accent_color
* 🔄 **MELHORIA**: Preview e front-end 100% sincronizados com valores salvos
* 📊 **MELHORIA**: Todas as cores agora persistem corretamente após reload

= 1.5.6.1 (2025-11-18) =
* 🛠️ **HOTFIX**: Corrigido erro fatal de sintaxe (concatenação PHP incorreta em class-affiliate-settings.php linha 267)
* ✅ **CORREÇÃO**: Plugin agora ativa normalmente sem erros PHP
* 🧩 **VERIFICADO**: Proteção contra warnings "Undefined array key button_style" já estava implementada

= 1.5.6 (2025-11-17) =
* 🎨 **CORREÇÃO**: Sistema de cores totalmente unificado entre preview e front-end
* 💡 **CORREÇÃO**: Separação completa entre cor de botão e cor de destaque (badge)
* 🧱 **NOVO**: Campo "Cor do Texto do Botão" para controle total da tipografia
* 🧩 **NOVO**: Campo "Fundo da Imagem" para personalizar área da foto do produto
* 🧠 **MELHORIA**: Aplicação de variáveis CSS dinâmicas em todos os elementos (títulos, badges, imagens, botões)
* 🔄 **MELHORIA**: CSS dinâmico v1.5.6 com suporte completo a todas as cores configuráveis
* 📊 **MELHORIA**: Melhorias na UX da aba Aparência & Configurações (tooltips e descrições expandidas)
* ✅ **CORREÇÃO**: Cores agora funcionam de forma independente sem conflitos

= 1.5.5 (2025-11-16) =
* 🎨 **CORREÇÃO**: Aplicação real do estilo de botão (flat / outline / gradient) no front-end e preview
* 🧩 **MELHORIA**: Classes CSS específicas aplicadas aos botões (.affiliate-btn-flat, .affiliate-btn-outline, .affiliate-btn-gradient)
* 💡 **MELHORIA**: Suporte completo a variáveis CSS (--button-color-start, --button-color-end) nos botões
* ✅ **CORREÇÃO**: Eliminação do comportamento estático nos shortcodes - botões agora refletem o estilo selecionado
* 🎯 **MELHORIA**: CSS dinâmico com seletores específicos para cada tipo de botão
* 🔄 **MELHORIA**: Preview e front-end agora aplicam consistentemente os estilos de botão selecionados

= 1.5.4 (2025-11-15) =
* 🧩 **CORREÇÃO**: Campo "Estilo de Botão" agora funciona corretamente (Contorno / Gradiente / Preenchido)
* 🎨 **MELHORIA**: Sincronização total do button_style entre painel admin, preview e front-end
* 💾 **CORREÇÃO**: Persistência real do valor button_style em affiliate_pro_settings
* 🧱 **MELHORIA**: Aplicação condicional de estilos CSS baseada em button_style
* 🛡️ **MELHORIA**: Isolamento entre cor do botão (button_color_start) e cor de destaque (accent_color)
* 🔄 **MELHORIA**: Mapeamento de compatibilidade para valores antigos ('filled' → 'flat')
* 🎯 **MELHORIA**: CSS dinâmico agora aplica estilos específicos por tipo de botão (flat, outline, gradient)

= 1.5.3 (2025-11-14) =
* 🔧 **CORREÇÃO**: Eliminados warnings "Undefined array key" no preview-template.php
* 🧩 **CORREÇÃO**: Alinhamento completo entre Template Builder, Preview e Front-end
* 🔄 **MELHORIA**: Mapeamento bidirecional de chaves antigas/novas para compatibilidade total
* ♻️ **MELHORIA**: Preview e front-end agora usam a mesma fonte de dados (affiliate_pro_settings)
* 🛡️ **OTIMIZAÇÃO**: Operador null coalescing (??) em todas as leituras de configurações
* 📊 **MELHORIA**: Fallbacks automáticos para chaves legadas (highlight_color, card_background_color, etc.)

= 1.5.2 (2025-11-13) =
* 🛠️ **CORREÇÃO CRÍTICA**: Sincronização total entre painel admin e front-end
* 🔄 **CORREÇÃO**: Template Builder agora salva em affiliate_pro_settings (sistema unificado)
* 🔄 **CORREÇÃO**: Shortcodes agora leem configurações do sistema correto
* ✅ **CORREÇÃO**: Configurações de aparência agora persistem corretamente no front-end
* 📊 **MELHORIA**: Removida duplicação de sistemas de configuração
* 🔧 **MELHORIA**: Mapeamento automático de campos legados para novos
* 💾 **MELHORIA**: Feedback visual aprimorado ao salvar configurações
* ♻️ **OTIMIZAÇÃO**: Removido CSS duplicado do Template Builder

= 1.5.1 (2025-11-12) =
* 💅 **POLIMENTO VISUAL**: Painéis e cards com bordas arredondadas (10-12px)
* 💅 **POLIMENTO VISUAL**: Efeitos hover em painéis, tabelas e botões
* 💅 **POLIMENTO VISUAL**: Sombras sutis e transições suaves
* ♿ **ACESSIBILIDADE**: Atributos aria-label em botões e controles
* ♿ **ACESSIBILIDADE**: Atributos title para melhor usabilidade
* ♿ **ACESSIBILIDADE**: role="status" em mensagens de feedback
* 🎨 **UX**: Tabelas com hover e cores alternadas para melhor legibilidade
* 🎨 **UX**: Tags de origem com design moderno e hover
* 🎨 **UX**: Mensagens de sucesso padronizadas e consistentes
* 📱 **RESPONSIVO**: Melhorias em espaçamento e padding

= 1.5.0 (2025-11-11) =
* ✨ **NOVO**: Rastreamento de página de origem (source_page) nos cliques
* ✨ **NOVO**: Painel de estatísticas mostra nome do produto (JOIN com wp_posts)
* ✨ **NOVO**: Coluna "Página de Origem" na tabela de estatísticas
* ✨ **NOVO**: Ícones visuais para origem do clique (🎯 Botão, 📝 Título, 🖼️ Imagem)
* ✨ **NOVO**: Botão "Limpar Dados de Cliques" no painel de estatísticas
* 📊 **MELHORIA**: Gráfico agora usa nomes de produtos ao invés de IDs
* 📊 **MELHORIA**: Estatísticas com informações mais detalhadas e úteis
* 🔧 **MELHORIA**: Labels truncados para melhor legibilidade nos gráficos
* 🔄 **MELHORIA**: Migração automática da tabela para adicionar coluna source_page
* 📱 **MELHORIA**: JavaScript de tracking captura automaticamente a URL da página

= 1.4.10 (2025-11-11) =
* 🔒 **SEGURANÇA CRÍTICA**: Adicionada autenticação ao endpoint REST API de rastreamento de cliques
* 🔒 **SEGURANÇA**: Implementado rate limiting (10 requisições/minuto por IP) no tracker
* 🔒 **SEGURANÇA**: Melhorada validação de MIME type no upload de CSV usando wp_check_filetype_and_ext()
* 🔒 **SEGURANÇA**: Validação adicional de conteúdo CSV (verificação de delimitadores)
* 🔒 **SEGURANÇA**: Corrigida query SQL no painel de estatísticas usando prepared statement
* 🔒 **SEGURANÇA**: Adicionado método seguro para obtenção de IP do cliente
* Recomendação: Atualização urgente para todos os usuários

= 1.2 (2025-01-08) =
* Nova: Estrutura modular completamente refatorada
* Nova: Página de Aparência e Configurações com seções colapsáveis
* Nova: Personalização completa de cores, botões e layout
* Nova: CSS otimizado com carregamento condicional
* Nova: Internacionalização completa (i18n/l10n)
* Melhoria: Performance geral do plugin
* Melhoria: Documentação aprimorada
* Fix: Compatibilidade com PHP 8.2
* Preparado para publicação no WordPress.org

= 1.1 =
* Nova: Estatísticas no dashboard (total de produtos, preço médio, categoria principal)
* Nova: Filtros avançados na página de gerenciar produtos
* Nova: Duplicação de produtos via AJAX
* Nova: Status de links de afiliado (identifica Shopee, Amazon, etc.)
* Nova: Copiar shortcode com um clique
* Melhoria: Interface de gerenciamento de produtos
* Melhoria: Paginação melhorada

= 1.0 =
* Lançamento inicial
* Custom Post Type "affiliate_product"
* Taxonomia "affiliate_category"
* Importação CSV básica
* Shortcodes [affiliate_product] e [affiliate_products]
* Layout responsivo
* Dashboard e gerenciamento de produtos

== Upgrade Notice ==

= 1.2 =
Grande atualização! Estrutura modular, nova página de aparência e muitas melhorias de performance. Recomendamos fazer backup antes de atualizar.

= 1.1 =
Adiciona estatísticas, filtros avançados e duplicação de produtos. Atualização recomendada.

== Additional Information ==

= Desenvolvedor =

Desenvolvido por **Fernando Pimenta**
Website: [fernandopimenta.blog.br](https://fernandopimenta.blog.br)

= Suporte =

Para suporte e dúvidas, visite o [fórum de suporte do WordPress](https://wordpress.org/support/plugin/plugin-afiliados-pro/) ou entre em contato através do site oficial.

= Contribua =

O plugin é open source! Contribua no [GitHub](https://github.com/fernando-pimenta/plugin-afiliados-pro-dev).

= Privacidade =

Este plugin não coleta dados dos usuários. Todas as informações ficam armazenadas localmente no seu banco de dados WordPress.

= Roadmap =

Próximas funcionalidades planejadas:
* Template Builder visual (v1.3)
* Integração com APIs de marketplaces (v1.4)
* Comparador de preços (v1.5)
* Sistema de rastreamento de cliques avançado (v1.6)
* Widgets do Gutenberg (v1.7)

== Credits ==

* Desenvolvido por Fernando Pimenta
* Ícones por [Dashicons](https://developer.wordpress.org/resource/dashicons/)
* Inspirado na comunidade WordPress

== License ==

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
