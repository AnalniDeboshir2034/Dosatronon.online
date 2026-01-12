<?php
$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

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

// Функция поиска файла (упрощенная)
function findFileSimple($img) {
    if (empty($img) || $img == '-' || $img == 'NULL') {
        return null;
    }
    
    $fileName = basename($img);
    $paths = [
        'images/' . $fileName,
        'img/' . $fileName,
        'products/' . $fileName,
        'uploads/' . $fileName,
        $fileName
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>7 company - Каталог медикаторов</title>
    <link rel="stylesheet" href="cs/style.css">
    <script src="j/script.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">


</head>
<body>
    <style>
/* === СЕКЦИЯ ПОПУЛЯРНЫЕ ТОВАРЫ === */

/* Grid для десктопа */
.products-grid-desktop {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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

/* Контейнер свайпера */
.products-swiper {
    padding: 10px;
}

/* === КАРТОЧКА ТОВАРА (точь-в-точь как в каталоге) === */
.product-card {
    background: var(--card);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
    transition: transform 0.3s ease, border-color 0.3s ease;
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
}

/* === ИЗОБРАЖЕНИЕ ТОВАРА === */
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
    width: auto;
    height: auto;
    object-fit: contain;
    padding: 10px;
    transition: transform 0.5s ease;
}

.product-card:hover .product-card__image img {
    transform: scale(1.05);
}

/* Плейсхолдер для отсутствующего изображения */
.image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--muted-foreground);
}

.placeholder-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
    opacity: 0.7;
}

.image-placeholder p {
    color: var(--muted-foreground);
    font-size: 14px;
    margin: 0;
}

/* === ТЕКСТ КАРТОЧКИ === */
.product-card__content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-card__title {
    color: var(--foreground);
    font-size: 1.3rem;
    margin-bottom: 12px;
    line-height: 1.4;
    text-align: center;
    height: 42px;
    overflow: hidden;
}

.product-card__title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.product-card__title a:hover {
    color: var(--primary);
}

/* Описание/спецификации */
.product-card__desc {
    color: var(--muted-foreground);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 20px;
    text-align: center;
    flex: 1;
}

.spec-item {
    display: block;
    margin-bottom: 5px;
    line-height: 1.4;
    color: var(--muted-foreground);
}

/* Цена */
.product-card__price {
    font-weight: 600;
    color: var(--foreground);
    margin-bottom: 20px;
    font-size: 1rem;
    text-align: center;
}

/* === КНОПКИ (точные цвета как в каталоге) === */
.product-card__actions {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

/* Базовая стилизация кнопок как в каталоге */
.product-card__actions .btn {
    flex: 1;
    padding: 10px;
    font-size: 0.9rem;
    text-align: center;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
}

/* Кнопка "В сравнение" - точь-в-точь как в каталоге */
.product-card__actions .btn-secondary {
    background: transparent;
    border: 2px solid var(--border);
    color: var(--muted-foreground);
}

.product-card__actions .btn-secondary:hover {
    border-color: var(--primary);
    color: var(--primary);
}


.product-card__actions .btn-primary {
    background: var(--primary);
    color: var(--primary-foreground);
}

.product-card__actions .btn-primary:hover {
    background: hsl(195 100% 40%);
}

/* Кнопка когда товар в сравнении */
.product-card__actions .btn-success {
    background: #4CAF50;
    color: white;
    border: none;
}

.product-card__actions .btn-success:hover {
    background: #45a049;
}

/* === КНОПКИ СЛАЙДЕРА === */
.products-swiper .swiper-button-next,
.products-swiper .swiper-button-prev {
    width: 40px;
    height: 40px;
    background: var(--primary); /* Голубой как кнопка "Подробнее" */
    border-radius: 50%;
    color: var(--primary-foreground); /* Темный текст */
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

/* === КНОПКА "ВЕСЬ КАТАЛОГ" === */
.text-center .btn-large {
    display: inline-block;
    padding: 12px 30px;
    background: var(--primary); /* Такой же голубой как "Подробнее" */
    color: var(--primary-foreground);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 1rem;
}

.text-center .btn-large:hover {
    background: hsl(195 100% 40%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 188, 235, 0.3);
}

/* === АДАПТИВНОСТЬ === */

/* На мобилке показываем свайпер, скрываем grid */
@media (max-width: 991px) {
    .products-grid-desktop {
        display: none !important;
    }
    
    .products-swiper-mobile {
        display: block;
    }
    
    .product-card__image {
        height: 200px;
    }
    
    .product-card__content {
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .products-swiper-mobile {
        padding: 0 50px 40px;
    }
    
    .product-card__image {
        height: 180px;
    }
    
    .product-card__title {
        font-size: 1.2rem;
        height: 38px;
    }
}

@media (max-width: 576px) {
    .products-swiper-mobile {
        padding: 0 40px 40px;
    }
    
    .product-card__image {
        height: 160px;
    }
    
    .product-card__actions {
        flex-direction: column;
    }
    
    /* На совсем маленьких скрываем стрелки */
    .products-swiper .swiper-button-next,
    .products-swiper .swiper-button-prev {
        display: none;
    }
}

@media (max-width: 480px) {
    .products-swiper-mobile {
        padding: 0 20px 30px;
    }
    
    .product-card__image {
        height: 140px;
    }
    
    .product-card__content {
        padding: 12px;
    }
    
    .product-card__title {
        font-size: 1.1rem;
        height: 36px;
    }
    
    .product-card__actions .btn {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
}
</style>
   <header class="header">
    <div class="container">
        <div class="header__inner">
            <!-- Бургер-кнопка -->
            <button class="burger-btn" id="burgerBtn" aria-label="Открыть меню" aria-expanded="false" aria-controls="mainNav">
                <span class="burger-btn__line"></span>
                <span class="burger-btn__line"></span>
                <span class="burger-btn__line"></span>
            </button>

            <!-- Логотип -->
            <a href="index.php" class="logo">
                <div class="logo__img">
                    <img src="logo.jpg" alt="7company" width="40" height="40" loading="lazy">
                </div>
                <span class="logo__text">7 company</span>
            </a>

   

            <!-- Затемнение фона -->
            <div class="nav-overlay" id="navOverlay"></div>

            <!-- Навигация -->
            <nav class="nav" id="mainNav" aria-label="Основная навигация">
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
                     <a href="contacts.php#contactFormSplit" class="btn btn-primary header__order-btn">Заказать</a>
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
                        <img src="logo.jpg" alt="7 company" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

<section class="products">
    <div class="container">
        <h2 class="section-title">Популярные товары</h2>
        
        <!-- Grid для десктопа (показывается только на ПК) -->
        <div class="products-grid-desktop">
            <?php if (count($products) > 0): ?>
                <?php 
                // Берем только 3 товара для десктопа
                $desktopProducts = array_slice($products, 0, 3);
                ?>
                <?php foreach ($desktopProducts as $product): ?>
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
                                <span class="spec-item">📏 <?php echo htmlspecialchars($product['d_dosing']); ?></span><br>
                            <?php endif; ?>
                            <?php if (!empty($product['performance'])): ?>
                                <span class="spec-item">⚡ <?php echo htmlspecialchars($product['performance']); ?></span>
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
            <?php endif; ?>
        </div>
        
        <!-- Swiper для мобилки (скрыт на десктопе) -->
        <div class="products-swiper-mobile">
            <div class="swiper products-swiper">
                <div class="swiper-wrapper">
                    <?php if (count($products) > 0): ?>
                        <?php 
                        // Берем первые 3 товара для цикла 0-2
                        $mobileProducts = array_slice($products, 0, 3);
                        ?>
                        <?php foreach ($mobileProducts as $product): ?>
                        <div class="swiper-slide">
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
                                            <span class="spec-item">📏 <?php echo htmlspecialchars($product['d_dosing']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if (!empty($product['performance'])): ?>
                                            <span class="spec-item">⚡ <?php echo htmlspecialchars($product['performance']); ?></span>
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
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Кнопки навигации -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
        
        <div class="text-center" style="margin-top: 30px;">
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
                        <li>📞 +375 33 680 07 07
                        <br>   +375 29 883 00 07</li>
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
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script>
// Удалить defer с этого скрипта и добавить в конец body

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен');
    
    // 1. ИНИЦИАЛИЗАЦИЯ СЛАЙДЕРОВ
    

    if (document.querySelector('.products-swiper')) {
        console.log('Проверяем устройства...');
        console.log('Ширина экрана:', window.innerWidth, 'px');
        
        // Проверяем мобильное устройство
        if (window.innerWidth <= 991) {
            console.log('✅ Мобильное устройство, инициализируем свайпер товаров');
            
            const productsSwiper = new Swiper('.products-swiper', {
                loop: true,                    // Бесконечная прокрутка
                speed: 400,                    // Скорость анимации
                slidesPerView: 1,              // 1 слайд на экране
                spaceBetween: 15,              // Отступ между слайдами
                centeredSlides: true,          // Центрируем активный слайд
                grabCursor: true,              // Курсор "рука" при наведении
                allowTouchMove: true,          // Разрешаем свайп пальцем
                
                // Навигация стрелками
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                    disabledClass: 'swiper-button-disabled'
                },
                
                // События для отладки
                on: {
                    init: function() {
                        console.log('✅ Свайпер товаров инициализирован!');
                        console.log('Всего слайдов: ' + this.slides.length);
                        console.log('Зацикливание: ' + this.params.loop);
                        
                        // Принудительно показываем кнопки
                        const nextBtn = this.navigation.nextEl;
                        const prevBtn = this.navigation.prevEl;
                        if (nextBtn) {
                            nextBtn.style.display = 'flex';
                            nextBtn.style.opacity = '1';
                            console.log('Кнопка Next:', nextBtn);
                        }
                        if (prevBtn) {
                            prevBtn.style.display = 'flex';
                            prevBtn.style.opacity = '1';
                            console.log('Кнопка Prev:', prevBtn);
                        }
                    },
                    
                    slideChange: function() {
                        console.log('Текущий слайд: ' + (this.realIndex + 1) + ' из ' + this.slides.length);
                    }
                }
            });
            
            // Тестируем клики на кнопках
            const nextBtn = document.querySelector('.products-swiper .swiper-button-next');
            const prevBtn = document.querySelector('.products-swiper .swiper-button-prev');
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    console.log('Клик по кнопке NEXT');
                    e.stopPropagation();
                });
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    console.log('Клик по кнопке PREV');
                    e.stopPropagation();
                });
            }
            
            // Тестируем свайп
            const swiperEl = document.querySelector('.products-swiper');
            if (swiperEl) {
                swiperEl.addEventListener('touchstart', function() {
                    console.log('Touch started (свайп начат)');
                });
                
                swiperEl.addEventListener('touchend', function() {
                    console.log('Touch ended (свайп закончен)');
                });
                
                // Для десктопа (если тестируешь мышкой)
                swiperEl.addEventListener('mousedown', function() {
                    console.log('Mouse down (начало свайпа мышкой)');
                });
                
                swiperEl.addEventListener('mouseup', function() {
                    console.log('Mouse up (конец свайпа мышкой)');
                });
            }
        } else {
            console.log('🖥️ Десктопное устройство, свайпер не нужен');
        }
    }
    
    // Слайдер отзывов (работает всегда)
    if (document.querySelector('.reviews-swiper')) {
        console.log('Инициализируем слайдер отзывов...');
        
        new Swiper('.reviews-swiper', {
            loop: true,
            speed: 600,
            autoplay: { 
                delay: 5000,
                disableOnInteraction: false 
            },
            slidesPerView: 1,
            spaceBetween: 20,
            grabCursor: true,
            breakpoints: {
                768: { 
                    slidesPerView: 2,
                    spaceBetween: 20 
                },
                992: { 
                    slidesPerView: 3,
                    spaceBetween: 20 
                }
            },
            on: {
                init: function() {
                    console.log('✅ Слайдер отзывов инициализирован');
                }
            }
        });
    }
    
    // 2. ФУНКЦИОНАЛ СРАВНЕНИЯ ТОВАРОВ
    
    const compareButtons = document.querySelectorAll('.btn-secondary[data-product-id]');
    compareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
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
    
    // Функция обновления кнопок сравнения
    function updateCompareButtons() {
        const compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
        compareButtons.forEach(button => {
            const productId = button.getAttribute('data-product-id');
            if (compareItems.some(item => item.id == productId)) {
                button.textContent = '✓ В сравнении';
                button.classList.add('btn-success');
                button.classList.remove('btn-secondary');
            }
        });
    }
    
    updateCompareButtons();
    
    // 3. ВЫПАДАЮЩЕЕ МЕНЮ КАТАЛОГА НА МОБИЛКЕ
    
    // const catalogLink = document.getElementById('catalogLink');
    // const catalogDropdown = document.getElementById('catalogDropdown');
    
    // if (catalogLink && catalogDropdown && window.innerWidth <= 992) {
    //     catalogLink.addEventListener('click', function(e) {
    //         e.preventDefault();
    //         e.stopPropagation();
    //         this.classList.toggle('active');
    //         catalogDropdown.classList.toggle('active');
    //     });
        
    //     // Закрытие при клике вне меню
    //     document.addEventListener('click', function(e) {
    //         if (!catalogLink.contains(e.target) && !catalogDropdown.contains(e.target)) {
    //             catalogLink.classList.remove('active');
    //             catalogDropdown.classList.remove('active');
    //         }
    //     });
    // }
    
    // 4. ПОИСК ПО ТОВАРАМ
    
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
    }
    
    // 5. ФУНКЦИЯ УВЕДОМЛЕНИЙ
    
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        // Стили для уведомления
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
            max-width: 300px;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    // 6. БУРГЕР МЕНЮ (если есть)
    // const burgerBtn = document.getElementById('burgerBtn');
    // const navOverlay = document.getElementById('navOverlay');
    // const mainNav = document.getElementById('mainNav');
    
    // if (burgerBtn && mainNav) {
    //     burgerBtn.addEventListener('click', function() {
    //         this.classList.toggle('active');
    //         mainNav.classList.toggle('active');
    //         if (navOverlay) navOverlay.classList.toggle('active');
    //         document.body.classList.toggle('menu-open');
            
    //         // Обновляем aria атрибуты
    //         const isExpanded = this.classList.contains('active');
    //         this.setAttribute('aria-expanded', isExpanded);
    //     });
        
    //     if (navOverlay) {
    //         navOverlay.addEventListener('click', function() {
    //             burgerBtn.classList.remove('active');
    //             mainNav.classList.remove('active');
    //             this.classList.remove('active');
    //             document.body.classList.remove('menu-open');
    //             burgerBtn.setAttribute('aria-expanded', 'false');
    //         });
    //     }
    // }
    
    // 7. ОБРАБОТЧИК ИЗМЕНЕНИЯ РАЗМЕРА ОКНА
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            console.log('Размер окна изменен:', window.innerWidth, 'px');
            

        }, 250);
    });
});

// 8. BITRIX24 ВИДЖЕТ (не трогаем)
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