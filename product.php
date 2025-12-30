<?php
// ============================================
// ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
// ============================================
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'catalog';

// Подключаемся к MySQL
$mysqli = new mysqli($host, $user, $pass, $db_name);

// Проверяем подключение
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных");
}

// Устанавливаем кодировку
$mysqli->set_charset("utf8mb4");

// ============================================
// ПОЛУЧАЕМ ID ТОВАРА ИЗ URL
// ============================================
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Запрос к базе данных
$sql = "SELECT * FROM medicator WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Если товар не найден
if ($result->num_rows === 0) {
    $product = null;
} else {
    $product = $result->fetch_assoc();
    
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
    
    // Ищем файлы
    $product['img_found'] = findFile($product['img'] ?? '');
    $product['diag_found'] = findFile($product['diag'] ?? '');
    $product['pdf_found'] = findFile($product['pdf'] ?? '');
}

// Закрываем запрос
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) . ' | 7 company' : 'Товар не найден | 7 company'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js" defer></script>
    <script src="js/script.js" defer></script>
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
    </style>
</head>
<body>
    <!-- ШАПКА САЙТА -->
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
                        <li>
                            <a href="compare.php" class="nav__link">Сравнение</a>
                        </li>
                        <li><a href="contacts.php" class="btn btn-primary">Заказать</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- ОСНОВНОЙ КОНТЕНТ -->
    <main class="main">
        <?php if (!$product): ?>
            <!-- Если товар не найден -->
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
            <!-- Хлебные крошки -->
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
                            <a href="contacts.php" class="btn btn-request">
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
                    
                    <!-- ПРАВАЯ КОЛОНКА - НАЗВАНИЕ И ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ -->
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
            <section class="similar-products">
                <div class="container">
                    <h2 style="text-align: center; color: var(--foreground); margin-bottom: 50px; font-size: 2rem;">Похожие товары</h2>
                    
                    <div class="similar-grid">
                        <?php
                        // Получаем похожие товары
                        $similar_sql = "SELECT * FROM medicator WHERE id != ? LIMIT 4";
                        $similar_stmt = $mysqli->prepare($similar_sql);
                        $similar_stmt->bind_param("i", $product_id);
                        $similar_stmt->execute();
                        $similar_result = $similar_stmt->get_result();
                        
                        while ($similar = $similar_result->fetch_assoc()):
                            // Ищем изображение для похожего товара
                            $similarImg = findFile($similar['img'] ?? '');
                        ?>
                        <div class="similar-card">
                            <div class="similar-image">
                                <?php if ($similarImg): ?>
                                    <img src="<?php echo htmlspecialchars($similarImg); ?>" 
                                         alt="<?php echo htmlspecialchars($similar['name']); ?>">
                                <?php else: ?>
                                    <div style="text-align: center; color: var(--muted-foreground); padding: 20px;">
                                        <span style="font-size: 3rem;">🏭</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="similar-content">
                                <h3 style="color: var(--foreground); margin-bottom: 10px; font-size: 1.2rem;">
                                    <a href="product.php?id=<?php echo $similar['id']; ?>" 
                                       style="color: var(--foreground); text-decoration: none;">
                                        <?php echo htmlspecialchars($similar['name']); ?>
                                    </a>
                                </h3>
                                <p style="color: var(--muted-foreground); font-size: 0.9rem; margin-bottom: 15px;">
                                    <?php echo htmlspecialchars($similar['d_dosing']); ?>
                                </p>
                                <div style="display: flex; gap: 10px;">
                                    <a href="product.php?id=<?php echo $similar['id']; ?>" 
                                       class="btn btn-primary" 
                                       style="flex: 1; text-align: center;">
                                        Подробнее
                                    </a>
                                    <button class="btn btn-secondary" 
                                            data-product-id="<?php echo $similar['id']; ?>"
                                            style="flex: 1;">
                                        В сравнение
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php 
                        endwhile; 
                        $similar_stmt->close();
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <!-- ПОДВАЛ -->
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
        // Добавление в сравнение
        document.querySelectorAll('[data-product-id]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productCard = this.closest('.similar-card');
                let productName = '';
                
                if (productCard) {
                    const titleElement = productCard.querySelector('h3');
                    if (titleElement) {
                        productName = titleElement.textContent.trim();
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
        if (compareBtn && !compareBtn.closest('.similar-card')) {
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
    </script>
</body>
</html>

<?php
// Закрываем соединение с БД
$mysqli->close();
?>