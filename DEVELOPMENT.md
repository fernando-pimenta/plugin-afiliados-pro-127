# Ambiente de Desenvolvimento - Plugin Afiliados Pro

## 📌 Status Atual

**Versão Estável:** v1.2.7
**Branch de Desenvolvimento:** `claude/check-commits-plugin-version-011CUwRSUeovK9tvEpVp1vM2`
**Data de Atualização:** 10/11/2025
**Status do Código:** ✅ Íntegro e Validado

---

## ✅ Confirmações de Integridade

### Versão Confirmada
- ✅ **afiliados-pro.php:6** → `Version: 1.2.7`
- ✅ **afiliados-pro.php:24** → `define('AFFILIATE_PRO_VERSION', '1.2.7');`

### Validação de Sintaxe PHP
Todos os 8 arquivos PHP foram validados com sucesso:
- ✅ `afiliados-pro.php`
- ✅ `includes/class-affiliate-products.php`
- ✅ `includes/class-affiliate-settings.php`
- ✅ `includes/csv-import.php`
- ✅ `includes/shortcodes.php`
- ✅ `admin/admin-manage-products.php`
- ✅ `admin/admin-settings.php`
- ✅ `admin/admin-import-csv.php`

### Estrutura de Arquivos
```
plugin-afiliados-pro-dev/
├── afiliados-pro.php (7.1 KB)
├── readme.txt (7.4 KB)
├── README.md (7.0 KB)
├── LICENSE (18 KB)
│
├── admin/ (61 KB)
│   ├── admin-import-csv.php (4.1 KB)
│   ├── admin-manage-products.php (25 KB)
│   ├── admin-script.js (11 KB)
│   ├── admin-settings.php (16 KB)
│   └── admin-style.css (5.2 KB)
│
├── includes/ (53 KB)
│   ├── class-affiliate-products.php (29 KB)
│   ├── class-affiliate-settings.php (11 KB)
│   ├── csv-import.php (5.3 KB)
│   └── shortcodes.php (7.4 KB)
│
├── public/ (18 KB)
│   ├── affiliate-pro.css (8.9 KB)
│   └── affiliate-pro.js (8.8 KB)
│
└── languages/
    └── afiliados-pro.pot
```

**Total:** ~132 KB de código PHP/CSS/JS

---

## 🚀 Próxima Versão: v1.3

### Objetivo Principal
**Template Builder Visual** - Permitir que usuários criem templates personalizados para exibição de produtos sem editar código.

### Funcionalidades Planejadas
- [ ] Interface visual de drag-and-drop para templates
- [ ] Biblioteca de blocos pré-configurados
- [ ] Preview em tempo real
- [ ] Templates salvos reutilizáveis
- [ ] Exportar/importar templates
- [ ] Suporte a custom fields visuais

### Requisitos Técnicos
- Manter compatibilidade com WordPress 6.0+
- Manter compatibilidade com PHP 8.1+
- Integração com Gutenberg (opcional)
- Performance: Carregamento < 1s
- Responsividade total (mobile-first)

### Arquivos a Serem Criados
```
includes/
  └── class-affiliate-template-builder.php

admin/
  ├── admin-template-builder.php
  ├── template-builder-script.js
  └── template-builder-style.css

public/
  └── templates/
      ├── default-card.php
      ├── compact-list.php
      └── featured-product.php
```

---

## 📋 Histórico de Versões

### v1.2.6 (Atual) - 08/11/2025
- ✨ Refinamento visual e aplicação das opções de aparência no front-end
- 🎨 Sistema de personalização completo
- 🔧 Otimizações de performance

### v1.2.5 - 08/11/2025
- 📊 Exibição de rascunhos e contadores de status na listagem
- 🔢 Contadores de status (Todos/Publicados/Rascunhos)

### v1.2.4 - 08/11/2025
- 🔄 Correção definitiva da duplicação de produtos
- ✅ Criação real no banco de dados

### v1.2.3 - 08/11/2025
- 🛠️ Correção completa da duplicação de produtos
- 📝 Validação robusta e logs detalhados

### v1.2.2 - 08/11/2025
- 🐛 Correção do AJAX de duplicação
- 🧹 Limpeza do pacote ZIP (arquivos legados removidos)

### v1.2.1 - 08/11/2025
- 🔧 Correções de inicialização e WP_Error
- 📈 Estabilidade nas páginas admin e dashboard

### v1.2.0 - 08/11/2025
- 🏗️ Estrutura modular completa
- 🎨 Painel de aparência e otimização de CSS

---

## 🛠️ Comandos Úteis

### Validação de Código
```bash
# Validar sintaxe PHP
find . -name "*.php" -exec php -l {} \;

# Contar linhas de código
wc -l afiliados-pro.php includes/*.php admin/*.php

# Ver estrutura de diretórios
tree -L 2 -h
```

### Git
```bash
# Ver histórico
git log --oneline --graph --all

# Status
git status

# Push para o branch
git push -u origin claude/check-commits-plugin-version-011CUwRSUeovK9tvEpVp1vM2
```

### WordPress
```bash
# Ativar plugin (via WP-CLI)
wp plugin activate plugin-afiliados-pro-dev

# Verificar erros
wp plugin list --status=must-use,dropin
```

---

## 📚 Documentação de Referência

### WordPress
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Custom Post Types](https://developer.wordpress.org/plugins/post-types/)

### PHP
- [PHP 8.1 Documentation](https://www.php.net/manual/en/)
- [PSR-12 Coding Style](https://www.php-fig.org/psr/psr-12/)

### JavaScript
- [jQuery Documentation](https://api.jquery.com/)
- [Gutenberg Handbook](https://developer.wordpress.org/block-editor/)

---

## 🔐 Segurança

### Validações Implementadas
- ✅ Nonce verificado em todos os formulários
- ✅ Sanitização de inputs (sanitize_text_field, esc_url, etc.)
- ✅ Escape de outputs (esc_html, esc_attr, etc.)
- ✅ Verificação de permissões (current_user_can)
- ✅ Prevenção de acesso direto (ABSPATH)

### Checklist para v1.3
- [ ] Validar nonce no Template Builder
- [ ] Sanitizar campos personalizados
- [ ] Verificar permissões de upload de templates
- [ ] Escapar HTML nos templates customizados
- [ ] Validar JSON de configurações

---

## 🧪 Testes

### Ambientes de Teste
- WordPress 6.0, 6.5, 6.7
- PHP 8.1, 8.2, 8.3
- Navegadores: Chrome, Firefox, Safari, Edge

### Checklist de Testes
- [ ] Criar produto manualmente
- [ ] Importar CSV com 100+ produtos
- [ ] Duplicar produto via AJAX
- [ ] Exibir [affiliate_product id="X"]
- [ ] Exibir [affiliate_products limit="9"]
- [ ] Personalizar cores no painel
- [ ] Verificar responsividade mobile
- [ ] Testar em tema padrão (Twenty Twenty-Four)

---

## 📞 Contato

**Desenvolvedor:** Fernando Pimenta
**Website:** [fernandopimenta.blog.br](https://fernandopimenta.blog.br)
**GitHub:** [@fernando-pimenta](https://github.com/fernando-pimenta)

---

**Última atualização:** 09/11/2025
**Preparado por:** Claude (Assistente de Desenvolvimento)
