<?php
/**
 * Classe responsável pela importação de produtos via CSV
 *
 * @package PAP
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class PAP_CSV_Import {

    /**
     * Instância única (Singleton)
     *
     * @var PAP_CSV_Import
     */
    private static $instance = null;

    /**
     * Obtém a instância única
     *
     * @return PAP_CSV_Import
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        add_action('admin_post_affiliate_pro_import_csv', array($this, 'handle_csv_import'));
        add_action('admin_post_affiliate_pro_download_csv_example', array($this, 'download_csv_example'));
    }

    /**
     * Processa a importação do CSV
     */
    public function handle_csv_import() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
        }

        if (!isset($_POST['csv_nonce']) || !wp_verify_nonce($_POST['csv_nonce'], 'import_csv_nonce')) {
            wp_die(__('Erro de segurança. Tente novamente.', 'afiliados-pro'));
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg(
                array('page' => 'affiliate-import-csv', 'error' => 'upload'),
                admin_url('admin.php')
            ));
            exit;
        }

        // Validar extensão do arquivo
        $file_name = sanitize_file_name($_FILES['csv_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'csv') {
            wp_redirect(add_query_arg(
                array('page' => 'affiliate-import-csv', 'error' => 'invalid_extension'),
                admin_url('admin.php')
            ));
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];

        // Validar MIME type usando WordPress (mais seguro que confiar no navegador)
        $wp_filetype = wp_check_filetype_and_ext($file, $file_name);
        $allowed_mimes = array('text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel');

        // Verificar se o tipo foi detectado corretamente
        if (!$wp_filetype['type'] || !in_array($wp_filetype['type'], $allowed_mimes)) {
            // Validação adicional: verificar se o arquivo realmente parece ser CSV
            $file_handle = fopen($file, 'r');
            if ($file_handle) {
                $first_line = fgets($file_handle);
                fclose($file_handle);

                // CSV deve ter delimitadores comuns
                if (strpos($first_line, ',') === false &&
                    strpos($first_line, ';') === false &&
                    strpos($first_line, "\t") === false) {
                    wp_redirect(add_query_arg(
                        array('page' => 'affiliate-import-csv', 'error' => 'invalid_mime'),
                        admin_url('admin.php')
                    ));
                    exit;
                }
            } else {
                wp_redirect(add_query_arg(
                    array('page' => 'affiliate-import-csv', 'error' => 'invalid_mime'),
                    admin_url('admin.php')
                ));
                exit;
            }
        }

        // Detectar e corrigir codificação do arquivo
        $content = file_get_contents($file);
        $content = $this->fix_encoding($content);
        file_put_contents($file, $content);

        // Detectar delimitador
        $delimiter = $this->detect_csv_delimiter($file);

        $imported_count = 0;
        $error_count = 0;

        if (($handle = fopen($file, 'r')) !== FALSE) {
            // Primeira linha com cabeçalhos
            $headers = fgetcsv($handle, 0, $delimiter);

            // Validar se tem pelo menos 6 colunas no cabeçalho
            if (!$headers || count($headers) < 6) {
                fclose($handle);
                wp_redirect(add_query_arg(
                    array('page' => 'affiliate-import-csv', 'error' => 'invalid_format'),
                    admin_url('admin.php')
                ));
                exit;
            }

            while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if (count($data) >= 6) {
                    $result = $this->import_product($data);
                    if ($result) {
                        $imported_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
            fclose($handle);
        }

        // Se nenhum produto foi importado, considere erro de formato
        if ($imported_count === 0 && $error_count === 0) {
            wp_redirect(add_query_arg(
                array('page' => 'affiliate-import-csv', 'error' => 'no_data'),
                admin_url('admin.php')
            ));
            exit;
        }

        $redirect_args = array(
            'page' => 'affiliate-import-csv',
            'imported' => $imported_count
        );

        if ($error_count > 0) {
            $redirect_args['errors'] = $error_count;
        }

        wp_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }

    /**
     * Detecta o delimitador do CSV (vírgula ou ponto e vírgula)
     *
     * @param string $file
     * @return string
     */
    private function detect_csv_delimiter($file) {
        $delimiters = array(',', ';', "\t");
        $data = array();
        $file_handle = fopen($file, 'r');

        if (!$file_handle) {
            return ','; // Padrão
        }

        $first_line = fgets($file_handle);
        fclose($file_handle);

        if (!$first_line) {
            return ',';
        }

        foreach ($delimiters as $delimiter) {
            $count = substr_count($first_line, $delimiter);
            $data[$delimiter] = $count;
        }

        // Retorna o delimitador com maior ocorrência
        arsort($data);
        return key($data);
    }

    /**
     * Corrige a codificação do arquivo (UTF-8 com BOM, ANSI, etc.)
     *
     * @param string $content
     * @return string
     */
    private function fix_encoding($content) {
        // Remover BOM (Byte Order Mark) se presente
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        // Detectar e converter codificação
        $encoding = mb_detect_encoding($content, array('UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'), true);

        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return $content;
    }

    /**
     * Importa um único produto
     *
     * @param array $data
     * @return int|false
     */
    private function import_product($data) {
        $title = sanitize_text_field($data[0]);
        $description = sanitize_textarea_field($data[1]);
        $price = floatval($data[2]);
        $affiliate_link = esc_url_raw($data[3]);
        $image_url = esc_url_raw($data[4]);
        $category_name = sanitize_text_field($data[5]);

        // Criar post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish',
            'post_type' => 'affiliate_product'
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            // Salvar meta fields
            update_post_meta($post_id, '_affiliate_price', $price);
            update_post_meta($post_id, '_affiliate_link', $affiliate_link);
            update_post_meta($post_id, '_affiliate_image_url', $image_url);

            // Criar/associar categoria
            if (!empty($category_name)) {
                $this->assign_category($post_id, $category_name);
            }

            // Fazer upload da imagem se URL válida
            if (!empty($image_url) && filter_var($image_url, FILTER_VALIDATE_URL)) {
                $this->set_featured_image_from_url($post_id, $image_url);
            }

            return $post_id;
        }

        return false;
    }

    /**
     * Associa uma categoria ao produto
     *
     * @param int $post_id
     * @param string $category_name
     */
    private function assign_category($post_id, $category_name) {
        $term = get_term_by('name', $category_name, 'affiliate_category');

        if (!$term) {
            $term = wp_insert_term($category_name, 'affiliate_category');
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'];
            } else {
                return;
            }
        } else {
            $term_id = $term->term_id;
        }

        if ($term_id) {
            wp_set_post_terms($post_id, array($term_id), 'affiliate_category');
        }
    }

    /**
     * Define a imagem destacada a partir de uma URL
     *
     * @param int $post_id
     * @param string $image_url
     */
    private function set_featured_image_from_url($post_id, $image_url) {
        // Validar extensão do arquivo
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $image_extension = strtolower(pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION));

        // Verificar se a extensão é permitida
        if (!in_array($image_extension, $allowed_extensions)) {
            error_log('Affiliate Pro: Extensão de imagem não permitida: ' . $image_extension);
            return;
        }

        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');

        if (!is_wp_error($attachment_id)) {
            // Validar MIME type do arquivo baixado
            $mime_type = get_post_mime_type($attachment_id);
            $allowed_mimes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');

            if (!in_array($mime_type, $allowed_mimes)) {
                // Remover arquivo se MIME type inválido
                wp_delete_attachment($attachment_id, true);
                error_log('Affiliate Pro: MIME type inválido detectado: ' . $mime_type);
                return;
            }

            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    /**
     * Gera e faz download de um CSV de exemplo
     */
    public function download_csv_example() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
        }

        // Dados de exemplo
        $csv_data = array(
            array('Título', 'Descrição', 'Preço', 'Link de Afiliado', 'URL da Imagem', 'Categoria'),
            array(
                'Smartphone XYZ Premium',
                'Smartphone com 128GB de memória, câmera 48MP e tela AMOLED de 6.5 polegadas',
                '1899.99',
                'https://shopee.com.br/product/123456789',
                'https://via.placeholder.com/400x400.png?text=Smartphone',
                'Eletrônicos'
            ),
            array(
                'Notebook para Trabalho',
                'Notebook Intel Core i5, 8GB RAM, SSD 256GB, ideal para trabalho e estudos',
                '2499.90',
                'https://amzn.to/3example',
                'https://via.placeholder.com/400x400.png?text=Notebook',
                'Informática'
            ),
            array(
                'Fone de Ouvido Bluetooth',
                'Fone de ouvido sem fio com cancelamento de ruído e bateria de 30 horas',
                '349.90',
                'https://mercadolivre.com.br/product/MLB123456',
                'https://via.placeholder.com/400x400.png?text=Fone',
                'Áudio'
            )
        );

        // Configurar headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="produtos_exemplo.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Abrir stream de saída
        $output = fopen('php://output', 'w');

        // Escrever BOM para UTF-8 (compatibilidade com Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Escrever linhas
        foreach ($csv_data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
