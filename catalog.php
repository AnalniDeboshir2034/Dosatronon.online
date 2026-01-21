<?php
// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

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


$selected_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Подготовка SQL запроса
if ($selected_filter != 'all' && !empty($selected_filter)) {
    $sql = "SELECT * FROM medicator WHERE filtr LIKE ? ORDER BY name ASC";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $search_param = "%" . $selected_filter . "%";
        $stmt->bind_param("s", $search_param);
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
        
        $products = [];
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
        $products = [];
    }
} else {
    $sql = "SELECT * FROM medicator ORDER BY name ASC";
    $result = $mysqli->query($sql);
    
    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['img_found'] = findFile($row['img'] ?? '');
            $products[] = $row;
        }
        $result->free();
    }
}

echo "<!-- Получено товаров: " . count($products) . " -->";

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
$favicon=getContent('favicon');
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $meta_desc; ?>" type="image/x-icon">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="cs/style.css">
    <link rel="stylesheet" href="cs/catalog.css">
    <script src="j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
    <script src="j/catalog.js"></script>

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
                        $count_sql = "SELECT COUNT(*) as count FROM medicator";
                        if ($filter_value != 'all') {
                            $count_sql .= " WHERE filtr LIKE '%$filter_value%'";
                        }
                        $count_result = $mysqli->query($count_sql);
                        $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
                    ?>
                        <a href="catalog.php?filter=<?php echo $filter_value; ?>" 
                           class="filter-item <?php echo $selected_filter == $filter_value ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($filter_label); ?>
                            <span class="filter-count"><?php echo $count; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            
            <div class="catalog-content">
                <div class="catalog-info">
                    <div class="catalog-count">
                        Найдено товаров: <strong id="productCount"><?php echo count($products); ?></strong>
                    </div>
                    
                    <?php if ($selected_filter != 'all'): ?>
                        <div class="current-filter">
                            Активный фильтр: <?php echo htmlspecialchars($final_filters[$selected_filter] ?? $selected_filter); ?>
                        </div>
                        <a href="catalog.php?filter=all" class="clear-filter">
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
                                    <img src="<?php echo htmlspecialchars($product['img_found']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                <?php elseif (!empty($product['img']) && $product['img'] != '-'): ?>
                                    <img src="images/products/<?php echo htmlspecialchars($product['img']); ?>" 
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
                                    <a href="product.php?id=<?php echo $product['id']; ?>" style="color: inherit; text-decoration: none;">
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
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>Товары не найдены. <?php echo $selected_filter != 'all' ? 'Попробуйте другой фильтр.' : 'Добавьте товары в базу данных.'; ?></p>
                            <?php if ($selected_filter != 'all'): ?>
                                <a href="catalog.php?filter=all" class="btn btn-primary" style="margin-top: 20px;">
                                    Показать все товары
                                </a>
                            <?php else: ?>
                                <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">
                                    Вернуться на главную
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

        <?php include 'includes/footer.php'; ?>
        <script>
             document.getElementById('searchBtn').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            let visibleCount = 0;
            
            productCards.forEach(card => {
                const title = card.querySelector('.product-card__title').textContent.toLowerCase();
                const desc = card.querySelector('.product-card__desc').textContent.toLowerCase();
                const dataName = card.getAttribute('data-product-name').toLowerCase();
                const dataDosing = card.getAttribute('data-product-dosing').toLowerCase();
                const dataPerformance = card.getAttribute('data-product-performance').toLowerCase();
                const dataFilter = card.getAttribute('data-product-filter').toLowerCase();
                
                if (title.includes(searchTerm) || desc.includes(searchTerm) || 
                    dataName.includes(searchTerm) || dataDosing.includes(searchTerm) || 
                    dataPerformance.includes(searchTerm) || dataFilter.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.getElementById('productCount').textContent = visibleCount;
        });
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchBtn').click();
            }
        });

        
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
            
            document.getElementById('searchInput').addEventListener('dblclick', function() {
                this.value = '';
                document.querySelectorAll('.product-card').forEach(card => {
                    card.style.display = 'block';
                });
                document.getElementById('productCount').textContent = document.querySelectorAll('.product-card').length;
            });
            
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