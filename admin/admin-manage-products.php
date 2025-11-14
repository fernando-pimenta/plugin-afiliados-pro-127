<?php
/**
 * Template da página de Gerenciar Produtos
 *
 * @package PAP
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Processar ações em lote
if (isset($_POST['bulk_action']) && isset($_POST['product_ids']) && wp_verify_nonce($_POST['bulk_nonce'], 'bulk_action_nonce')) {
    $action = sanitize_text_field($_POST['bulk_action']);
    $product_ids = array_map('intval', $_POST['product_ids']);
    $count = 0;

    switch ($action) {
        case 'trash':
            foreach ($product_ids as $product_id) {
                if (wp_trash_post($product_id)) {
                    $count++;
                }
            }
            if ($count > 0) {
                // Limpar cache de preço médio após mover para lixeira
                delete_transient('affiliate_pro_avg_price');
            }
            echo '<div class="notice notice-success"><p>' . sprintf(__('%d produto(s) movido(s) para a lixeira!', 'afiliados-pro'), $count) . '</p></div>';
            break;

        case 'delete':
            foreach ($product_ids as $product_id) {
                // Só permite exclusão permanente se estiver na lixeira
                $post_status = get_post_status($product_id);
                if ($post_status === 'trash') {
                    if (wp_delete_post($product_id, true)) {
                        $count++;
                    }
                }
            }
            if ($count > 0) {
                // Limpar cache de preço médio após exclusão permanente
                delete_transient('affiliate_pro_avg_price');
                echo '<div class="notice notice-success"><p>' . sprintf(__('%d produto(s) excluído(s) permanentemente!', 'afiliados-pro'), $count) . '</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>' . __('Apenas produtos na lixeira podem ser excluídos permanentemente.', 'afiliados-pro') . '</p></div>';
            }
            break;

        case 'restore':
            foreach ($product_ids as $product_id) {
                if (wp_untrash_post($product_id)) {
                    $count++;
                }
            }
            if ($count > 0) {
                // Limpar cache de preço médio após restaurar da lixeira
                delete_transient('affiliate_pro_avg_price');
            }
            echo '<div class="notice notice-success"><p>' . sprintf(__('%d produto(s) restaurado(s) da lixeira!', 'afiliados-pro'), $count) . '</p></div>';
            break;

        case 'duplicate':
            $products_instance = PAP_Products::get_instance();
            foreach ($product_ids as $product_id) {
                if ($products_instance->duplicate_product($product_id)) {
                    $count++;
                }
            }
            echo '<div class="notice notice-success"><p>' . sprintf(__('%d produto(s) duplicado(s) com sucesso!', 'afiliados-pro'), $count) . '</p></div>';
            break;
    }
}

// Filtros
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$category_filter = isset($_GET['category_filter']) ? sanitize_text_field($_GET['category_filter']) : '';
$price_range = isset($_GET['price_range']) ? sanitize_text_field($_GET['price_range']) : '';
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

// Filtro de status (novo em v1.2.5)
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

if ($status_filter === 'publish') {
    $post_status = array('publish');
} elseif ($status_filter === 'draft') {
    $post_status = array('draft');
} elseif ($status_filter === 'trash') {
    $post_status = array('trash');
} else {
    $post_status = array('publish', 'draft');
}

$paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$posts_per_page = 20;

$args = array(
    'post_type' => 'affiliate_product',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => $post_status,
    'orderby' => $orderby,
    'order' => $order
);

// Filtro de busca
if (!empty($search)) {
    $args['s'] = $search;
}

// Filtro por categoria
if (!empty($category_filter)) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'affiliate_category',
            'field' => 'slug',
            'terms' => $category_filter
        )
    );
}

// Filtro por preço
if (!empty($price_range)) {
    $args['meta_query'] = array();
    switch ($price_range) {
        case '0-50':
            $args['meta_query'][] = array(
                'key' => '_affiliate_price',
                'value' => array(0, 50),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN'
            );
            break;
        case '50-200':
            $args['meta_query'][] = array(
                'key' => '_affiliate_price',
                'value' => array(50, 200),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN'
            );
            break;
        case '200-500':
            $args['meta_query'][] = array(
                'key' => '_affiliate_price',
                'value' => array(200, 500),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN'
            );
            break;
        case '500+':
            $args['meta_query'][] = array(
                'key' => '_affiliate_price',
                'value' => 500,
                'type' => 'DECIMAL',
                'compare' => '>'
            );
            break;
    }
}

// Adicionar meta_key se ordenando por preço
if ($orderby == 'meta_value_num') {
    $args['meta_key'] = '_affiliate_price';
}

$query = new WP_Query($args);

// Buscar categorias para o filtro
$categories_result = get_terms(array(
    'taxonomy' => 'affiliate_category',
    'hide_empty' => false
));
$categories = (!is_wp_error($categories_result)) ? $categories_result : array();

// Estatísticas
global $wpdb;
$products_count_obj = wp_count_posts('affiliate_product');
$total_products = (is_object($products_count_obj) && isset($products_count_obj->publish)) ? $products_count_obj->publish : 0;

// Calcular preço médio
if ($total_products === 0) {
    // Se não há produtos, média é zero e cache deve ser limpo
    $avg_price = 0;
    delete_transient('affiliate_pro_avg_price');
} else {
    // Tentar obter preço médio do cache
    $avg_price = get_transient('affiliate_pro_avg_price');

    if (false === $avg_price) {
        // Cache não existe, calcular e armazenar
        $avg_price_result = $wpdb->get_var($wpdb->prepare("
            SELECT AVG(CAST(meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = %s
            AND p.post_type = %s
            AND p.post_status = %s
            AND pm.meta_value != ''
        ", '_affiliate_price', 'affiliate_product', 'publish'));

        $avg_price = $avg_price_result ? floatval($avg_price_result) : 0;

        // Armazenar no cache por 1 hora
        set_transient('affiliate_pro_avg_price', $avg_price, HOUR_IN_SECONDS);
    }
}

// Calcular categoria principal
if ($total_products === 0) {
    // Se não há produtos, categoria é N/A
    $top_category = 'N/A';
} else {
    $top_categories_result = get_terms(array(
        'taxonomy' => 'affiliate_category',
        'hide_empty' => true,
        'orderby' => 'count',
        'order' => 'DESC',
        'number' => 1
    ));

    if (!is_wp_error($top_categories_result) && !empty($top_categories_result)) {
        $top_category = $top_categories_result[0]->name;
    } else {
        $top_category = 'N/A';
    }
}

/**
 * Verifica o status de um link de afiliado
 */
function affiliate_pro_check_link_status($url) {
    if (empty($url)) {
        return array(
            'class' => 'status-error',
            'text' => __('Sem link', 'afiliados-pro'),
            'title' => __('Nenhum link de afiliado configurado', 'afiliados-pro')
        );
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return array(
            'class' => 'status-error',
            'text' => __('URL inválida', 'afiliados-pro'),
            'title' => __('O link não é uma URL válida', 'afiliados-pro')
        );
    }

    // Verificar marketplaces conhecidos com aliases
    $marketplaces = array(
        'Amazon' => array('amazon.com', 'amazon.com.br', 'amzn.to', 'amzn.com'),
        'Shopee' => array('shopee.com', 'shopee.com.br', 'shp.ee', 's.shopee.com'),
        'Mercado Livre' => array('mercadolivre.com', 'mercadolivre.com.br', 'mercadolibre.com', 'meli.com', 'produto.mercadolivre.com'),
        'Magazine Luiza' => array('magazineluiza.com', 'magazineluiza.com.br', 'magalu.com'),
        'Americanas' => array('americanas.com', 'americanas.com.br'),
        'AliExpress' => array('aliexpress.com', 'pt.aliexpress.com', 's.click.aliexpress.com'),
        'Kabum' => array('kabum.com', 'kabum.com.br'),
        'Casas Bahia' => array('casasbahia.com', 'casasbahia.com.br')
    );

    foreach ($marketplaces as $name => $domains) {
        foreach ($domains as $domain) {
            if (strpos($url, $domain) !== false) {
                return array(
                    'class' => 'status-ok',
                    'text' => $name . ' ✓',
                    'title' => sprintf(__('Link do %s configurado', 'afiliados-pro'), $name)
                );
            }
        }
    }

    return array(
        'class' => 'status-warning',
        'text' => __('Link genérico ⚠️', 'afiliados-pro'),
        'title' => __('Link configurado mas não identificado como marketplace conhecido', 'afiliados-pro')
    );
}
?>

<div class="wrap">
    <h1>
        <?php _e('Gerenciar Produtos Afiliados', 'afiliados-pro'); ?>
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=affiliate_product')); ?>" class="page-title-action">
            <?php _e('Adicionar Novo', 'afiliados-pro'); ?>
        </a>
    </h1>

    <!-- Estatísticas Rápidas -->
    <div class="affiliate-stats" style="display: flex; gap: 15px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 150px;">
            <h4 style="margin: 0; color: #666;"><?php _e('Total de Produtos', 'afiliados-pro'); ?></h4>
            <p style="font-size: 20px; color: #0073aa; margin: 5px 0 0;"><?php echo esc_html($total_products); ?></p>
        </div>
        <div class="stat-card" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 150px;">
            <h4 style="margin: 0; color: #666;"><?php _e('Preço Médio', 'afiliados-pro'); ?></h4>
            <p style="font-size: 20px; color: #27ae60; margin: 5px 0 0;">R$ <?php echo number_format($avg_price, 2, ',', '.'); ?></p>
        </div>
        <div class="stat-card" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 150px;">
            <h4 style="margin: 0; color: #666;"><?php _e('Categoria Principal', 'afiliados-pro'); ?></h4>
            <p style="font-size: 16px; color: #8e44ad; margin: 5px 0 0;"><?php echo esc_html($top_category); ?></p>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="tablenav top">
        <form method="get" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <input type="hidden" name="page" value="affiliate-manage-products">

            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Buscar produtos...', 'afiliados-pro'); ?>" style="width: 200px;">

            <select name="category_filter">
                <option value=""><?php _e('Todas as categorias', 'afiliados-pro'); ?></option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($category_filter, $category->slug); ?>>
                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="price_range">
                <option value=""><?php _e('Todos os preços', 'afiliados-pro'); ?></option>
                <option value="0-50" <?php selected($price_range, '0-50'); ?>>R$ 0 - R$ 50</option>
                <option value="50-200" <?php selected($price_range, '50-200'); ?>>R$ 50 - R$ 200</option>
                <option value="200-500" <?php selected($price_range, '200-500'); ?>>R$ 200 - R$ 500</option>
                <option value="500+" <?php selected($price_range, '500+'); ?>><?php _e('Acima de R$ 500', 'afiliados-pro'); ?></option>
            </select>

            <select name="orderby">
                <option value="date" <?php selected($orderby, 'date'); ?>><?php _e('Data', 'afiliados-pro'); ?></option>
                <option value="title" <?php selected($orderby, 'title'); ?>><?php _e('Título', 'afiliados-pro'); ?></option>
                <option value="meta_value_num" <?php selected($orderby, 'meta_value_num'); ?>><?php _e('Preço', 'afiliados-pro'); ?></option>
            </select>

            <select name="order">
                <option value="DESC" <?php selected($order, 'DESC'); ?>><?php _e('Decrescente', 'afiliados-pro'); ?></option>
                <option value="ASC" <?php selected($order, 'ASC'); ?>><?php _e('Crescente', 'afiliados-pro'); ?></option>
            </select>

            <input type="submit" class="button" value="<?php esc_attr_e('Filtrar', 'afiliados-pro'); ?>">

            <?php if ($search || $category_filter || $price_range) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manage-products')); ?>" class="button">
                    <?php _e('Limpar Filtros', 'afiliados-pro'); ?>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Contador de Status (v1.2.5) -->
    <?php
    $counts = wp_count_posts('affiliate_product');
    $total_all   = intval($counts->publish) + intval($counts->draft);
    $total_pub   = intval($counts->publish);
    $total_draft = intval($counts->draft);
    $total_trash = intval($counts->trash);

    $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
    $base_url = admin_url('admin.php?page=affiliate-manage-products');

    // Preservar outros parâmetros de filtro na URL
    $filter_params = array();
    if (!empty($search)) $filter_params['s'] = $search;
    if (!empty($category_filter)) $filter_params['category_filter'] = $category_filter;
    if (!empty($price_range)) $filter_params['price_range'] = $price_range;
    if (!empty($orderby) && $orderby !== 'date') $filter_params['orderby'] = $orderby;
    if (!empty($order) && $order !== 'DESC') $filter_params['order'] = $order;
    ?>

    <style>
        .affiliate-filters {
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #dcdcde;
        }
        .affiliate-filters a {
            text-decoration: none;
            margin-right: 8px;
            color: #2271b1;
            font-weight: 500;
        }
        .affiliate-filters a.current {
            font-weight: 700;
            color: #000;
        }
        .affiliate-filters a:hover {
            color: #135e96;
        }
    </style>

    <div class="affiliate-filters">
        <?php
        $all_url = add_query_arg(array_merge($filter_params, array('status' => 'all')), $base_url);
        $pub_url = add_query_arg(array_merge($filter_params, array('status' => 'publish')), $base_url);
        $draft_url = add_query_arg(array_merge($filter_params, array('status' => 'draft')), $base_url);
        $trash_url = add_query_arg(array_merge($filter_params, array('status' => 'trash')), $base_url);
        ?>
        <a href="<?php echo esc_url($all_url); ?>" class="<?php echo ($current_status === 'all' ? 'current' : ''); ?>">
            <?php printf(__('Todos (%d)', 'afiliados-pro'), $total_all); ?>
        </a> |
        <a href="<?php echo esc_url($pub_url); ?>" class="<?php echo ($current_status === 'publish' ? 'current' : ''); ?>">
            <?php printf(__('Publicados (%d)', 'afiliados-pro'), $total_pub); ?>
        </a> |
        <a href="<?php echo esc_url($draft_url); ?>" class="<?php echo ($current_status === 'draft' ? 'current' : ''); ?>">
            <?php printf(__('Rascunhos (%d)', 'afiliados-pro'), $total_draft); ?>
        </a>
        <?php if ($total_trash > 0) : ?>
         | <a href="<?php echo esc_url($trash_url); ?>" class="<?php echo ($current_status === 'trash' ? 'current' : ''); ?>">
            <?php printf(__('Lixeira (%d)', 'afiliados-pro'), $total_trash); ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Ações em Lote -->
    <form method="post">
        <?php wp_nonce_field('bulk_action_nonce', 'bulk_nonce'); ?>

        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value=""><?php _e('Ações em lote', 'afiliados-pro'); ?></option>
                    <?php if ($status_filter === 'trash') : ?>
                        <option value="restore"><?php _e('Restaurar', 'afiliados-pro'); ?></option>
                        <option value="delete"><?php _e('Excluir Permanentemente', 'afiliados-pro'); ?></option>
                    <?php else : ?>
                        <option value="trash"><?php _e('Mover para Lixeira', 'afiliados-pro'); ?></option>
                        <option value="duplicate"><?php _e('Duplicar', 'afiliados-pro'); ?></option>
                    <?php endif; ?>
                </select>
                <input type="submit" class="button action" value="<?php esc_attr_e('Aplicar', 'afiliados-pro'); ?>">
            </div>

            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo sprintf(__('%d itens', 'afiliados-pro'), $query->found_posts); ?></span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all">
                    </td>
                    <th class="manage-column" style="width: 60px;"><?php _e('Imagem', 'afiliados-pro'); ?></th>
                    <th class="manage-column"><?php _e('Título', 'afiliados-pro'); ?></th>
                    <th class="manage-column" style="width: 100px;"><?php _e('Preço', 'afiliados-pro'); ?></th>
                    <th class="manage-column" style="width: 120px;"><?php _e('Categoria', 'afiliados-pro'); ?></th>
                    <th class="manage-column" style="width: 100px;"><?php _e('Data', 'afiliados-pro'); ?></th>
                    <th class="manage-column" style="width: 120px;"><?php _e('Status Link', 'afiliados-pro'); ?></th>
                    <th class="manage-column" style="width: 200px;"><?php _e('Shortcode', 'afiliados-pro'); ?></th>
                    <th class="manage-column" style="width: 150px;"><?php _e('Ações', 'afiliados-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->have_posts()) : ?>
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php
                        $product_id = get_the_ID();
                        $price = get_post_meta($product_id, '_affiliate_price', true);
                        $link = get_post_meta($product_id, '_affiliate_link', true);
                        $link_status = affiliate_pro_check_link_status($link);
                        $shortcode = '[pap_product id="' . $product_id . '"]';
                        ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="product_ids[]" value="<?php echo $product_id; ?>">
                            </th>
                            <td>
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($product_id, 'thumbnail')); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                         alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php else : ?>
                                    <div style="width: 50px; height: 50px; background: #f0f0f1; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                        <span style="color: #666; font-size: 12px;"><?php _e('Sem img', 'afiliados-pro'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(get_edit_post_link()); ?>" style="text-decoration: none;">
                                        <?php
                                        $title = wp_trim_words(get_the_title(), 8, '...');
                                        echo esc_html($title);
                                        if (get_post_status() === 'draft') {
                                            echo ' <span style="color:#888;font-weight:normal;">' . __('(Rascunho)', 'afiliados-pro') . '</span>';
                                        }
                                        ?>
                                    </a>
                                </strong>
                                <div style="font-size: 12px; color: #666; margin-top: 3px;">
                                    ID: <?php echo $product_id; ?> |
                                    <a href="<?php echo esc_url(get_permalink()); ?>" target="_blank" style="color: #0073aa;"><?php _e('Ver página', 'afiliados-pro'); ?></a>
                                </div>
                            </td>
                            <td>
                                <strong style="color: <?php echo !empty($price) ? '#27ae60' : '#e74c3c'; ?>">
                                    <?php echo !empty($price) ? 'R$ ' . number_format(floatval($price), 2, ',', '.') : 'N/A'; ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $terms = get_the_terms($product_id, 'affiliate_category');
                                if ($terms && !is_wp_error($terms)) {
                                    foreach ($terms as $term) {
                                        echo '<span style="background: #e1e1e1; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-right: 3px;">' . esc_html($term->name) . '</span>';
                                    }
                                } else {
                                    echo '<span style="color: #999;">' . __('Sem categoria', 'afiliados-pro') . '</span>';
                                }
                                ?>
                            </td>
                            <td style="font-size: 12px; color: #666;">
                                <?php echo get_the_date('d/m/Y'); ?>
                            </td>
                            <td>
                                <?php if (!empty($link)) : ?>
                                    <span class="link-status <?php echo esc_attr($link_status['class']); ?>"
                                          title="<?php echo esc_attr($link_status['title']); ?>">
                                        <?php echo esc_html($link_status['text']); ?>
                                    </span>
                                <?php else : ?>
                                    <span style="color: #e74c3c;"><?php _e('Sem link', 'afiliados-pro'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="shortcode-container" style="position: relative;">
                                    <input type="text"
                                           value="<?php echo esc_attr($shortcode); ?>"
                                           readonly
                                           style="width: 160px; font-size: 11px; padding: 4px; border: 1px solid #ddd; border-radius: 3px; background: #f9f9f9;">
                                    <button type="button"
                                            class="copy-shortcode button button-small"
                                            data-shortcode="<?php echo esc_attr($shortcode); ?>"
                                            style="margin-left: 5px; padding: 2px 8px; font-size: 11px;"
                                            title="<?php esc_attr_e('Copiar shortcode', 'afiliados-pro'); ?>">
                                        📋
                                    </button>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="button button-small"><?php _e('Editar', 'afiliados-pro'); ?></a>
                                <a href="#" class="button button-small duplicate-product" data-id="<?php echo $product_id; ?>"><?php _e('Duplicar', 'afiliados-pro'); ?></a>
                                <a href="<?php echo esc_url(get_delete_post_link($product_id)); ?>"
                                   class="button button-small"
                                   style="color: #a00;"
                                   onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja excluir este produto?', 'afiliados-pro'); ?>')">
                                    <?php _e('Excluir', 'afiliados-pro'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <div style="color: #666;">
                                <p><strong><?php _e('Nenhum produto encontrado.', 'afiliados-pro'); ?></strong></p>
                                <?php if ($search || $category_filter || $price_range) : ?>
                                    <p><?php _e('Tente ajustar os filtros ou', 'afiliados-pro'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manage-products')); ?>"><?php _e('remover todos os filtros', 'afiliados-pro'); ?></a>.</p>
                                <?php else : ?>
                                    <p><a href="<?php echo esc_url(admin_url('post-new.php?post_type=affiliate_product')); ?>" class="button button-primary"><?php _e('Adicionar primeiro produto', 'afiliados-pro'); ?></a></p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>

    <!-- Paginação -->
    <?php if ($query->max_num_pages > 1) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $big = 999999999;
                $pagination = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '?paged=%#%',
                    'current' => max(1, $paged),
                    'total' => $query->max_num_pages,
                    'prev_text' => __('‹ Anterior', 'afiliados-pro'),
                    'next_text' => __('Próximo ›', 'afiliados-pro'),
                    'show_all' => false,
                    'end_size' => 2,
                    'mid_size' => 1
                ));
                echo $pagination;
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
