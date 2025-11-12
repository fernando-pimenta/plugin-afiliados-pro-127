=== Plugin Afiliados Pro ===
Contributors: fernandopimenta
Donate link: https://fernandopimenta.blog.br/doar
Tags: affiliate, affiliates, products, csv-import, catalog, ecommerce, shopee, amazon, marketplace
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.5.6
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
`[affiliate_product id="123"]`

**Grade de produtos:**
`[affiliate_products limit="6" category="eletronicos" columns="3"]`

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
