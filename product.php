<?php
// Включаем отладку
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
// ============================================
$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

// Подключаемся к MySQL
$mysqli = new mysqli($host, $user, $pass, $db_name);

// Проверяем подключение
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Устанавливаем кодировку
$mysqli->set_charset("utf8mb4");

// ============================================
// ФУНКЦИЯ ДЛЯ ПОИСКА ФАЙЛОВ
// ============================================
function findFile($dbPath) {
    if (empty($dbPath) || $dbPath == '-' || $dbPath == 'NULL') {
        return null;
    }
    
    // Берем только имя файла
    $fileName = basename($dbPath);
    
    // Папки для поиска
    $searchFolders = [
        '',                      // Корень проекта
        'images/',              // Папка images
        'img/',                 // Папка img
        'products/',            // Папка products
        'uploads/',             // Папка uploads
        'diagrams/',            // Папка diagrams
        'pdfs/',                // Папка pdfs
        'images/products/',     // Вложенная images/products
        'img/products/',        // Вложенная img/products
    ];
    
    // Проверяем все варианты
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

// ============================================
// ПОЛУЧАЕМ ID ТОВАРА ИЗ URL
// ============================================
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$product = null;

// Запрос к базе данных
$sql = "SELECT * FROM medicator WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    
    // Вместо get_result() используем bind_result()
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Получаем информацию о колонках
        $meta = $stmt->result_metadata();
        $fields = array();
        $fieldReferences = array();
        
        while ($field = $meta->fetch_field()) {
            $fields[$field->name] = null;
            $fieldReferences[] = &$fields[$field->name];
        }
        
        call_user_func_array(array($stmt, 'bind_result'), $fieldReferences);
        
        if ($stmt->fetch()) {
            $product = array();
            foreach ($fields as $key => $value) {
                $product[$key] = $value;
            }
            
            // Ищем файлы
            $product['img_found'] = findFile($product['img'] ?? '');
            $product['diag_found'] = findFile($product['diag'] ?? '');
            $product['pdf_found'] = findFile($product['pdf'] ?? '');
        }
    }
    
    $stmt->close();
}

// ============================================
// ПОЛУЧАЕМ ПОХОЖИЕ ТОВАРЫ
// ============================================
$similar_products = array();
if ($product) {
    $similar_sql = "SELECT * FROM medicator WHERE id != ? ORDER BY RAND() LIMIT 6";
    $similar_stmt = $mysqli->prepare($similar_sql);
    
    if ($similar_stmt) {
        $similar_stmt->bind_param("i", $product_id);
        $similar_stmt->execute();
        $similar_stmt->store_result();
        
        // Получаем информацию о колонках
        $meta = $similar_stmt->result_metadata();
        $fields = array();
        $fieldReferences = array();
        
        while ($field = $meta->fetch_field()) {
            $fields[$field->name] = null;
            $fieldReferences[] = &$fields[$field->name];
        }
        
        call_user_func_array(array($similar_stmt, 'bind_result'), $fieldReferences);
        
        while ($similar_stmt->fetch()) {
            $similar_product = array();
            foreach ($fields as $key => $value) {
                $similar_product[$key] = $value;
            }
            
            // Ищем изображение
            $similar_product['img_found'] = findFile($similar_product['img'] ?? '');
            $similar_products[] = $similar_product;
        }
        
        $similar_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) . ' | 7 company' : 'Товар не найден | 7 company'; ?></title>
    <link rel="stylesheet" href="cs/style.css">
    <script src="j/script.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js" defer></script>

    <style>
        /* Стили для страницы товара */
        .product-page {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 40px;
        }
        
        .product-gallery {
            background: var(--card);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
        }
        
        .product-image-container {
            width: 100%;
            height: 400px;
            background: var(--muted);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .product-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-placeholder {
            text-align: center;
            color: var(--muted-foreground);
            padding: 40px;
        }
        
        .placeholder-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .product-title {
            font-size: 2.5rem;
            color: var(--foreground);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .product-subtitle {
            color: var(--muted-foreground);
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        /* ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ */
        .tech-specs {
            margin-top: 30px;
        }
        
        .tech-specs h2 {
            font-size: 1.8rem;
            color: var(--foreground);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
        }
        
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        
        .spec-card {
            background: var(--muted);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 25px;
        }
        
        .spec-card.full-width {
            grid-column: 1 / -1;
        }
        
        .spec-label {
            color: var(--muted-foreground);
            font-size: 0.95rem;
            margin-bottom: 10px;
            display: block;
            font-weight: 500;
        }
        
        .spec-value {
            color: var(--foreground);
            font-size: 1.2rem;
            font-weight: 600;
            line-height: 1.4;
        }
        
        /* Диаграмма */
        .diagram-container {
            margin-top: 40px;
            background: var(--card);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
        }
        
        .diagram-image-container {
            width: 100%;
            max-height: 500px;
            min-height: 300px;
            background: var(--muted);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .diagram-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        /* Кнопки */
        .actions-bar {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-compare {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-compare:hover {
            background: var(--primary);
            color: var(--primary-foreground);
        }
        
        .btn-request {
            background: #4CAF50;
            color: white;
        }
        
        .btn-request:hover {
            background: #45a049;
        }
        
        .btn-download {
            background: #2196F3;
            color: white;
        }
        
        .btn-download:hover {
            background: #1976D2;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--primary-foreground);
        }
        
        /* ОПИСАНИЕ ТОВАРА */
        .product-description {
            margin-top: 30px;
            padding: 25px;
            background: var(--muted);
            border-radius: 12px;
            border: 1px solid var(--border);
        }
        
        .product-description h3 {
            color: var(--foreground);
            margin-bottom: 15px;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
        }
        
        .description-content {
            color: var(--foreground);
            line-height: 1.7;
            font-size: 1.05rem;
        }
        
        .description-content p {
            margin-bottom: 15px;
        }
        
        .description-content ul, .description-content ol {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        .description-content li {
            margin-bottom: 8px;
        }
        
        .description-content strong {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Похожие товары */
        .similar-products {
            padding: 80px 0;
            background: var(--background);
            border-top: 1px solid var(--border);
        }
        
        .similar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .similar-card {
            background: var(--card);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .similar-image {
            height: 200px;
            background: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .similar-image img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }
        
        .similar-content {
            padding: 20px;
        }
        
        /* Адаптивность */
        @media (max-width: 1100px) {
            .product-layout {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .actions-bar {
                flex-direction: column;
            }
            
            .actions-bar .btn {
                width: 100%;
            }
            
            .product-image-container {
                height: 350px;
            }
            
            .product-title {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .product-image-container {
                height: 300px;
            }
            
            .diagram-image-container {
                min-height: 250px;
            }
        }
        /* Grid для десктопа */
.products-grid-desktop {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

/* Swiper для мобилки - скрыт на десктопе */
.products-swiper-mobile {
    display: none;
    position: relative;
    padding: 0 60px 40px;
    margin-bottom: 20px;
}

/* Адаптивность */
@media (max-width: 991px) {
    .products-grid-desktop {
        display: none !important;
    }
    
    .products-swiper-mobile {
        display: block;
    }
}

@media (max-width: 768px) {
    .products-swiper-mobile {
        padding: 0 50px 40px;
    }
}

@media (max-width: 576px) {
    .products-swiper-mobile {
        padding: 0 40px 40px;
    }
}

@media (max-width: 480px) {
    .products-swiper-mobile {
        padding: 0 20px 30px;
    }
}

/* Стили для навигации Swiper */
.products-swiper .swiper-button-next,
.products-swiper .swiper-button-prev {
    width: 40px;
    height: 40px;
    background: var(--primary);
    border-radius: 50%;
    color: var(--primary-foreground);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    top: 50%;
    margin-top: -20px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.8;
    transition: all 0.2s;
    z-index: 10;
}

.products-swiper .swiper-button-next:hover,
.products-swiper .swiper-button-prev:hover {
    opacity: 1;
    background: hsl(195 100% 40%);
    transform: scale(1.05);
}

.products-swiper .swiper-button-prev {
    left: 10px;
}

.products-swiper .swiper-button-next {
    right: 10px;
}

.products-swiper .swiper-button-prev:after,
.products-swiper .swiper-button-next:after {
    font-size: 18px;
    font-weight: bold;
}

.products-swiper .swiper-button-disabled {
    opacity: 0.3;
    cursor: not-allowed;
    transform: none !important;
}
    </style>
</head>
<body>
    <!-- ШАПКА САЙТА -->
    <div class="container">
        <?php include 'includes/header.php'; ?>
    </div>

    <!-- ОСНОВНОЙ КОНТЕНТ -->
    <main class="main">
        <?php if (!$product): ?>
            <section class="not-found" style="padding: 100px 0; text-align: center;">
                <div class="container">
                    <h1 style="color: var(--foreground); margin-bottom: 20px;">Товар не найден</h1>
                    <p style="color: var(--muted-foreground); margin-bottom: 40px;">
                        Запрашиваемый товар не существует или был удален.
                    </p>
                    <div>
                        <a href="catalog.php" class="btn btn-primary">
                            Вернуться в каталог
                        </a>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <nav style="background: var(--card); padding: 15px 0; border-bottom: 1px solid var(--border);">
                <div class="container">
                    <a href="index.php" style="color: var(--muted-foreground); text-decoration: none;">Главная</a>
                    <span style="color: var(--muted-foreground); margin: 0 10px;">></span>
                    <a href="catalog.php" style="color: var(--muted-foreground); text-decoration: none;">Каталог</a>
                    <span style="color: var(--muted-foreground); margin: 0 10px;">></span>
                    <span style="color: var(--foreground); font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
            </nav>

            <!-- СТРАНИЦА ТОВАРА -->
            <section class="product-page">
                <div class="product-layout">
                    <!-- ЛЕВАЯ КОЛОНКА - ИЗОБРАЖЕНИЕ ТОВАРА -->
                    <div class="product-gallery">
                        <!-- Изображение товара -->
                        <div class="product-image-container">
                            <?php if ($product['img_found']): ?>
                                <img src="<?php echo htmlspecialchars($product['img_found']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <span class="placeholder-icon">🏭</span>
                                    <p>Изображение отсутствует</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Кнопки действий -->
                        <div class="actions-bar">
                            <button class="btn btn-compare" data-product-id="<?php echo $product['id']; ?>">
                                <span style="margin-right: 8px;">⚖️</span>
                                Добавить к сравнению
                            </button>
                            <a href="contacts.php#contactFormSplit" class="btn btn-request">
                                <span style="margin-right: 8px;">📧</span>
                                Заказать
                            </a>
                            <?php if ($product['pdf_found']): ?>
                                <a href="<?php echo htmlspecialchars($product['pdf_found']); ?>" 
                                   class="btn btn-download" 
                                   target="_blank"
                                   download>
                                    <span style="margin-right: 8px;">📄</span>
                                    Скачать паспорт
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary">
                                    <span style="margin-right: 8px;">📄</span>
                                    Паспорт отсутствует
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- ОПИСАНИЕ ТОВАРА ИЗ ПОЛЯ OPIS -->
                        <?php if (!empty($product['opis']) && $product['opis'] != '-'): ?>
                        <div class="product-description">
                            <h3>
                                <span style="font-size: 1.5rem;">📝</span> Описание товара
                            </h3>
                            <div class="description-content">
                                <?php 
                                $opis = htmlspecialchars($product['opis']);
                                $opis = nl2br($opis);
                                $opis = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $opis);
                                $opis = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $opis);
                                echo $opis;
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <!-- Заголовок и краткая информация -->
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="product-subtitle">
                            Профессиональный дозатор для систем полива и внесения удобрений
                        </p>
                        
                        <!-- ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ -->
                        <div class="tech-specs">
                            <h2>Технические характеристики</h2>
                            <div class="specs-grid">
                                <?php if (!empty($product['d_dosing']) && $product['d_dosing'] != '-'): ?>
                                <div class="spec-card">
                                    <span class="spec-label">Диапазон дозирования</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['d_dosing']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['performance']) && $product['performance'] != '-'): ?>
                                <div class="spec-card">
                                    <span class="spec-label">Производительность</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['performance']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['pressure']) && $product['pressure'] != '-'): ?>
                                <div class="spec-card">
                                    <span class="spec-label">Рабочее давление</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['pressure']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['temperature']) && $product['temperature'] != '-'): ?>
                                <div class="spec-card">
                                    <span class="spec-label">Температура жидкости</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['temperature']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['connections']) && $product['connections'] != '-'): ?>
                                <div class="spec-card full-width">
                                    <span class="spec-label">Тип подключения</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['connections']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['m_seal']) && $product['m_seal'] != '-'): ?>
                                <div class="spec-card">
                                    <span class="spec-label">Материал уплотнений</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['m_seal']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['m_case']) && $product['m_case'] != '-'): ?>
                                <div class="spec-card">
                                    <span class="spec-label">Материал корпуса</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['m_case']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['dop']) && $product['dop'] != '-'): ?>
                                <div class="spec-card full-width">
                                    <span class="spec-label">Дополнительные характеристики</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['dop']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- ДИАГРАММА -->
                        <?php if (!empty($product['diag']) && $product['diag'] != '-'): ?>
                        <div class="diagram-container">
                            <h2 style="color: var(--foreground); margin-bottom: 25px; font-size: 1.8rem;">Техническая схема</h2>
                            
                            <div class="diagram-image-container">
                                <?php if ($product['diag_found']): ?>
                                    <img src="<?php echo htmlspecialchars($product['diag_found']); ?>" 
                                         alt="Техническая схема <?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <span class="placeholder-icon">📊</span>
                                        <p>Диаграмма не загружена</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <p style="color: var(--muted-foreground); line-height: 1.6; text-align: center; margin-top: 20px;">
                                Принципиальная схема работы дозатора
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            
            <!-- ПОХОЖИЕ ТОВАРЫ -->
            <?php if (!empty($similar_products)): ?>
            <section class="similar-products">
                <div class="container">
                    <h2 style="text-align: center; color: var(--foreground); margin-bottom: 50px; font-size: 2rem;">Похожие товары</h2>
                    
                    <!-- Grid для десктопа -->
                    <div class="products-grid-desktop">
                        <?php foreach ($similar_products as $similar): ?>
                        <div class="product-card">
                            <div class="product-card__image">
                                <?php if ($similar['img_found']): ?>
                                    <img src="<?php echo htmlspecialchars($similar['img_found']); ?>" 
                                         alt="<?php echo htmlspecialchars($similar['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <span class="placeholder-icon">🏭</span>
                                        <p>Нет изображения</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-card__content">
                                <h3 class="product-card__title">
                                    <a href="product.php?id=<?php echo $similar['id']; ?>" class="product-link">
                                        <?php echo htmlspecialchars($similar['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-card__desc">
                                    <?php if (!empty($similar['d_dosing'])): ?>
                                        <span class="spec-item">📏 <?php echo htmlspecialchars($similar['d_dosing']); ?></span><br>
                                    <?php endif; ?>
                                    <?php if (!empty($similar['performance'])): ?>
                                        <span class="spec-item">⚡ <?php echo htmlspecialchars($similar['performance']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div class="product-card__price">Подробности по запросу</div>
                                <div class="product-card__actions">
                                    <button class="btn btn-secondary" 
                                            data-product-id="<?php echo $similar['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($similar['name']); ?>">
                                        В сравнение
                                    </button>
                                    <a href="product.php?id=<?php echo $similar['id']; ?>" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Swiper для мобилки -->
                    <div class="products-swiper-mobile">
                        <div class="swiper products-swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($similar_products as $similar): ?>
                                <div class="swiper-slide">
                                    <div class="product-card">
                                        <div class="product-card__image">
                                            <?php if ($similar['img_found']): ?>
                                                <img src="<?php echo htmlspecialchars($similar['img_found']); ?>" 
                                                     alt="<?php echo htmlspecialchars($similar['name']); ?>"
                                                     loading="lazy">
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <span class="placeholder-icon">🏭</span>
                                                    <p>Нет изображения</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-card__content">
                                            <h3 class="product-card__title">
                                                <a href="product.php?id=<?php echo $similar['id']; ?>" class="product-link">
                                                    <?php echo htmlspecialchars($similar['name']); ?>
                                                </a>
                                            </h3>
                                            <p class="product-card__desc">
                                                <?php if (!empty($similar['d_dosing'])): ?>
                                                    <span class="spec-item">📏 <?php echo htmlspecialchars($similar['d_dosing']); ?></span><br>
                                                <?php endif; ?>
                                                <?php if (!empty($similar['performance'])): ?>
                                                    <span class="spec-item">⚡ <?php echo htmlspecialchars($similar['performance']); ?></span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="product-card__price">Подробности по запросу</div>
                                            <div class="product-card__actions">
                                                <button class="btn btn-secondary" 
                                                        data-product-id="<?php echo $similar['id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($similar['name']); ?>">
                                                    В сравнение
                                                </button>
                                                <a href="product.php?id=<?php echo $similar['id']; ?>" class="btn btn-primary">Подробнее</a>
                                                 <a href="catalog.php" class="btn btn-large">Весь каталог →</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Навигация -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                    
                    <div class="text-center" >
                        <a href="catalog.php" class="btn btn-large">Весь каталог →</a>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- ПОДВАЛ -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Добавление в сравнение
        document.querySelectorAll('[data-product-id]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                let productName = this.getAttribute('data-product-name');
                
                if (!productName) {
                    const productCard = this.closest('.product-card');
                    if (productCard) {
                        const titleElement = productCard.querySelector('.product-card__title');
                        if (titleElement) {
                            productName = titleElement.textContent.trim();
                        }
                    }
                }
                
                if (productName) {
                    let compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
                    
                    const exists = compareItems.some(item => item.id == productId);
                    if (!exists) {
                        compareItems.push({
                            id: productId,
                            name: productName,
                            date: new Date().toISOString()
                        });
                        localStorage.setItem('compareItems', JSON.stringify(compareItems));
                        
                        // Визуальная обратная связь
                        const originalText = this.textContent;
                        this.textContent = '✓ Добавлено';
                        this.style.backgroundColor = '#4CAF50';
                        
                        setTimeout(() => {
                            this.textContent = originalText;
                            this.style.backgroundColor = '';
                        }, 2000);
                        
                        alert('Товар добавлен в сравнение!');
                    } else {
                        alert('Товар уже в списке сравнения!');
                    }
                }
            });
        });
        
        // Для кнопки сравнения на странице товара
        const compareBtn = document.querySelector('.btn-compare');
        if (compareBtn) {
            compareBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = document.querySelector('.product-title').textContent.trim();
                
                let compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
                
                const exists = compareItems.some(item => item.id == productId);
                if (!exists) {
                    compareItems.push({
                        id: productId,
                        name: productName,
                        date: new Date().toISOString()
                    });
                    localStorage.setItem('compareItems', JSON.stringify(compareItems));
                    
                    // Визуальная обратная связь
                    const originalText = this.textContent;
                    this.textContent = '✓ Добавлено';
                    this.style.backgroundColor = '#4CAF50';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.backgroundColor = '';
                    }, 2000);
                    
                    alert('Товар добавлен в сравнение!');
                } else {
                    alert('Товар уже в списке сравнения!');
                }
            });
        }
        
        // Инициализация Swiper для похожих товаров
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация Swiper только на мобильных устройствах
            if (window.innerWidth <= 991) {
                console.log('Мобильное устройство, инициализируем Swiper');
                
                // Удаляем предыдущие экземпляры Swiper
                if (window.productsSwiper) {
                    window.productsSwiper.destroy();
                }
                
                window.productsSwiper = new Swiper('.products-swiper', {
                    // Конфигурация Swiper
                    loop: true,
                    speed: 400,
                    slidesPerView: 1,
                    spaceBetween: 15,
                    centeredSlides: true,
                    grabCursor: true,
                    
                    // Навигация
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    
                    // Адаптивность
                    breakpoints: {
                        576: {
                            slidesPerView: 1.2,
                        },
                        768: {
                            slidesPerView: 1.5,
                        }
                    },
                    
                    // События
                    on: {
                        init: function() {
                            console.log('Swiper инициализирован');
                            // Принудительно показываем кнопки навигации
                            if (this.navigation.nextEl) this.navigation.nextEl.style.display = 'flex';
                            if (this.navigation.prevEl) this.navigation.prevEl.style.display = 'flex';
                        },
                    }
                });
            } else {
                console.log('Десктоп, Swiper не нужен');
                // Скрываем кнопки навигации на десктопе
                const nextBtn = document.querySelector('.swiper-button-next');
                const prevBtn = document.querySelector('.swiper-button-prev');
                if (nextBtn) nextBtn.style.display = 'none';
                if (prevBtn) prevBtn.style.display = 'none';
            }
            
            // Обработчик изменения размера окна
            window.addEventListener('resize', function() {
                clearTimeout(window.resizeTimer);
                window.resizeTimer = setTimeout(function() {
                    // Проверяем размер экрана и переинициализируем Swiper при необходимости
                    const isMobile = window.innerWidth <= 991;
                    const hasSwiper = !!window.productsSwiper;
                    
                    if (isMobile && !hasSwiper) {
                        // Если перешли на мобилку, но Swiper не инициализирован
                        window.location.reload(); // Проще перезагрузить страницу
                    } else if (!isMobile && hasSwiper) {
                        // Если перешли на десктоп, а Swiper был инициализирован
                        window.productsSwiper.destroy();
                        window.productsSwiper = null;
                        document.querySelector('.products-swiper-mobile').style.display = 'none';
                        document.querySelector('.products-grid-desktop').style.display = 'grid';
                    }
                }, 250);
            });
        });
    </script>
</body>
</html>

<?php
// Закрываем соединение с БД
$mysqli->close();
?>