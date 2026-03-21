<?php
session_start();
require_once 'includes/config.php';

if (!$mysqli || $mysqli->connect_error) {
    die("Ошибка соединения с БД");
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

function exportViewsToExcel($date_from, $date_to, $mysqli) {
    $daily_query = "SELECT 
                        view_date,
                        SUM(view_count) as total_views,
                        COUNT(DISTINCT product_id) as unique_products
                    FROM product_views
                    WHERE view_date BETWEEN ? AND ?
                    GROUP BY view_date
                    ORDER BY view_date DESC";
    
    $stmt = $mysqli->prepare($daily_query);
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $stmt->store_result();
    
    $daily_stats = array();
    $view_date = null;
    $total_views = null;
    $unique_products = null;
    $stmt->bind_result($view_date, $total_views, $unique_products);
    while ($stmt->fetch()) {
        $daily_stats[] = array(
            'view_date' => $view_date,
            'total_views' => $total_views,
            'unique_products' => $unique_products
        );
    }
    $stmt->close();
    
    $top_query = "SELECT 
                    product_name,
                    product_id,
                    SUM(view_count) as total_views
                FROM product_views
                WHERE view_date BETWEEN ? AND ?
                GROUP BY product_id, product_name
                ORDER BY total_views DESC";
    
    $stmt = $mysqli->prepare($top_query);
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $stmt->store_result();
    
    $top_products = array();
    $product_name = null;
    $product_id = null;
    $prod_total_views = null;
    $stmt->bind_result($product_name, $product_id, $prod_total_views);
    while ($stmt->fetch()) {
        $top_products[] = array(
            'product_name' => $product_name,
            'product_id' => $product_id,
            'total_views' => $prod_total_views
        );
    }
    $stmt->close();
    
    $detail_query = "SELECT 
                        view_date,
                        product_name,
                        view_count
                    FROM product_views
                    WHERE view_date BETWEEN ? AND ?
                    ORDER BY view_date DESC, view_count DESC";
    
    $stmt = $mysqli->prepare($detail_query);
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $stmt->store_result();
    
    $details = array();
    $det_view_date = null;
    $det_product_name = null;
    $det_view_count = null;
    $stmt->bind_result($det_view_date, $det_product_name, $det_view_count);
    while ($stmt->fetch()) {
        $details[] = array(
            'view_date' => $det_view_date,
            'product_name' => $det_product_name,
            'view_count' => $det_view_count
        );
    }
    $stmt->close();
    
    $total_views_sum = 0;
    foreach ($daily_stats as $stat) {
        $total_views_sum += $stat['total_views'];
    }
    
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="views_report_' . $date_from . '_to_' . $date_to . '.xls"');
    
    echo '<html>';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    
    echo '<h2>ОТЧЕТ ПО ПРОСМОТРАМ ТОВАРОВ</h2>';
    echo '<p>Период: ' . $date_from . ' - ' . $date_to . '</p>';
    echo '<p>Дата формирования: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<hr>';
    
    echo '<h3>Статистика по дням</h3>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Дата</th><th>Просмотров</th><th>Уникальных товаров</th></tr>';
    
    foreach ($daily_stats as $stat) {
        echo '<tr>';
        echo '<td>' . $stat['view_date'] . '</td>';
        echo '<td align="right">' . number_format($stat['total_views']) . '</td>';
        echo '<td align="right">' . number_format($stat['unique_products']) . '</td>';
        echo '</tr>';
    }
    echo '<tr><td><strong>Итого:</strong></td><td align="right"><strong>' . number_format($total_views_sum) . '</strong></td><td align="right"></td></tr>';
    echo '</table>';
    
    echo '<h3>Топ товаров</h3>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>#</th><th>Товар</th><th>ID</th><th>Просмотров</th></tr>';
    
    $rank = 1;
    foreach ($top_products as $product) {
        echo '<tr>';
        echo '<td>' . $rank++ . '</td>';
        echo '<td>' . $product['product_name'] . '</td>';
        echo '<td>#' . $product['product_id'] . '</td>';
        echo '<td align="right">' . number_format($product['total_views']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<h3>Детальный отчет</h3>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Дата</th><th>Товар</th><th>Просмотров</th></tr>';
    
    foreach ($details as $detail) {
        echo '<tr>';
        echo '<td>' . $detail['view_date'] . '</td>';
        echo '<td>' . $detail['product_name'] . '</td>';
        echo '<td align="right">' . number_format($detail['view_count']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</body></html>';
    exit;
}

if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
    exportViewsToExcel($date_from, $date_to, $mysqli);
    exit;
}

$dates_query = "SELECT MIN(view_date) as min_date, MAX(view_date) as max_date FROM product_views";
$dates_result = $mysqli->query($dates_query);
$date_range = $dates_result->fetch_assoc();
$min_date_db = $date_range['min_date'] ?? date('Y-m-d');
$max_date_db = $date_range['max_date'] ?? date('Y-m-d');

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : $min_date_db;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : $max_date_db;

$daily_query = "SELECT 
                    view_date,
                    SUM(view_count) as total_views,
                    COUNT(DISTINCT product_id) as unique_products
                FROM product_views
                WHERE view_date BETWEEN ? AND ?
                GROUP BY view_date
                ORDER BY view_date DESC";

$stmt = $mysqli->prepare($daily_query);
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$stmt->store_result();

$daily_stats = array();
$view_date = null;
$total_views = null;
$unique_products = null;
$stmt->bind_result($view_date, $total_views, $unique_products);
while ($stmt->fetch()) {
    $daily_stats[] = array(
        'view_date' => $view_date,
        'total_views' => $total_views,
        'unique_products' => $unique_products
    );
}
$stmt->close();

$total_views_sum = 0;
foreach ($daily_stats as $stat) {
    $total_views_sum += $stat['total_views'];
}

$top_query = "SELECT 
                product_name,
                product_id,
                SUM(view_count) as total_views
            FROM product_views
            WHERE view_date BETWEEN ? AND ?
            GROUP BY product_id, product_name
            ORDER BY total_views DESC
            LIMIT 20";

$stmt = $mysqli->prepare($top_query);
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$stmt->store_result();

$top_products = array();
$product_name = null;
$product_id = null;
$prod_total_views = null;
$stmt->bind_result($product_name, $product_id, $prod_total_views);
while ($stmt->fetch()) {
    $top_products[] = array(
        'product_name' => $product_name,
        'product_id' => $product_id,
        'total_views' => $prod_total_views
    );
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по просмотрам</title>
    <link rel="stylesheet" href="cs/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-container { padding: 20px; }
        .filters {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: white;
        }
        .filter-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-form div {
            flex: 1;
            min-width: 200px;
        }
        .filter-form label {
            display: block;
            margin-bottom: 5px;
        }
        .filter-form input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
        }
        .filter-form button {
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary { background: white; color: #667eea; }
        .btn-success { background: #28a745; color: white; }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .stat-card h3 {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
        .stat-card .stat-number {
            font-size: 32px;
            font-weight: 600;
        }
        .reports-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .report-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .btn-group button {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-excel { background: #4CAF50; color: white; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
        }
        .chart-container {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border);
            margin-top: 20px;
        }
        canvas { width: 100% !important; height: 400px !important; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>📊 Админ панель</h1>
            </div>
            <nav class="nav-links">
                <a href="adminpanel.php">📋 Медикаторы</a>
                <a href="report_views.php" class="active">👁️ Просмотры</a>
                <a href="add.php">➕ Добавить запись</a>
                <a href="logout.php" style="color: #f85149;">🚪 Выйти</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="report-container">
                <div class="filters">
                    <h3>📅 Фильтр по дате</h3>
                    <form method="GET" class="filter-form">
                        <div>
                            <label>Дата с:</label>
                            <input type="date" name="date_from" value="<?= $date_from ?>">
                        </div>
                        <div>
                            <label>Дата по:</label>
                            <input type="date" name="date_to" value="<?= $date_to ?>">
                        </div>
                        <button type="submit" class="btn-primary">🔍 Применить</button>
                        <button type="button" onclick="window.location.href='report_views.php'" class="btn-success">↺ Сброс</button>
                        <button type="button" onclick="exportToExcel()" class="btn-success">📥 Excel</button>
                    </form>
                </div>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3>👁️ Всего просмотров</h3>
                        <div class="stat-number"><?= number_format($total_views_sum) ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>📦 Уникальных товаров</h3>
                        <div class="stat-number"><?= count($top_products) ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>📅 Дней в отчете</h3>
                        <div class="stat-number"><?= count($daily_stats) ?></div>
                    </div>
                </div>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <div class="report-header">
                            <h2>📈 Просмотры по дням</h2>
                            <div class="btn-group">
                                <button onclick="exportTableToExcel('dailyTable', 'daily_views')" class="btn-excel">Excel</button>
                            </div>
                        </div>
                        <table id="dailyTable">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th class="text-right">Просмотров</th>
                                    <th class="text-right">Уникальных</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daily_stats as $stat): ?>
                                <tr>
                                    <td><?= date('d.m.Y', strtotime($stat['view_date'])) ?></td>
                                    <td class="text-right"><?= number_format($stat['total_views']) ?></td>
                                    <td class="text-right"><?= number_format($stat['unique_products']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="report-card">
                        <div class="report-header">
                            <h2>🏆 Топ товаров</h2>
                            <div class="btn-group">
                                <button onclick="exportTableToExcel('topTable', 'top_products')" class="btn-excel">Excel</button>
                            </div>
                        </div>
                        <table id="topTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Товар</th>
                                    <th class="text-right">Просмотров</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($top_products as $product): 
                                ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($product['product_name']) ?> (ID: <?= $product['product_id'] ?>)</td>
                                    <td class="text-right"><?= number_format($product['total_views']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>📊 График просмотров по дням</h3>
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        const labels = <?= json_encode(array_map(function($d) {
            return date('d.m', strtotime($d['view_date']));
        }, $daily_stats)) ?>;
        
        const views = <?= json_encode(array_column($daily_stats, 'total_views')) ?>;
        
        new Chart(document.getElementById('viewsChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Просмотры',
                    data: views,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        function exportToExcel() {
            const date_from = document.querySelector('input[name="date_from"]').value;
            const date_to = document.querySelector('input[name="date_to"]').value;
            window.location.href = 'report_views.php?export=excel&date_from=' + date_from + '&date_to=' + date_to;
        }
        
        function exportTableToExcel(tableId, filename) {
            const table = document.getElementById(tableId);
            const wb = XLSX.utils.table_to_book(table, {sheet: "Отчет"});
            XLSX.writeFile(wb, filename + '_' + new Date().toISOString().slice(0,10) + '.xlsx');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
</body>
</html>