<?php
/**
 * PAP - Estatísticas de Cliques
 *
 * @package PAP
 * @version 1.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handler para limpar dados de cliques
if (isset($_POST['affiliate_clear_stats']) &&
    check_admin_referer('affiliate_clear_stats', 'affiliate_clear_stats_nonce')) {

    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para executar esta ação.', 'afiliados-pro'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'affiliate_clicks';

    $wpdb->query("TRUNCATE TABLE {$table}");

    echo '<div class="notice notice-success is-dismissible" role="status"><p>';
    echo esc_html__('Todos os registros de cliques foram excluídos com sucesso.', 'afiliados-pro');
    echo '</p></div>';
}

global $wpdb;
$table = $wpdb->prefix . 'affiliate_clicks';

// Verificar se a tabela existe (usando prepared statement)
$table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table;

// Período selecionado
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;

// Consulta agregada com JOIN para buscar nome do produto
$results = array();
$total_clicks = 0;

if ($table_exists) {
    // Query com JOIN para obter o nome do produto
    $query = $wpdb->prepare("
        SELECT
            c.product_id,
            p.post_title as product_name,
            c.source,
            c.source_page,
            COUNT(*) as total
        FROM {$table} c
        LEFT JOIN {$wpdb->posts} p ON c.product_id = p.ID
        WHERE c.clicked_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
        GROUP BY c.product_id, c.source, c.source_page
        ORDER BY total DESC
        LIMIT 100
    ", $days);

    $results = $wpdb->get_results($query);

    // Calcular total de cliques
    foreach ($results as $row) {
        $total_clicks += intval($row->total);
    }
}

// Preparar dados para o gráfico (agrupar por produto com nome)
$chart_data = array();
$chart_labels = array();

if ($results) {
    $product_clicks = array();

    foreach ($results as $row) {
        if (!isset($product_clicks[$row->product_id])) {
            $product_clicks[$row->product_id] = array(
                'total' => 0,
                'name' => $row->product_name
            );
        }
        $product_clicks[$row->product_id]['total'] += intval($row->total);
    }

    // Ordenar por total e limitar a 10
    uasort($product_clicks, function($a, $b) {
        return $b['total'] - $a['total'];
    });

    $product_clicks = array_slice($product_clicks, 0, 10, true);

    // Preparar labels e data para o gráfico
    foreach ($product_clicks as $product_id => $data) {
        $label = $data['name'] ?
            wp_trim_words($data['name'], 4, '...') :
            sprintf(__('Produto #%s', 'afiliados-pro'), $product_id);

        $chart_labels[] = $label;
        $chart_data[] = $data['total'];
    }
}
?>

<div class="wrap">
    <h1>📊 <?php _e('Estatísticas de Cliques', 'afiliados-pro'); ?></h1>
    <p><?php _e('Monitoramento dos cliques registrados nos produtos afiliados.', 'afiliados-pro'); ?></p>

    <?php if (!$table_exists): ?>
        <div class="notice notice-warning">
            <p><?php _e('A tabela de rastreamento de cliques não foi encontrada. Por favor, desative e reative o plugin para criar a tabela.', 'afiliados-pro'); ?></p>
        </div>
    <?php else: ?>

        <!-- Filtro de Período -->
        <form method="get" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="affiliate-stats">
            <label for="days"><strong><?php _e('Período:', 'afiliados-pro'); ?></strong></label>
            <select name="days" id="days" onchange="this.form.submit()" style="margin-left: 10px;" aria-label="<?php esc_attr_e('Selecionar período de análise', 'afiliados-pro'); ?>">
                <option value="7" <?php selected($days, 7); ?>><?php _e('Últimos 7 dias', 'afiliados-pro'); ?></option>
                <option value="30" <?php selected($days, 30); ?>><?php _e('Últimos 30 dias', 'afiliados-pro'); ?></option>
                <option value="90" <?php selected($days, 90); ?>><?php _e('Últimos 90 dias', 'afiliados-pro'); ?></option>
            </select>
        </form>

        <!-- Total de Cliques -->
        <div class="card" style="max-width: 300px; padding: 20px; margin-bottom: 20px;">
            <h2 style="margin-top: 0;"><?php _e('Total de Cliques', 'afiliados-pro'); ?></h2>
            <p style="font-size: 48px; font-weight: bold; color: #283593; margin: 10px 0;">
                <?php echo number_format_i18n($total_clicks); ?>
            </p>
            <p class="description"><?php printf(__('Nos últimos %d dias', 'afiliados-pro'), $days); ?></p>
        </div>

        <?php if (empty($results)): ?>
            <div class="notice notice-info">
                <p><?php _e('Nenhum clique registrado no período selecionado.', 'afiliados-pro'); ?></p>
            </div>
        <?php else: ?>

            <!-- Gráfico de Barras -->
            <div class="card" style="padding: 20px; margin-bottom: 20px;">
                <h3><?php _e('Top 10 Produtos Mais Clicados', 'afiliados-pro'); ?></h3>
                <canvas id="affiliateChart" style="max-height: 400px;"></canvas>
            </div>

            <!-- Tabela Detalhada -->
            <h3><?php _e('Detalhamento por Produto, Página e Origem', 'afiliados-pro'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 30%;"><?php _e('Produto', 'afiliados-pro'); ?></th>
                        <th scope="col" style="width: 30%;"><?php _e('Página de Origem', 'afiliados-pro'); ?></th>
                        <th scope="col" style="width: 20%;"><?php _e('Origem do Clique', 'afiliados-pro'); ?></th>
                        <th scope="col" style="width: 20%;"><?php _e('Total de Cliques', 'afiliados-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row):
                        // Determinar nome do produto
                        $product_display = $row->product_name ?
                            esc_html($row->product_name) :
                            sprintf(__('Produto #%s (não encontrado)', 'afiliados-pro'), $row->product_id);

                        // Determinar ícone e label da origem
                        $source_icon = '';
                        $source_label = '';
                        switch ($row->source) {
                            case 'button':
                                $source_icon = '🎯';
                                $source_label = __('Botão', 'afiliados-pro');
                                break;
                            case 'title':
                                $source_icon = '📝';
                                $source_label = __('Título', 'afiliados-pro');
                                break;
                            case 'image':
                                $source_icon = '🖼️';
                                $source_label = __('Imagem', 'afiliados-pro');
                                break;
                            default:
                                $source_icon = '🔗';
                                $source_label = esc_html(ucfirst($row->source));
                        }

                        // Página de origem
                        $source_page_display = $row->source_page ?
                            '<code>' . esc_html($row->source_page) . '</code>' :
                            '<span style="color: #999;">N/A</span>';
                    ?>
                        <tr>
                            <td><strong><?php echo $product_display; ?></strong></td>
                            <td><?php echo $source_page_display; ?></td>
                            <td>
                                <span class="affiliate-pro-source-tag">
                                    <span style="font-size: 14px; margin-right: 3px;"><?php echo $source_icon; ?></span>
                                    <?php echo $source_label; ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: #283593; font-size: 16px;">
                                    <?php echo number_format_i18n(intval($row->total)); ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3"><strong><?php _e('Total Geral', 'afiliados-pro'); ?></strong></th>
                        <th><strong style="color: #283593; font-size: 18px;">
                            <?php echo number_format_i18n($total_clicks); ?>
                        </strong></th>
                    </tr>
                </tfoot>
            </table>

        <?php endif; ?>

        <!-- Botão Limpar Dados -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h3><?php _e('Gerenciar Dados', 'afiliados-pro'); ?></h3>
            <p class="description">
                <?php _e('Use esta opção para remover todos os registros de cliques do banco de dados. Esta ação não pode ser desfeita.', 'afiliados-pro'); ?>
            </p>
            <form method="post" action="" style="margin-top: 15px;">
                <?php wp_nonce_field('affiliate_clear_stats', 'affiliate_clear_stats_nonce'); ?>
                <input type="submit"
                       name="affiliate_clear_stats"
                       class="button button-secondary"
                       value="🧹 <?php esc_attr_e('Limpar Dados de Cliques', 'afiliados-pro'); ?>"
                       aria-label="<?php esc_attr_e('Excluir permanentemente todos os registros de cliques', 'afiliados-pro'); ?>"
                       title="<?php esc_attr_e('Remover todos os dados de cliques do banco de dados', 'afiliados-pro'); ?>"
                       onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja excluir todos os registros de cliques? Esta ação não pode ser desfeita.', 'afiliados-pro'); ?>');">
            </form>
        </div>

    <?php endif; ?>
</div>

<?php if ($table_exists && !empty($chart_labels)): ?>
<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('affiliateChart');

    if (ctx) {
        const data = {
            labels: <?php echo wp_json_encode($chart_labels); ?>,
            datasets: [{
                label: '<?php echo esc_js(__('Cliques', 'afiliados-pro')); ?>',
                data: <?php echo wp_json_encode($chart_data); ?>,
                backgroundColor: '#283593',
                borderColor: '#1a237e',
                borderWidth: 1
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.dashicons {
    vertical-align: middle;
    margin-right: 5px;
}

code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    color: #0073aa;
}
</style>
