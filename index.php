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
    die("Ошибка подключения: " . $mysqli->connect_error);
}

// Устанавливаем кодировку
$mysqli->set_charset("utf8mb4");


function findFile($dbPath) {
    if (empty($dbPath) || $dbPath == '-' || $dbPath == 'NULL') {
        return null;
    }
    
    // Берем только имя файла
    $fileName = basename($dbPath);
    
    // Папки для поиска (можно добавлять новые)
    $searchFolders = [
        '',                      // Корень проекта
        'images/',              // Папка images
        'img/',                 // Папка img
        'products/',            // Папка products
        'uploads/',             // Папка uploads
        'images/products/',     // Вложенная images/products
        'img/products/',        // Вложенная img/products
        'assets/images/',       // Вложенная assets/images
        'media/products/',      // Вложенная media/products
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

//Получение товаров из базы
$sql = "SELECT * FROM medicator LIMIT 6";
$result = $mysqli->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ищем изображение для каждого товара
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
        /* Дополнительные стили для улучшенного интерфейса */
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
    </style>
</head>
<body>
    <!-- Шапка сайта -->
    <header class="header">
        <div class="container">
            <div class="header__inner">
                <!-- Логотип -->
                <a href="index.php" class="logo">
                    <div class="logo__img">
                        <img src="logo.jpg" alt="7company" width="40" height="40">
                    </div>
                    <span class="logo__text">7 company</span>
                </a>

                <!-- Навигация -->
                <nav class="nav">
                    <ul class="nav__list">
                        <li class="nav__item">
                            <a href="index.php" class="nav__link nav__link--active">Главная</a>
                        </li>
                        <li class="nav__item">
                            <a href="catalog.php" class="nav__link">Каталог</a>
                        </li>
                        <li class="nav__item">
                            <a href="contacts.php" class="nav__link">Контакты</a>
                        </li>
                        <li>
                            <a href="compare.php" class="nav__link">Сравнение</a>
                        </li>
                        <li>
                            <a href="contacts.php" class="btn btn-primary">Заказать</a>
                        </li>
                        <li>
                            <div class="sidebar-search">
                                <div class="search-box">
                                    <input type="text" id="searchInput" placeholder="Поиск по названию...">
                                    <button id="searchBtn">🔍</button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="main">
        <!-- Слайдер -->
        <section class="hero">
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <!-- Слайд 1 -->
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

        <!-- О компании -->
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

        <!-- Карточки товаров ИЗ БАЗЫ ДАННЫХ -->
        <section class="products">
            <div class="container">
                <h2 class="section-title">Популярные товары</h2>
                <div class="products__grid">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-card__image">
                                <?php if ($product['img_found']): ?>
                                    <!-- Если нашли изображение через findFile() -->
                                    <img src="<?php echo htmlspecialchars($product['img_found']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                <?php elseif (!empty($product['img']) && $product['img'] != '-'): ?>
                                    <!-- Старый вариант (для обратной совместимости) -->
                                    <img src="images/products/<?php echo htmlspecialchars($product['img']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <!-- Заглушка если нет изображения -->
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
                        <!-- Если товаров нет в БД, показываем заглушки -->
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

        <!-- Отзывы -->
        <section class="reviews">
            <div class="container">
                <h2 class="section-title">Отзывы наших клиентов</h2>
                <div class="reviews__slider">
                    <div class="swiper reviews-swiper">
                        <div class="swiper-wrapper">
                            <!-- Отзыв 1 -->
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
                            
                            <!-- Отзыв 2 -->
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
                            
                            <!-- Отзыв 3 -->
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
        // Обновленный JavaScript с улучшенной обратной связью
        document.querySelectorAll('.btn-secondary[data-product-id]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                // Получаем список для сравнения из localStorage
                let compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
                
                // Проверяем, нет ли уже этого товара
                const exists = compareItems.some(item => item.id == productId);
                if (!exists) {
                    // Добавляем товар
                    compareItems.push({
                        id: productId,
                        name: productName,
                        date: new Date().toISOString()
                    });
                    localStorage.setItem('compareItems', JSON.stringify(compareItems));
                    
                    // Визуальная обратная связь
                    this.textContent = '✓ В сравнении';
                    this.classList.add('btn-success');
                    this.classList.remove('btn-secondary');
                    
                    // Показываем уведомление
                    showNotification(`Товар "${productName}" добавлен в сравнение!`);
                } else {
                    // Удаляем товар из сравнения
                    compareItems = compareItems.filter(item => item.id != productId);
                    localStorage.setItem('compareItems', JSON.stringify(compareItems));
                    
                    // Визуальная обратная связь
                    this.textContent = 'В сравнение';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    
                    showNotification(`Товар "${productName}" удален из сравнения!`);
                }
            });
        });
        
        // Функция для показа уведомлений
        function showNotification(message) {
            // Создаем элемент уведомления
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Удаляем через 3 секунды
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        

        document.addEventListener('DOMContentLoaded', function() {

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
            

            updateCompareButtons();
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
                }
            });
        }
   
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