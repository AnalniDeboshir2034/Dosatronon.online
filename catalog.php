<?php
// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Начало выполнения PHP -->";

// Проверяем соединение с базой данных
$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

echo "<!-- Подключаемся к базе данных -->";

// Подключение к базе данных
$mysqli = @new mysqli($host, $user, $pass, $db_name);

if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
echo "<!-- Подключение успешно -->";

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

echo "<!-- Получаем параметр фильтра -->";
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
    'D30' => 'D30 Серия'
];

$final_filters = array_merge(['all' => 'Все товары'], $available_filters);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог медикаторов | 7 company</title>
    <link rel="stylesheet" href="cs/style.css">
    <script src="j/script.js" defer></script>
    <style>
        .catalog-header {
            background: linear-gradient(135deg, hsl(220 40% 5%), hsl(200 70% 10%));
            padding: 80px 0;
            text-align: center;
            border-bottom: 1px solid hsl(200 30% 18%);
        }
        
        .catalog-header h1 {
            font-size: 3rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .catalog-header p {
            color: hsl(200 20% 70%);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .catalog-container {
            display: flex;
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .catalog-sidebar {
            flex: 0 0 280px;
            background: hsl(220 35% 8%);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid hsl(200 30% 18%);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .sidebar-title {
            color: white;
            font-size: 1.4rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid hsl(195 100% 50%);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-title::before {
            content: '';
            font-size: 1.2rem;
        }
        
        .filter-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-item {
            display: block;
            width: 100%;
            padding: 12px 15px;
            background: hsl(200 30% 12%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 8px;
            color: hsl(200 20% 70%);
            text-align: left;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .filter-item:hover {
            background: hsl(200 30% 15%);
            border-color: hsl(195 100% 50%);
            color: hsl(195 100% 50%);
            transform: translateX(5px);
        }
        
        .filter-item.active {
            background: hsl(195 100% 50%);
            border-color: hsl(195 100% 50%);
            color: hsl(220 40% 5%);
            font-weight: 600;
            transform: translateX(5px);
        }
        
        .filter-count {
            float: right;
            background: hsl(200 30% 20%);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            color: hsl(200 20% 70%);
        }
        
        .filter-item.active .filter-count {
            background: hsl(220 40% 5%);
            color: white;
        }
        
        .sidebar-search {
            margin-top: 20px;
        }
        
        .search-box {
            display: flex;
            margin-bottom: 15px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px 15px;
            background: hsl(200 30% 12%);
            border: 1px solid hsl(200 30% 18%);
            border-radius: 8px 0 0 8px;
            color: white;
            font-size: 0.95rem;
        }
        
        .search-box button {
            padding: 12px 20px;
            background: hsl(195 100% 50%);
            border: none;
            border-radius: 0 8px 8px 0;
            color: hsl(220 40% 5%);
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .catalog-content {
            flex: 1;
        }
        
        .catalog-info {
            background: hsl(220 35% 8%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid hsl(200 30% 18%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .catalog-count {
            color: hsl(200 20% 70%);
            font-size: 1rem;
        }
        
        .catalog-count strong {
            color: white;
            font-size: 1.2rem;
        }
        
        .current-filter {
            background: hsl(200 30% 15%);
            padding: 8px 15px;
            border-radius: 20px;
            color: hsl(195 100% 50%);
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .current-filter::before {
            content: '';
        }
        
        .clear-filter {
            background: transparent;
            border: 1px solid hsl(200 30% 18%);
            color: hsl(200 20% 70%);
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .clear-filter:hover {
            border-color: hsl(195 100% 50%);
            color: hsl(195 100% 50%);
        }
        
        .clear-filter::before {
            content: '🗑️';
        }
        
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px;
            color: hsl(200 20% 60%);
            font-size: 1.2rem;
            background: hsl(220 35% 8%);
            border-radius: 12px;
            border: 1px solid hsl(200 30% 18%);
        }
        
        .product-card {
            background: hsl(220 35% 8%);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid hsl(200 30% 18%);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: hsl(195 100% 50%);
        }
        
        .product-card__image {
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: hsl(200 30% 12%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-card__image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            padding: 10px;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-card__image img {
            transform: scale(1.05);
        }
        
        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: hsl(200 20% 60%);
        }
        
        .placeholder-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .product-card__content {
            padding: 20px;
        }
        
        .product-card__title {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .product-card__title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .product-card__title a:hover {
            color: hsl(195 100% 50%);
        }
        
        .product-card__desc {
            color: hsl(200 20% 70%);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .product-card__filter {
            display: inline-block;
            padding: 4px 10px;
            background: hsl(200 30% 15%);
            border-radius: 4px;
            color: hsl(195 100% 50%);
            font-size: 0.8rem;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .product-card__actions {
            display: flex;
            gap: 10px;
        }
        
        .product-card__actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid hsl(200 30% 18%);
            color: hsl(200 20% 70%);
        }
        
        .btn-secondary:hover {
            border-color: hsl(195 100% 50%);
            color: hsl(195 100% 50%);
        }
        
        .btn-primary {
            background: hsl(195 100% 50%);
            color: hsl(220 40% 5%);
        }
        
        .btn-primary:hover {
            background: hsl(195 100% 40%);
        }
        
        .btn-success {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        
        @media (max-width: 992px) {
            .catalog-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .catalog-sidebar {
                flex: none;
                width: 100%;
                position: static;
            }
            
            .catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .catalog-header h1 {
                font-size: 2.2rem;
            }
            
            .catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .catalog-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .catalog-grid {
                grid-template-columns: 1fr;
            }
            
            .product-card__actions {
                flex-direction: column;
            }
            
            .filter-item {
                padding: 10px 15px;
            }
        }
    </style>
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
                
                <div class="sidebar-search">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Поиск по названию...">
                        <button id="searchBtn">🔍</button>
                    </div>
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