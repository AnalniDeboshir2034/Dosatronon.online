<?php
$host = 'localhost';
$user = 'dosatronon_dosatronon';
$pass = 'dosatronon_dosatronon';
$db_name = 'dosatronon_catalog';



$mysqli = new mysqli($host, $user, $pass, $db_name);
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных");
}
$mysqli->set_charset("utf8mb4");

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

$compare_items = [];
$compare_ids = [];

if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    echo '<script>localStorage.removeItem("compareItems"); window.location.href = "compare.php";</script>';
    exit();
}

if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    $compare_ids = [];
    
    if (isset($_GET['ids'])) {
        $ids = explode(',', $_GET['ids']);
        $ids = array_filter(array_map('intval', $ids));
        $compare_ids = array_values(array_filter($ids, function($id) use ($remove_id) {
            return $id != $remove_id;
        }));
    }
    
    if (!empty($compare_ids)) {
        header('Location: compare.php?ids=' . implode(',', $compare_ids));
    } else {
        header('Location: compare.php');
    }
    exit();
}

if (isset($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    $ids = array_filter(array_map('intval', $ids));
    $compare_ids = $ids;
}

if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $sql = "SELECT * FROM medicator WHERE id IN ($placeholders)";
    
    $stmt = $mysqli->prepare($sql);
    $types = str_repeat('i', count($compare_ids));
    $stmt->bind_param($types, ...$compare_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['img_found'] = findFile($row['img'] ?? '');
        $compare_items[] = $row;
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сравнение товаров | 7 company</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .compare-page {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .compare-header {
            padding: 60px 0 30px;
            text-align: center;
        }
        
        .compare-header h1 {
            font-size: 2.5rem;
            color: var(--foreground);
            margin-bottom: 15px;
        }
        
        .compare-header p {
            color: var(--muted-foreground);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        .compare-empty {
            text-align: center;
            padding: 80px 20px;
            background: var(--muted);
            border-radius: 16px;
            border: 1px solid var(--border);
            margin: 40px 0;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--muted-foreground);
        }
        
        .empty-title {
            font-size: 1.8rem;
            color: var(--foreground);
            margin-bottom: 15px;
        }
        
        .empty-text {
            color: var(--muted-foreground);
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .compare-table-container {
            overflow-x: auto;
            margin: 40px 0;
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 0;
        }
        
        .compare-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }
        
        .compare-table th {
            background: var(--muted);
            padding: 20px;
            text-align: left;
            font-weight: 600;
            color: var(--foreground);
            border-bottom: 2px solid var(--border);
            vertical-align: top;
            min-width: 200px;
        }
        
        .compare-table td {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
            min-width: 250px;
            background: var(--card);
        }
        
        .compare-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-column {
            background: var(--muted);
            position: sticky;
            left: 0;
            z-index: 10;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .compare-product-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 400px;
        }
        
        .compare-product-image {
            width: 180px;
            height: 180px;
            margin-bottom: 20px;
            background: var(--background);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 15px;
        }
        
        .compare-product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .compare-product-title {
            font-size: 1.2rem;
            color: var(--foreground);
            margin-bottom: 15px;
            font-weight: 600;
            line-height: 1.4;
        }
        
        .compare-remove-btn {
            margin-top: auto;
            padding: 8px 16px;
            background: transparent;
            border: 2px solid #f44336;
            color: #f44336;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .compare-remove-btn:hover {
            background: #f44336;
            color: white;
        }
        
        .spec-row {
            margin-bottom: 15px;
        }
        
        .spec-label {
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 5px;
            font-size: 0.95rem;
        }
        
        .spec-value {
            color: var(--muted-foreground);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .spec-value.highlight {
            color: var(--primary);
            font-weight: 600;
        }
        
        .compare-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 40px 0;
            flex-wrap: wrap;
        }
        
        .compare-action-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .compare-action-btn.primary {
            background: var(--primary);
            color: var(--primary-foreground);
        }
        
        .compare-action-btn.secondary {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--foreground);
        }
        
        .compare-action-btn.danger {
            background: transparent;
            border: 2px solid #f44336;
            color: #f44336;
        }
        
        .compare-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 1100px) {
            .compare-table-container {
                margin: 20px -20px;
                border-radius: 0;
                border-left: none;
                border-right: none;
            }
            
            .compare-header {
                padding: 40px 0 20px;
            }
            
            .compare-header h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .compare-product-image {
                width: 140px;
                height: 140px;
            }
            
            .compare-product-card {
                min-height: 350px;
            }
            
            .compare-actions {
                flex-direction: column;
            }
            
            .compare-action-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .compare-page {
                padding: 0 10px;
            }
            
            .compare-product-image {
                width: 120px;
                height: 120px;
            }
            
            .compare-table th,
            .compare-table td {
                padding: 15px 10px;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="container">
            <div class="header__inner">
                <a href="index.php" class="logo">
                    <div class="logo__img">
                        <img src="logo.jpg" alt="7company" width="40" height="40">
                    </div>
                    <span class="logo__text">7 company</span>
                </a>

                <nav class="nav">
                    <ul class="nav__list">
                        <li><a href="index.php" class="nav__link">Главная</a></li>
                        <li><a href="catalog.php" class="nav__link">Каталог</a></li>
                        <li><a href="contacts.php" class="nav__link">Контакты</a></li>
                        <li><a href="compare.php" class="nav__link nav__link--active">Сравнение</a></li>
                        <li><a href="contacts.php" class="btn btn-primary">Заказать</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="compare-page">
            <div class="compare-header">
                <h1>Сравнение товаров</h1>
                <p>Сравните характеристики выбранных медикаторов</p>
                <a href="srav.html"class="nav__link">Общая Сравнительная Таблица</a>
            </div>
            
            <?php if (empty($compare_items)): ?>
                <div class="compare-empty">
                    <div class="empty-icon">⚖️</div>
                    <h2 class="empty-title">Список сравнения пуст</h2>
                    <p class="empty-text">Добавьте товары для сравнения из каталога.</p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="catalog.php" class="compare-action-btn primary">
                            Перейти в каталог
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="compare-table-container">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th class="product-column">Характеристики</th>
                                <?php foreach ($compare_items as $product): ?>
                                <th>
                                    <div class="compare-product-card">
                                        <div class="compare-product-image">
                                            <?php if ($product['img_found']): ?>
                                                <img src="<?php echo htmlspecialchars($product['img_found']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php else: ?>
                                                <img src="medikator.jpg" alt="Нет изображения">
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="compare-product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <a href="compare.php?remove_id=<?php echo $product['id']; ?>&ids=<?php echo isset($_GET['ids']) ? $_GET['ids'] : ''; ?>" class="compare-remove-btn" onclick="return confirm('Удалить товар из сравнения?')">
                                            Удалить
                                        </a>
                                    </div>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $display_fields = [
                                'd_dosing' => 'Диапазон дозирования',
                                'performance' => 'Производительность',
                                'pressure' => 'Рабочее давление',
                                'temperature' => 'Температура жидкости',
                                'connections' => 'Тип подключения',
                                'm_seal' => 'Материал уплотнений',
                                'm_case' => 'Материал корпуса',
                                'dop' => 'Дополнительные характеристики',
                                'filtr' => 'Серия'
                            ];
                            
                            foreach ($display_fields as $field => $label): 
                                $has_data = false;
                                foreach ($compare_items as $product) {
                                    if (!empty($product[$field]) && $product[$field] != '-') {
                                        $has_data = true;
                                        break;
                                    }
                                }
                                
                                if ($has_data):
                            ?>
                            <tr>
                                <td class="product-column"><div class="spec-label"><?php echo $label; ?></div></td>
                                <?php foreach ($compare_items as $product): ?>
                                <td>
                                    <div class="spec-value <?php echo !empty($product[$field]) && $product[$field] != '-' ? 'highlight' : ''; ?>">
                                        <?php echo !empty($product[$field]) && $product[$field] != '-' ? htmlspecialchars($product[$field]) : '—'; ?>
                                    </div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                            
                            <tr>
                                <td class="product-column"><div class="spec-label">Действия</div></td>
                                <?php foreach ($compare_items as $product): ?>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                                           class="compare-action-btn secondary">
                                            Подробнее
                                        </a>
                                        <a href="contacts.php?product=<?php echo urlencode($product['name']); ?>" 
                                           class="compare-action-btn primary">
                                            Заказать
                                        </a>
                                    </div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="compare-actions">
                        <a href="compare.php?action=clear" class="compare-action-btn danger" onclick="return confirm('Очистить весь список сравнения?')">
                            Очистить сравнение
                        </a>
                        <a href="catalog.php" class="compare-action-btn secondary">
                            Добавить еще товары
                        </a>
                        <a href="contacts.php" class="compare-action-btn primary">
                            Заказать выбранное
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__col">
                    <a href="index.php" class="footer-logo">7company</a>
                    <p class="footer__text">Каталог медикаторов</p>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Контакты</h3>
                    <ul class="footer__list">
                        <li>📞 +375296052273</li>
                        <li>✉️ example@mail.com</li>
                        <li>📍 г. Минск, ул. Пушкина д. Колотушкина</li>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Навигация</h3>
                    <ul class="footer__list">
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="catalog.php">Каталог</a></li>
                        <li><a href="contacts.php">Контакты</a></li>
                        <li><a href="compare.php">Сравнение</a></li>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Часы работы</h3>
                    <ul class="footer__list">
                        <li>Пн-Пт: 9:00-18:00</li>
                        <li>Сб-Вс: Выходной</li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <p>&copy; 2025 7company. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
            const urlParams = new URLSearchParams(window.location.search);
            const idsParam = urlParams.get('ids');
            
            if (compareItems.length > 0 && !idsParam) {
                const ids = compareItems.map(item => item.id).join(',');
                window.location.href = `compare.php?ids=${ids}`;
            }
            
            if (compareItems.length === 0 && idsParam) {
                window.location.href = 'compare.php';
            }
        });
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
    </script>
</body>
</html>

<?php
$mysqli->close();
?>