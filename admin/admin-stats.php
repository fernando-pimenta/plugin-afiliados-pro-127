<?php
/**
 * Afiliados Pro - Estatísticas de Cliques
 *
 * @package Affiliate_Pro
 * @version 1.4.8
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'affiliate_clicks';

// Verificar se a tabela existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;

// Período selecionado
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;

// Consulta agregada
$results = array();
$total_clicks = 0;

if ($table_exists) {
    $query = $wpdb->prepare("
        SELECT product_id, source, COUNT(*) as total
        FROM $table
        WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
        GROUP BY product_id, source
        ORDER BY total DESC
        LIMIT 50
    ", $days);

    $results = $wpdb->get_results($query);

    // Calcular total de cliques
    foreach ($results as $row) {
        $total_clicks += intval($row->total);
    }
}

// Preparar dados para o gráfico (agrupar por produto)
$chart_data = array();
if ($results) {
    foreach ($results as $row) {
        if (!isset($chart_data[$row->product_id])) {
            $chart_data[$row->product_id] = 0;
        }
        $chart_data[$row->product_id] += intval($row->total);
    }
}

// Limitar a 10 produtos para o gráfico
$chart_data = array_slice($chart_data, 0, 10, true);
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
            <select name="days" id="days" onchange="this.form.submit()" style="margin-left: 10px;">
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
            <h3><?php _e('Detalhamento por Produto e Origem', 'afiliados-pro'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 40%;"><?php _e('Produto ID', 'afiliados-pro'); ?></th>
                        <th scope="col" style="width: 30%;"><?php _e('Origem', 'afiliados-pro'); ?></th>
                        <th scope="col" style="width: 30%;"><?php _e('Total de Cliques', 'afiliados-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><strong><?php echo esc_html($row->product_id); ?></strong></td>
                            <td>
                                <span class="dashicons dashicons-<?php
                                    switch ($row->source) {
                                        case 'button':
                                            echo 'button';
                                            break;
                                        case 'title':
                                            echo 'heading';
                                            break;
                                        case 'image':
                                            echo 'format-image';
                                            break;
                                        default:
                                            echo 'admin-links';
                                    }
                                ?>"></span>
                                <?php echo esc_html(ucfirst($row->source)); ?>
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
                        <th colspan="2"><strong><?php _e('Total Geral', 'afiliados-pro'); ?></strong></th>
                        <th><strong style="color: #283593; font-size: 18px;">
                            <?php echo number_format_i18n($total_clicks); ?>
                        </strong></th>
                    </tr>
                </tfoot>
            </table>

        <?php endif; ?>

    <?php endif; ?>
</div>

<?php if ($table_exists && !empty($chart_data)): ?>
<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('affiliateChart');

    if (ctx) {
        const data = {
            labels: <?php echo wp_json_encode(array_keys($chart_data)); ?>,
            datasets: [{
                label: '<?php echo esc_js(__('Cliques', 'afiliados-pro')); ?>',
                data: <?php echo wp_json_encode(array_values($chart_data)); ?>,
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
</style>
