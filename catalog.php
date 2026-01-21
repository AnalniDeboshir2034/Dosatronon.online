<?php
// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/forslug.php';
include 'includes/content_parser.php';

// Проверяем соединение с базой данных
$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

// Подключение к базе данных
$mysqli = @new mysqli($host, $user, $pass, $db_name);

if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

// Функция поиска файла
function findFile($dbPath) {
    if (empty($dbPath) || $dbPath == '-' || $dbPath == 'NULL') {
        return null;
    }

    $fileName = basename($dbPath);
    
    $searchFolders = [
        '',
        'images/',
        'img/',
        'products/',
        'uploads/',
        'images/products/',
        'img/products/',
        'assets/images/',
        'media/products/',
    ];
    
    foreach ($searchFolders as $folder) {
        $fullPath = $folder . $fileName;
        $fullPath = str_replace('\\', '/', $fullPath);
        $fullPath = preg_replace('#/+#', '/', $fullPath);
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return $fullPath;
        }
    }
    
    return null;
}

// Получаем параметры фильтра и поиска
$selected_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Подготовка SQL запроса с учетом поиска
$products = [];
$search_mode = !empty($search_query);

if ($search_mode) {
    // Есть поисковый запрос
    if ($selected_filter != 'all' && !empty($selected_filter)) {
        // Поиск + фильтр
        $sql = "SELECT *,slug FROM medicator WHERE filtr LIKE ? AN LIKE ? AND 
                (name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?) 
                ORDER BY name ASC  ";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $filter_param = "%" . $selected_filter . "%";
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("sssss", $filter_param, $search_param, $search_param, $search_param, $search_param);
            $stmt->execute();
            $stmt->store_result();
            
            // Получаем колонки
            $meta = $stmt->result_metadata();
            $fields = [];
            $fieldReferences = [];
            
            while ($field = $meta->fetch_field()) {
                $fields[$field->name] = null;
                $fieldReferences[] = &$fields[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $fieldReferences);
            
            while ($stmt->fetch()) {
                $row = [];
                foreach ($fields as $key => $value) {
                    $row[$key] = $value;
                }
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            
            $stmt->close();
        } else {
            echo "<!-- Ошибка подготовки запроса: " . $mysqli->error . " -->";
        }
    } else {
        // Только поиск (без фильтра)
        $sql = "SELECT * FROM medicator WHERE 
                (name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?) 
                ORDER BY name ASC";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
            $stmt->execute();
            $stmt->store_result();
            
            // Получаем колонки
            $meta = $stmt->result_metadata();
            $fields = [];
            $fieldReferences = [];
            
            while ($field = $meta->fetch_field()) {
                $fields[$field->name] = null;
                $fieldReferences[] = &$fields[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $fieldReferences);
            
            while ($stmt->fetch()) {
                $row = [];
                foreach ($fields as $key => $value) {
                    $row[$key] = $value;
                }
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            
            $stmt->close();
        } else {
            echo "<!-- Ошибка подготовки запроса: " . $mysqli->error . " -->";
        }
    }
} else {
    // НЕТ поискового запроса - используем старую логику фильтрации
    if ($selected_filter != 'all' && !empty($selected_filter)) {
        // Только фильтр (без поиска)
        $sql = "SELECT * FROM medicator WHERE filtr LIKE ? ORDER BY name ASC";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $filter_param = "%" . $selected_filter . "%";
            $stmt->bind_param("s", $filter_param);
            $stmt->execute();
            $stmt->store_result();
            
            // Получаем колонки
            $meta = $stmt->result_metadata();
            $fields = [];
            $fieldReferences = [];
            
            while ($field = $meta->fetch_field()) {
                $fields[$field->name] = null;
                $fieldReferences[] = &$fields[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $fieldReferences);
            
            while ($stmt->fetch()) {
                $row = [];
                foreach ($fields as $key => $value) {
                    $row[$key] = $value;
                }
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            
            $stmt->close();
        } else {
            echo "<!-- Ошибка подготовки запроса: " . $mysqli->error . " -->";
        }
    } else {
        // Нет ни поиска, ни фильтра - все товары
        $sql = "SELECT * FROM medicator ORDER BY name ASC";
        $result = $mysqli->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            $result->free();
        } else {
            echo "<!-- Ошибка запроса: " . $mysqli->error . " -->";
        }
    }
}

echo "<!-- Получено товаров: " . count($products) . " -->";
echo "<!-- Поисковый запрос: " . htmlspecialchars($search_query) . " -->";
echo "<!-- Фильтр: " . htmlspecialchars($selected_filter) . " -->";

$available_filters = [
    'DIA' => 'DIA Серия',
    'D07' => 'D07 Серия', 
    'D25' => 'D25 Серия',
    'D3' => 'D3 Серия',
    'D45' => 'D45 Серия',
    'D8' => 'D8 Серия',
    'D9' => 'D9 Серия',
    'D20' => 'D20 Серия',
    'D30' => 'D30 Серия',
    'Drugoe'=>'Доп. оборудование'
];

$final_filters = array_merge(['all' => 'Все товары'], $available_filters);

function getContent($section) {
    require_once 'includes/content_parser.php';
    return getContentSection($section, '');
}

$meta_desc = getContent('meta_description');
$meta_keys = getContent('meta_keywords');
$page_title = getContent('header_title');
$favicon = getContent('favicon');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/cs/style.css">
    <link rel="stylesheet" href="/cs/catalog.css">
    <script src="/j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
    <script src="/j/catalog.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main">
        <section class="catalog-header">
            <div class="container">
                <h1>Каталог медикаторов</h1>
                <p>Полный ассортимент оборудования для дозирования и систем орошения</p>
            </div>
        </section>

        <div class="catalog-container">
            <aside class="catalog-sidebar">
                <h2 class="sidebar-title">Фильтры по типу</h2>
                
                <div class="filter-list">
                    <?php foreach ($final_filters as $filter_value => $filter_label): 
                        // Формируем URL для фильтра (красивый URL)
                        if ($filter_value == 'all') {
                            $filter_url = "/catalog";
                        } else {
                            $filter_url = "/catalog/" . $filter_value;
                        }
                        
                        // Добавляем поисковый запрос если есть
                        if (!empty($search_query)) {
                            $filter_url .= "?search=" . urlencode($search_query);
                        }
                        
                        // Проверяем активен ли фильтр
                        $is_active = ($selected_filter == $filter_value);
                        
                        // Считаем количество товаров
                        $count_sql = "SELECT COUNT(*) as count FROM medicator";
                        $params = [];
                        $types = "";
                        
                        if ($filter_value != 'all') {
                            $count_sql .= " WHERE filtr LIKE ?";
                            $params[] = "%" . $filter_value . "%";
                            $types .= "s";
                            
                            if ($search_mode) {
                                $count_sql .= " AND (name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?)";
                                $params = array_merge($params, array_fill(0, 4, "%" . $search_query . "%"));
                                $types .= str_repeat("s", 4);
                            }
                        } else if ($search_mode) {
                            $count_sql .= " WHERE (name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?)";
                            $params = array_fill(0, 4, "%" . $search_query . "%");
                            $types = str_repeat("s", 4);
                        }
                        
                        $count = 0;
                        $stmt = $mysqli->prepare($count_sql);
                        if ($stmt) {
                            if (!empty($params)) {
                                $stmt->bind_param($types, ...$params);
                            }
                            $stmt->execute();
                            $stmt->bind_result($count);
                            $stmt->fetch();
                            $stmt->close();
                        }
                    ?>
                        <a href="<?php echo $filter_url; ?>" 
                           class="filter-item <?php echo $is_active ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($filter_label); ?>
                            <span class="filter-count"><?php echo $count; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            
            <div class="catalog-content">
                <div class="catalog-info">
                    <?php if ($search_mode): ?>
                        <div class="search-results-info">
                            <strong>Результаты поиска:</strong> "<?php echo htmlspecialchars($search_query); ?>"
                            <a href="/catalog" class="clear-search">
                                ✕ Очистить поиск
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="catalog-count">
                        Найдено товаров: <strong id="productCount"><?php echo count($products); ?></strong>
                    </div>
                    
                    <?php if ($selected_filter != 'all'): ?>
                        <div class="current-filter">
                            Активный фильтр: <?php echo htmlspecialchars($final_filters[$selected_filter] ?? $selected_filter); ?>
                        </div>
                        <a href="/catalog<?php echo $search_mode ? '?search=' . urlencode($search_query) : ''; ?>" class="clear-filter">
                            Сбросить фильтр
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="catalog-grid" id="productsGrid">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
        <div class="product-card" 
             data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
             data-product-dosing="<?php echo htmlspecialchars($product['d_dosing']); ?>"
             data-product-performance="<?php echo htmlspecialchars($product['performance']); ?>"
             data-product-filter="<?php echo htmlspecialchars($product['filtr'] ?? ''); ?>">
            <div class="product-card__image">
                <?php if ($product['img_found']): ?>
                    <!-- ВАЖНО: добавлен / в начале пути -->
                    <img src="/<?php echo htmlspecialchars($product['img_found']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         loading="lazy">
                <?php elseif (!empty($product['img']) && $product['img'] != '-'): ?>
                    <!-- ВАЖНО: добавлен / в начале пути -->
                    <img src="/images/products/<?php echo htmlspecialchars($product['img']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="image-placeholder">
                        <span class="placeholder-icon">🏭</span>
                        <p>Нет изображения</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="product-card__content">
                <?php if (!empty($product['filtr']) && $product['filtr'] != '-'): ?>
                    <div class="product-card__filter">
                        <?php 
                        $filters = explode(',', $product['filtr']);
                        echo htmlspecialchars(trim($filters[0]));
                        ?>
                    </div>
                <?php endif; ?>
                
                <h3 class="product-card__title">
                    <!-- ВАЖНО: ссылка с / -->
                    <a href="<?php echo getProductUrl($product); ?>" style="color: inherit; text-decoration: none;">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                </h3>
                <p class="product-card__desc">
                    <?php if (!empty($product['d_dosing'])): ?>
                        <strong>Дозировка:</strong> <?php echo htmlspecialchars($product['d_dosing']); ?><br>
                    <?php endif; ?>
                    <?php if (!empty($product['performance'])): ?>
                        <strong>Производительность:</strong> <?php echo htmlspecialchars($product['performance']); ?><br>
                    <?php endif; ?>
                    <?php if (!empty($product['filtr']) && $product['filtr'] != '-'): ?>
                        <strong>Серия:</strong> <?php echo htmlspecialchars($product['filtr']); ?>
                    <?php endif; ?>
                </p>
                <div class="product-card__actions">
                    <button class="btn btn-secondary" 
                            data-product-id="<?php echo $product['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                        В сравнение
                    </button>
                    <!-- ВАЖНО: ссылка с / -->
                     <a href="<?php echo getProductUrl($product); ?>" class="btn btn-primary">Подробнее</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-products">
            <p>
                <?php if ($search_mode): ?>
                    По запросу "<?php echo htmlspecialchars($search_query); ?>" ничего не найдено.
                <?php elseif ($selected_filter != 'all'): ?>
                    Товары не найдены. Попробуйте другой фильтр.
                <?php else: ?>
                    Товары не найдены. Добавьте товары в базу данных.
                <?php endif; ?>
            </p>
            <!-- ВАЖНО: ссылка с / -->
            <a href="/catalog" class="btn btn-primary" style="margin-top: 20px;">
                Показать все товары
            </a>
        </div>
    <?php endif; ?>
</div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        
        function updateCompareButtons() {
            const compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
            
            document.querySelectorAll('.btn-secondary[data-product-id]').forEach(button => {
                const productId = button.getAttribute('data-product-id');
                const exists = compareItems.some(item => item.id == productId);
                
                if (exists) {
                    button.textContent = '✓ В сравнении';
                    button.classList.add('btn-success');
                    button.classList.remove('btn-secondary');
                } else {
                    button.textContent = 'В сравнение';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                }
            });
        }

        document.querySelectorAll('.btn-secondary[data-product-id]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                let compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
                
                const exists = compareItems.some(item => item.id == productId);
                if (!exists) {
                    compareItems.push({
                        id: productId,
                        name: productName,
                        date: new Date().toISOString()
                    });
                    localStorage.setItem('compareItems', JSON.stringify(compareItems));
                    
                    this.textContent = '✓ В сравнении';
                    this.classList.add('btn-success');
                    this.classList.remove('btn-secondary');
                    
                    const ids = compareItems.map(item => item.id).join(',');
                    showNotification(`Товар добавлен! <a href="compare.php?ids=${ids}" style="color: white; text-decoration: underline;">Перейти к сравнению</a>`);
                } else {
                    compareItems = compareItems.filter(item => item.id != productId);
                    localStorage.setItem('compareItems', JSON.stringify(compareItems));
                    
                    this.textContent = 'В сравнение';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    
                    showNotification(`Товар "${productName}" удален из сравнения!`);
                }
            });
        });
        
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateCompareButtons();
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
            
            window.addEventListener('storage', function(e) {
                if (e.key === 'compareItems') {
                    updateCompareButtons();
                }
            });
        });
        
        (function(w,d,u){
            var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
            var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
    </script>
</body>
</html>

<?php $mysqli->close(); ?>