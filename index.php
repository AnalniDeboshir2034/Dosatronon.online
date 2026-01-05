<?php
$host = 'localhost';
$user = 'dosatronon_dosatronon';
$pass = 'dosatronon_dosatronon';
$db_name = 'dosatronon_catalog';

$mysqli = new mysqli($host, $user, $pass, $db_name);

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
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

$sql = "SELECT * FROM medicator LIMIT 6";
$result = $mysqli->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['img_found'] = findFile($row['img'] ?? '');
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>7 company - Каталог медикаторов</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js" defer></script>
    <script src="js/script.js" defer></script>
 <style>
    /* ===== ОСНОВНЫЕ СТИЛИ ===== */
    .burger-btn {
        display: none;
        flex-direction: column;
        justify-content: space-around;
        width: 30px;
        height: 30px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 1001;
    
    }
    .burger-btn__line {
        width: 100%;
        height: 3px;
        background: hsl(200 100% 98%);
        border-radius: 3px;
        transition: all 0.3s ease;
    }
    
    .burger-btn.active .burger-btn__line:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }
    
    .burger-btn.active .burger-btn__line:nth-child(2) {
        opacity: 0;
    }
    
    .burger-btn.active .burger-btn__line:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }
    
    /* Навигация всегда видима */
    .nav {
        display: flex;
    }
    
    /* Затемнение фона */
    .nav-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(5px);
        z-index: 999;
    }
    
    .nav-overlay.active {
        display: block;
    }
    
    /* ===== МОБИЛЬНАЯ ВЕРСИЯ (до 1024px) ===== */
    @media (max-width: 1024px) {
        /* Показываем бургер */
        .burger-btn {
            display: flex;
            order: 1;
        }
        
        /* Горизонтальный хеддер */
        .header__inner {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 15px !important;
            min-height: 70px !important;
        }
        
        .logo {
            flex: 1 !important;
            order: 2 !important;
            text-align: center !important;
        }
        
        .header__order-btn {
            order: 3 !important;
            flex-shrink: 0 !important;
            padding: 10px 15px !important;
            font-size: 0.9rem !important;
        }
        
        /* Скрываем навигацию по умолчанию */
        .nav {
            position: fixed !important;
            top: 0 !important;
            left: -100% !important;
            width: 320px !important;
            max-width: 85% !important;
            height: 100vh !important;
            background: hsl(220 40% 5%) !important;
            z-index: 1000 !important;
            transition: left 0.3s ease !important;
            overflow-y: auto !important;
            padding: 80px 25px 30px 25px !important;
        }
        
        .nav.active {
            left: 0 !important;
            box-shadow: 5px 0 30px rgba(0,0,0,0.5);
        }
        
        /* Вертикальное меню */
        .nav__list {
            flex-direction: column !important;
            gap: 0 !important;
            width: 100% !important;
        }
        
        .nav__item {
            width: 100% !important;
            border-bottom: 1px solid hsl(200 30% 18%) !important;
        }
        
        .nav__item:last-child {
            border-bottom: none !important;
        }
        
        /* Ссылки в меню */
        .nav__link, .catalog-link {
            display: block !important;
            padding: 18px 0 !important;
            color: var(--muted-foreground) !important;
            text-decoration: none !important;
            font-size: 1.1rem !important;
            width: 100% !important;
            text-align: left !important;
            background: none !important;
            border: none !important;
            cursor: pointer !important;
            font-family: inherit !important;
        }
        
        .catalog-link {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
        
        .catalog-link::after {
            content: '▾' !important;
            transition: transform 0.3s ease !important;
        }
        
        .catalog-link.active::after {
            transform: rotate(180deg) !important;
        }
        
        /* Выпадающее меню каталога */
        .dropdown-menu {
            display: none !important;
            padding-left: 20px !important;
            list-style: none !important;
            margin: 10px 0 !important;
        }
        
        .dropdown-menu.active {
            display: block !important;
        }
        
        .dropdown-menu li {
            border-bottom: none !important;
        }
        
        .dropdown-menu a {
            display: block !important;
            padding: 15px 0 !important;
            color: hsl(200 20% 70%) !important;
            text-decoration: none !important;
        }
        
        .dropdown-menu a:hover {
            color: var(--primary) !important;
        }
        
        /* Поиск в мобильном меню */
        .nav__item--search {
            padding: 20px 0 !important;
        }
        
        .search-box {
            flex-direction: column !important;
            width: 100% !important;
            gap: 10px !important;
        }
        
        .search-box input {
            width: 100% !important;
            padding: 14px !important;
            border-radius: 8px !important;
        }
        
        .search-box button {
            width: 100% !important;
            padding: 14px !important;
            border-radius: 8px !important;
            margin-top: 5px !important;
        }
        
        /* Блокируем скролл при открытом меню */
        body.menu-open {
            overflow: hidden !important;
        }
    }
    
    /* ===== ДЕСКТОПНАЯ ВЕРСИЯ (от 1025px) ===== */
    @media (min-width: 1025px) {
        /* Скрываем бургер и оверлей */
        .burger-btn, .nav-overlay {
            display: none !important;
        }
        
        /* Горизонтальное меню */
        .nav {
            display: flex !important;
            position: relative !important;
            height: auto !important;
            width: auto !important;
            background: transparent !important;
            padding: 0 !important;
        }
        
        .nav__list {
            display: flex !important;
            flex-direction: row !important;
            gap: 30px !important;
            align-items: center !important;
        }
        
        .nav__item {
            position: relative !important;
            border-bottom: none !important;
            width: auto !important;
        }
        
        /* Ссылки в меню */
        .nav__link, .catalog-link {
            color: var(--muted-foreground) !important;
            text-decoration: none !important;
            padding: 8px 0 !important;
            position: relative !important;
            font-size: 1rem !important;
            transition: color 0.3s ease !important;
        }
        
        .nav__link:hover, .catalog-link:hover {
            color: var(--foreground) !important;
        }
        
        .nav__link::before, .catalog-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }
        
        .nav__link:hover::before, .catalog-link:hover::before {
            width: 100%;
        }
        
        /* Каталог с выпадающим меню */
        .catalog-link {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }
        
        .catalog-link::after {
            content: '▾' !important;
            font-size: 0.8rem !important;
            transition: transform 0.3s ease !important;
        }
        
        .nav__item.has-dropdown:hover .catalog-link::after {
            transform: rotate(180deg) !important;
        }
        
        /* Выпадающее меню (появляется при наведении) */
        .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            min-width: 220px !important;
            background: hsl(220 40% 8%) !important;
            border: 1px solid hsl(200 30% 20%) !important;
            border-radius: var(--radius) !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.7) !important;
            padding: 10px 0 !important;
            margin-top: 15px !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translateY(-10px) !important;
            transition: all 0.3s ease !important;
            z-index: 1000 !important;
            display: block !important;
        }
        
        .nav__item.has-dropdown:hover .dropdown-menu {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
        
        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 20px;
            width: 12px;
            height: 12px;
            background: hsl(220 40% 8%);
            border-left: 1px solid hsl(200 30% 20%);
            border-top: 1px solid hsl(200 30% 20%);
            transform: rotate(45deg);
        }
        
        .dropdown-menu li {
            padding: 0;
            margin: 0;
        }
        
        .dropdown-menu a {
            display: block;
            padding: 12px 20px;
            color: hsl(200 20% 70%);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .dropdown-menu a:hover {
            color: var(--primary);
            background: hsl(195 100% 50% / 0.1);
            padding-left: 25px;
        }
        
        /* Поиск на десктопе */
        .search-box {
            display: flex;
            align-items: center;
            gap: 0;
        }
        
        .search-box input {
            width: 200px;
            padding: 10px 15px;
            background: hsl(220 40% 10%);
            border: 1px solid hsl(200 30% 25%);
            border-radius: 8px 0 0 8px;
            color: var(--foreground);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px hsl(195 100% 50% / 0.1);
        }
        
        .search-box input::placeholder {
            color: hsl(200 20% 45%);
        }
        
        .search-box button {
            padding: 10px 20px;
            background: linear-gradient(135deg, hsl(195 100% 50%), hsl(180 100% 50%));
            border: none;
            border-radius: 0 8px 8px 0;
            color: var(--primary-foreground);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px hsl(195 100% 50% / 0.3);
        }
    }
    
    /* ===== ОБЩИЕ СТИЛИ ===== */
    .image-placeholder {
        width: 100%;
        height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
        color: #666;
        border-radius: 8px;
    }
    
    .placeholder-icon {
        font-size: 3rem;
        margin-bottom: 10px;
    }
    
    .product-link {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .product-link:hover {
        color: #2196F3;
    }
    
    .spec-item {
        display: inline-block;
        margin-bottom: 5px;
        font-size: 0.9rem;
        color: #555;
    }
    
    .btn-success {
        background-color: #4CAF50 !important;
        color: white !important;
        border-color: #4CAF50 !important;
    }
    
    .notification {
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
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .product-card__image img {
        width: 100%;
        height: 200px;
        object-fit: contain;
        background: #f9f9f9;
        border-radius: 8px 8px 0 0;
    }
    
    .product-card__actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .product-card__actions .btn {
        flex: 1;
        padding: 10px;
        font-size: 0.9rem;
    }
    @media (max-width: 640px) {
    .header__inner {
        padding: 10px 15px !important;
        gap: 10px !important;
        min-height: 60px !important;
    }
    
    /* Уменьшаем логотип */
    .logo__img {
        width: 32px !important;
        height: 32px !important;
        flex-shrink: 0 !important;
    }
    
    .logo__text {
        font-size: 0.9rem !important;
        white-space: nowrap !important;
    }
    
    /* Делаем кнопку "Заказать" компактнее */
    .header__order-btn {
        padding: 8px 12px !important;
        font-size: 0.85rem !important;
        min-width: auto !important;
        flex-shrink: 0 !important;
    }
    
    /* Настраиваем бургер-кнопку */
    .burger-btn {
        width: 26px !important;
        height: 26px !important;
        flex-shrink: 0 !important;
    }
    
    .burger-btn__line {
        height: 2px !important;
    }
    
    /* Уменьшаем отступы в меню */
    .nav {
        width: 280px !important;
        padding: 70px 20px 20px 20px !important;
    }
    
    /* Адаптируем каталог товаров на главной */
    .products__grid {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
    
    .product-card__actions {
        flex-direction: column !important;
    }
    
    .product-card__actions .btn {
        width: 100% !important;
    }
    }
</style>

</head>
<body>
   <header class="header">
    <div class="container">
        <div class="header__inner">
            <!-- Бургер-кнопка -->
            <button class="burger-btn" id="burgerBtn" aria-label="Открыть меню">
                <span class="burger-btn__line"></span>
                <span class="burger-btn__line"></span>
                <span class="burger-btn__line"></span>
            </button>

            <!-- Логотип -->
            <a href="index.php" class="logo">
                <div class="logo__img">
                    <img src="logo.jpg" alt="7company" width="40" height="40">
                </div>
                <span class="logo__text">7 company</span>
            </a>

   

            <!-- Затемнение фона -->
            <div class="nav-overlay" id="navOverlay"></div>

            <!-- Навигация -->
            <nav class="nav" id="mainNav">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="index.php" class="nav__link nav__link--active">Главная</a>
                    </li>
                    <li class="nav__item has-dropdown">
                        <a href="catalog.php" class="catalog-link" id="catalogLink">
                            Каталог
                        </a>
                        <ul class="dropdown-menu" id="catalogDropdown">
                            <li><a href="catalog.php?category=all">Все модели</a></li>
                            <li><a href="catalog.php?category=DIA">DIA</a></li>
                            <li><a href="catalog.php?category=D07">D07</a></li>
                        </ul>
                    </li>
                    <li class="nav__item">
                        <a href="contacts.php" class="nav__link">Контакты</a>
                    </li>
                    <li class="nav__item">
                        <a href="compare.php" class="nav__link">Сравнение</a>
                    </li>
                    <li class="nav__item nav__item--search">
                        <div class="sidebar-search">
                            <div class="search-box">
                                <input type="text" id="searchInput" placeholder="Поиск по названию...">
                                <button id="searchBtn" type="button">🔍</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
                     <a href="contacts.php" class="btn btn-primary header__order-btn">Заказать</a>
        </div>
    </div>
</header>

    <main class="main">
        <section class="hero">
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide hero-slide">
                        <div class="container">
                            <div class="hero-slide__content">
                                <h1 class="hero-slide__title">Профессиональные медикаторы</h1>
                                <p class="hero-slide__text">Точное дозирование для сельского хозяйства</p>
                                <a href="catalog.php" class="btn btn-primary btn-hero">Смотреть каталог</a>
                            </div>
                        </div>
                        <div class="hero-slide__overlay"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="about">
            <div class="container">
                <h2 class="section-title">О компании</h2>
                <div class="about__content">
                    <div class="about__text">
                        <p><strong>7 company</strong> — поставщик профессионального оборудования для систем дозирования и орошения. Мы работаем с сельскохозяйственными предприятиями, тепличными комплексами и промышленными объектами.</p>
                        <p>Наша специализация — медикаторы и дозаторы для точного внесения удобрений, средств защиты растений и химических реагентов.</p>
                        <ul class="about__features">
                            <li>✅ Гарантия качества продукции</li>
                            <li>✅ Техническая поддержка</li>
                            <li>✅ Доставка по всей Беларуси</li>
                            <li>✅ Индивидуальный подход</li>
                        </ul>
                    </div>
                    <div class="about__image">
                        <img src="logo.jpg" alt="7 company">
                    </div>
                </div>
            </div>
        </section>

        <section class="products">
            <div class="container">
                <h2 class="section-title">Популярные товары</h2>
                <div class="products__grid">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                        <div class="product-card">
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
                                <h3 class="product-card__title">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="product-link">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-card__desc">
                                    <?php if (!empty($product['d_dosing'])): ?>
                                        <span class="spec-item">📏 Дозировка: <?php echo htmlspecialchars($product['d_dosing']); ?></span><br>
                                    <?php endif; ?>
                                    <?php if (!empty($product['performance'])): ?>
                                        <span class="spec-item">⚡ Производительность: <?php echo htmlspecialchars($product['performance']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div class="product-card__price">Подробности по запросу</div>
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
                        <div class="product-card">
                            <div class="product-card__image">
                                <img src="medikator.jpg" alt="Медикатор" loading="lazy">
                            </div>
                            <div class="product-card__content">
                                <h3 class="product-card__title">Dosatron DIA4AL VF</h3>
                                <p class="product-card__desc">
                                    <span class="spec-item">📏 Дозировка: 0.2-4%</span><br>
                                    <span class="spec-item">⚡ Производительность: 5-120 л/ч</span>
                                </p>
                                <div class="product-card__price">Подробности по запросу</div>
                                <div class="product-card__actions">
                                    <button class="btn btn-secondary" data-product-id="1" data-product-name="Dosatron DIA4AL VF">В сравнение</button>
                                    <a href="product.php?id=1" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-card__image">
                                <img src="medikator.jpg" alt="Медикатор" loading="lazy">
                            </div>
                            <div class="product-card__content">
                                <h3 class="product-card__title">Повагоп DBREE</h3>
                                <p class="product-card__desc">
                                    <span class="spec-item">📏 Дозировка: 0.5-2%</span><br>
                                    <span class="spec-item">⚡ Производительность: 3-50 л/ч</span>
                                </p>
                                <div class="product-card__price">Подробности по запросу</div>
                                <div class="product-card__actions">
                                    <button class="btn btn-secondary" data-product-id="2" data-product-name="Повагоп DBREE">В сравнение</button>
                                    <a href="product.php?id=2" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-card__image">
                                <img src="medikator.jpg" alt="Медикатор" loading="lazy">
                            </div>
                            <div class="product-card__content">
                                <h3 class="product-card__title">Dosatron D25RE2</h3>
                                <p class="product-card__desc">
                                    <span class="spec-item">📏 Дозировка: 0.2-6%</span><br>
                                    <span class="spec-item">⚡ Производительность: 50-300 л/ч</span>
                                </p>
                                <div class="product-card__price">Подробности по запросу</div>
                                <div class="product-card__actions">
                                    <button class="btn btn-secondary" data-product-id="3" data-product-name="Dosatron D25RE2">В сравнение</button>
                                    <a href="product.php?id=3" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center">
                    <a href="catalog.php" class="btn btn-large">Весь каталог →</a>
                </div>
            </div>
        </section>

        <section class="reviews">
            <div class="container">
                <h2 class="section-title">Отзывы наших клиентов</h2>
                <div class="reviews__slider">
                    <div class="swiper reviews-swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide review-slide">
                                <div class="review-card">
                                    <div class="review-card__header">
                                        <div class="review-card__avatar">
                                            JC
                                        </div>
                                        <div class="review-card__info">
                                            <h4 class="review-card__name">Исус Христосович</h4>
                                            <div class="review-card__rating">
                                                ⭐⭐⭐⭐⭐
                                                <span class="review-card__date">25.12.2023</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-card__body">
                                        <p>Хлеб и вино лучше всего растут с профессиональным оборудованием. Дозаторы работают безотказно!</p>
                                    </div>
                                    <div class="review-card__footer">
                                        <span class="review-card__farm">Дружественная община</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="swiper-slide review-slide">
                                <div class="review-card">
                                    <div class="review-card__header">
                                        <div class="review-card__avatar">
                                            MI
                                        </div>
                                        <div class="review-card__info">
                                            <h4 class="review-card__name">Мойша Исакович</h4>
                                            <div class="review-card__rating">
                                                ⭐⭐⭐⭐⭐
                                                <span class="review-card__date">14.05.2024</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-card__body">
                                        <p>Таки добра, очинь дешево для такого качества! Оборудование работает уже второй сезон без нареканий.</p>
                                    </div>
                                    <div class="review-card__footer">
                                        <span class="review-card__farm">ООО "Сингапурская ферма"</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="swiper-slide review-slide">
                                <div class="review-card">
                                    <div class="review-card__header">
                                        <div class="review-card__avatar">
                                            ПИ
                                        </div>
                                        <div class="review-card__info">
                                            <h4 class="review-card__name">Петр Петрович</h4>
                                            <div class="review-card__rating">
                                                ⭐⭐⭐⭐⭐
                                                <span class="review-card__date">03.11.2024</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-card__body">
                                        <p>Текст</p>
                                    </div>
                                    <div class="review-card__footer">
                                        <span class="review-card__farm">Что то интересное</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
                        <li>📞 +375 (29) 605-22-73</li>
                        <li>✉️ info@7company.by</li>
                        <li>📍 г. Минск, ул. Толбухина д.2</li>
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
    // Обработчик для кнопок сравнения
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
                
                showNotification(`Товар "${productName}" добавлен в сравнение!`);
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
    
    // Функция уведомлений
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Основной код
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация слайдера отзывов
        if (document.querySelector('.reviews-swiper')) {
            new Swiper('.reviews-swiper', {
                loop: true,
                speed: 600,
                autoplay: { delay: 5000 },
                slidesPerView: 1,
                spaceBetween: 20,
                breakpoints: {
                    768: { slidesPerView: 2 },
                    992: { slidesPerView: 3 }
                }
            });
        }
        
        // Получаем элементы
        const burgerBtn = document.getElementById('burgerBtn');
        const mainNav = document.getElementById('mainNav');
        const navOverlay = document.getElementById('navOverlay');
        const catalogLink = document.getElementById('catalogLink');
        const catalogDropdown = document.getElementById('catalogDropdown');
        
        // Проверяем, мобилка или десктоп
        const isMobile = window.innerWidth <= 1024;
        
        // ===== ФУНКЦИИ ДЛЯ МЕНЮ =====
        
        // Открытие/закрытие бургер-меню
        function toggleMenu() {
            burgerBtn.classList.toggle('active');
            mainNav.classList.toggle('active');
            navOverlay.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        }
        
        // Закрытие меню
        function closeMenu() {
            burgerBtn.classList.remove('active');
            mainNav.classList.remove('active');
            navOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            
            // Закрываем выпадающее меню каталога
            if (catalogDropdown) {
                catalogDropdown.classList.remove('active');
                catalogLink.classList.remove('active');
            }
        }
        
        // ===== ОБРАБОТЧИКИ СОБЫТИЙ =====
        
        // Бургер-кнопка
        if (burgerBtn) {
            burgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMenu();
            });
        }
        
        // Затемнение фона
        if (navOverlay) {
            navOverlay.addEventListener('click', closeMenu);
        }
        
        // Выпадающее меню каталога на МОБИЛКЕ
        if (catalogLink && catalogDropdown && isMobile) {
            catalogLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Переключаем выпадающее меню
                catalogLink.classList.toggle('active');
                catalogDropdown.classList.toggle('active');
            });
            
            // Закрытие при клике на ссылки в выпадающем меню
            catalogDropdown.addEventListener('click', function(e) {
                if (e.target.tagName === 'A') {
                    closeMenu();
                }
            });
        }
        
        // Закрытие меню при клике на обычные ссылки на МОБИЛКЕ
        if (isMobile) {
            document.querySelectorAll('.nav__link').forEach(link => {
                if (!link.classList.contains('catalog-link')) {
                    link.addEventListener('click', closeMenu);
                }
            });
        }
        
        // Поиск
        const searchBtn = document.getElementById('searchBtn');
        const searchInput = document.getElementById('searchInput');
        
        if (searchBtn && searchInput) {
            function performSearch() {
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    window.location.href = `catalog.php?search=${encodeURIComponent(searchTerm)}`;
                }
            }
            
            searchBtn.addEventListener('click', performSearch);
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
            
            // На мобилке закрываем меню после поиска
            if (isMobile) {
                searchBtn.addEventListener('click', function() {
                    closeMenu();
                });
            }
        }
        
        // Закрытие меню при изменении размера окна
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Если перешли с мобилки на десктоп - закрываем меню
                if (window.innerWidth > 1024) {
                    closeMenu();
                }
            }, 100);
        });
        
        // Обновление кнопок сравнения
        updateCompareButtons();
    });
    
    // Функция обновления кнопок сравнения
    function updateCompareButtons() {
        const compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
        
        document.querySelectorAll('.btn-secondary[data-product-id]').forEach(button => {
            const productId = button.getAttribute('data-product-id');
            const exists = compareItems.some(item => item.id == productId);
            
            if (exists) {
                button.textContent = '✓ В сравнении';
                button.classList.add('btn-success');
                button.classList.remove('btn-secondary');
            }
        });
    }

    // Bitrix24 виджет
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