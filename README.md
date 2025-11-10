# Plugin Afiliados Pro

![Version](https://img.shields.io/badge/version-1.2.7-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-brightgreen.svg)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPLv2%2B-red.svg)

Plugin WordPress profissional para gerenciamento e exibição de produtos afiliados com importação CSV, shortcodes personalizáveis e painel de aparência visual completo.

---

## 📋 Descrição

O **Plugin Afiliados Pro** é uma solução completa para WordPress que permite criar, gerenciar e exibir produtos afiliados de forma profissional e atrativa. Ideal para sites de comparação, blogs de review, e portais de cupons.

### ✨ Principais Recursos

- 🎨 **Painel de Aparência Visual** - Personalize cores, bordas, botões e layout sem tocar em código
- 📊 **Dashboard Completo** - Visualize estatísticas e gerencie produtos facilmente
- 📁 **Importação CSV** - Importe centenas de produtos de uma só vez
- 🎯 **Shortcodes Flexíveis** - Exiba produtos individuais ou grades personalizadas
- 🏷️ **Sistema de Categorias** - Organize produtos por categorias hierárquicas
- 🔄 **Duplicação de Produtos** - Clone produtos com um clique
- 📱 **Totalmente Responsivo** - Visual perfeito em desktop, tablet e mobile
- 🌐 **Pronto para Tradução** - Suporte completo a i18n
- ⚡ **Otimizado** - Carregamento condicional de CSS/JS

---

## 📦 Instalação

### Via GitHub

1. Clone o repositório:
```bash
git clone https://github.com/fernando-pimenta/plugin-afiliados-pro-dev.git
```

2. Copie a pasta para `/wp-content/plugins/`:
```bash
cp -r plugin-afiliados-pro-dev /caminho/para/wordpress/wp-content/plugins/
```

3. Ative o plugin no painel do WordPress

### Via Download Manual

1. Baixe o arquivo ZIP do repositório
2. No WordPress, vá em **Plugins → Adicionar Novo**
3. Clique em **Enviar Plugin** e selecione o arquivo ZIP
4. Clique em **Instalar Agora** e depois **Ativar**

---

## 🚀 Uso Rápido

### 1. Adicionar Produtos

Acesse **Afiliados → Adicionar Produto** e preencha:
- Título do produto
- Descrição
- Preço
- Link de afiliado
- Imagem destacada
- Categoria

### 2. Importar via CSV

Acesse **Afiliados → Importar CSV** e use o seguinte formato:

```csv
Título,Descrição,Preço,Link de Afiliado,URL da Imagem,Categoria
Smartphone XYZ,"Smartphone com 128GB",899.99,https://link.com,https://img.jpg,eletronicos
```

### 3. Usar Shortcodes

**Produto único:**
```
[affiliate_product id="123"]
```

**Grade de produtos:**
```
[affiliate_products limit="6" category="eletronicos" columns="3"]
```

### 4. Personalizar Aparência

Acesse **Afiliados → Aparência e Configurações** para personalizar:
- Cores do card e botões
- Arredondamento das bordas
- Layout (grade ou lista)
- Número de colunas
- Formato de preços
- CSS customizado

---

## 🎨 Capturas de Tela

*(Adicione capturas de tela aqui quando disponíveis)*

1. Dashboard principal com estatísticas
2. Página de gerenciamento de produtos com filtros
3. Painel de aparência e configurações
4. Grade de produtos no frontend
5. Card de produto individual

---

## 📖 Documentação Completa

### Shortcodes Disponíveis

#### `[affiliate_product]`

Exibe um único produto.

**Atributos:**
- `id` (obrigatório) - ID do produto

**Exemplo:**
```
[affiliate_product id="42"]
```

#### `[affiliate_products]`

Exibe uma grade de produtos.

**Atributos:**
- `limit` (opcional, padrão: 6) - Número de produtos
- `category` (opcional) - Slug da categoria
- `layout` (opcional: grid|list) - Tipo de layout
- `columns` (opcional: 2-4) - Número de colunas

**Exemplos:**
```
[affiliate_products limit="9" columns="3"]
[affiliate_products category="smartphones" limit="6"]
[affiliate_products layout="list" limit="5"]
```

---

## 🔧 Estrutura do Projeto

```
plugin-afiliados-pro/
├── afiliados-pro.php          # Arquivo principal
├── readme.txt                 # WordPress.org readme
├── README.md                  # Este arquivo
│
├── /includes                  # Lógica PHP
│   ├── class-affiliate-products.php
│   ├── class-affiliate-settings.php
│   ├── csv-import.php
│   └── shortcodes.php
│
├── /admin                     # Interface admin
│   ├── admin-settings.php
│   ├── admin-import-csv.php
│   ├── admin-manage-products.php
│   ├── admin-style.css
│   └── admin-script.js
│
├── /public                    # Frontend
│   ├── affiliate-pro.css
│   └── affiliate-pro.js
│
├── /languages                 # Traduções
│   └── afiliados-pro.pot
│
└── /assets-wporg              # Assets WordPress.org
    ├── banner-772x250.png
    ├── icon-128x128.png
    └── screenshot-*.png
```

---

## 🛠️ Requisitos Técnicos

- **WordPress:** 6.0 ou superior
- **PHP:** 8.1 ou superior
- **MySQL:** 5.7 ou superior

---

## 📝 Changelog

### v1.2 (2025-01-08)
- ✨ Estrutura modular completamente refatorada
- 🎨 Nova página de Aparência e Configurações
- ⚡ CSS otimizado com carregamento condicional
- 🌐 Internacionalização completa
- 📚 Documentação aprimorada
- 🔧 Preparado para publicação no WordPress.org

### v1.1
- Adicionadas estatísticas no dashboard
- Filtros avançados na página de gerenciar produtos
- Duplicação de produtos via AJAX
- Status de links de afiliado

### v1.0
- Lançamento inicial
- Custom Post Type e Taxonomia
- Importação CSV
- Shortcodes básicos

---

## 👤 Autor

**Fernando Pimenta**

- Website: [fernandopimenta.blog.br](https://fernandopimenta.blog.br)
- GitHub: [@fernando-pimenta](https://github.com/fernando-pimenta)

---

## 📄 Licença

Este projeto está licenciado sob a GPL v2 ou posterior - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para:

1. Fazer um Fork do projeto
2. Criar uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abrir um Pull Request

---

## 🐛 Reportar Bugs

Encontrou um bug? Por favor, abra uma [issue](https://github.com/fernando-pimenta/plugin-afiliados-pro-dev/issues) com:

- Descrição detalhada do problema
- Passos para reproduzir
- Versão do WordPress e PHP
- Screenshots (se aplicável)

---

## ⭐ Apoie o Projeto

Se este plugin foi útil para você, considere:

- ⭐ Dar uma estrela no GitHub
- 🐦 Compartilhar nas redes sociais
- ☕ [Fazer uma doação](https://fernandopimenta.blog.br/doar)

---

## 🔮 Roadmap

- [ ] Template Builder visual (v1.3)
- [ ] Integração com APIs de marketplaces (v1.4)
- [ ] Comparador de preços (v1.5)
- [ ] Sistema de rastreamento de cliques (v1.6)
- [ ] Widgets do Gutenberg (v1.7)

---

**Desenvolvido com ❤️ por [Fernando Pimenta](https://fernandopimenta.blog.br)**
