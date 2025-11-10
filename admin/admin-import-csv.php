<?php
/**
 * Template da página de Importar CSV
 *
 * @package Affiliate_Pro
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Mensagens de feedback
if (isset($_GET['imported']) && $_GET['imported'] > 0) {
    $imported = intval($_GET['imported']);
    $message = sprintf(_n('%d produto importado com sucesso!', '%d produtos importados com sucesso!', $imported, 'afiliados-pro'), $imported);
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';

    if (isset($_GET['errors']) && $_GET['errors'] > 0) {
        $errors = intval($_GET['errors']);
        $error_message = sprintf(_n('%d produto com erro.', '%d produtos com erros.', $errors, 'afiliados-pro'), $errors);
        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
    }
}

if (isset($_GET['error'])) {
    $error_type = sanitize_text_field($_GET['error']);
    $error_messages = array(
        'upload' => __('Erro ao fazer upload do arquivo. Tente novamente.', 'afiliados-pro'),
        'invalid_format' => __('Formato do CSV inválido. Certifique-se de que o arquivo contém pelo menos 6 colunas: Título, Descrição, Preço, Link de Afiliado, URL da Imagem e Categoria.', 'afiliados-pro'),
        'no_data' => __('Nenhum produto encontrado no arquivo CSV. Verifique se o arquivo contém dados além do cabeçalho.', 'afiliados-pro'),
        'invalid_extension' => __('Apenas arquivos .csv são permitidos. Por favor, selecione um arquivo CSV válido.', 'afiliados-pro'),
        'invalid_mime' => __('Tipo de arquivo inválido. Por favor, envie apenas arquivos CSV.', 'afiliados-pro')
    );

    $error_message = isset($error_messages[$error_type]) ? $error_messages[$error_type] : $error_messages['upload'];
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
}
?>

<div class="wrap">
    <h1><?php _e('Importar Produtos via CSV', 'afiliados-pro'); ?></h1>

    <div style="background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px;">
            <strong><?php _e('Precisa de um modelo?', 'afiliados-pro'); ?></strong>
            <?php _e('Baixe um arquivo CSV de exemplo com 3 produtos pré-configurados.', 'afiliados-pro'); ?>
        </p>
        <a href="<?php echo esc_url(admin_url('admin-post.php?action=affiliate_pro_download_csv_example')); ?>"
           class="button button-secondary"
           style="margin-top: 10px;">
            <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: 2px;"></span>
            <?php _e('Baixar CSV de Exemplo', 'afiliados-pro'); ?>
        </a>
    </div>

    <div class="card" style="max-width: 100%;">
        <h2><?php _e('Formato do CSV', 'afiliados-pro'); ?></h2>
        <p><?php _e('O arquivo CSV deve conter as seguintes colunas na primeira linha:', 'afiliados-pro'); ?></p>
        <div style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-wrap: break-word; word-break: break-all; max-width: 100%; margin: 10px 0;">
            <code style="font-size: 12px;">Título,Descrição,Preço,Link de Afiliado,URL da Imagem,Categoria</code>
        </div>

        <h3><?php _e('Exemplo:', 'afiliados-pro'); ?></h3>
        <div style="background: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto; max-width: 100%; margin: 10px 0;">
            <pre style="white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; margin: 0; font-size: 12px; line-height: 1.6;">Título,Descrição,Preço,Link de Afiliado,URL da Imagem,Categoria
Smartphone XYZ,"Smartphone com 128GB de memória",899.99,https://affiliate.com/link1,https://example.com/image1.jpg,eletronicos
Notebook ABC,"Notebook para trabalho e estudos",2499.90,https://affiliate.com/link2,https://example.com/image2.jpg,informatica</pre>
        </div>

        <h3><?php _e('Observações:', 'afiliados-pro'); ?></h3>
        <ul style="line-height: 1.8;">
            <li><?php _e('A primeira linha do CSV deve conter os cabeçalhos (será ignorada na importação)', 'afiliados-pro'); ?></li>
            <li><?php _e('Use vírgula (,) ou ponto e vírgula (;) como separador - o plugin detecta automaticamente', 'afiliados-pro'); ?></li>
            <li><?php _e('Campos com vírgulas ou quebras de linha devem estar entre aspas duplas', 'afiliados-pro'); ?></li>
            <li><?php _e('O preço deve usar ponto (.) como separador decimal', 'afiliados-pro'); ?></li>
            <li><?php _e('A categoria será criada automaticamente se não existir', 'afiliados-pro'); ?></li>
            <li><?php _e('Se a URL da imagem for válida, ela será baixada e definida como imagem destacada', 'afiliados-pro'); ?></li>
            <li><?php _e('Compatível com arquivos salvos no Excel (UTF-8 e ANSI)', 'afiliados-pro'); ?></li>
        </ul>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="margin-top: 20px;">
        <?php wp_nonce_field('import_csv_nonce', 'csv_nonce'); ?>
        <input type="hidden" name="action" value="affiliate_pro_import_csv">

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Arquivo CSV', 'afiliados-pro'); ?></th>
                <td>
                    <input type="file" name="csv_file" accept=".csv" required>
                    <p class="description"><?php _e('Selecione um arquivo CSV com os produtos para importar.', 'afiliados-pro'); ?></p>
                </td>
            </tr>
        </table>

        <p class="submit" style="text-align: left; padding-left: 0;">
            <?php submit_button(__('Importar Produtos', 'afiliados-pro'), 'primary', 'submit', false); ?>
        </p>
    </form>
</div>
