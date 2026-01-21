<?php
require_once 'content_parser.php';

$logo__text = getContentSection('head_name');
$current_search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
?>
<header class="header">
    <div class="container">
        <div class="header__inner">
            <!-- Бургер-кнопка -->
            <button class="burger-btn" id="burgerBtn" aria-label="Открыть меню" aria-expanded="false" aria-controls="mainNav">
                <span class="burger-btn__line"></span>
                <span class="burger-btn__line"></span>
                <span class="burger-btn__line"></span>
            </button>

            <!-- ЛОГО -->
            <a href="/" class="logo"> <!-- или <a href="/index" class="logo"> -->
                <span class="logo__text"><?php echo nl2br($logo__text); ?></span>
            </a>

            <!-- Затемнение фона -->
            <div class="nav-overlay" id="navOverlay"></div>

            <!-- НАВИГАЦИЯ -->
            <nav class="nav" id="mainNav" aria-label="Основная навигация">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="/" class="nav__link">Главная</a> <!-- или /index -->
                    </li>
                    <li class="nav__item has-dropdown">
                        <a href="/catalog" class="catalog-link" id="catalogLink">
                            Каталог
                        </a>
                        <ul class="dropdown-menu" id="catalogDropdown">
                            <!-- ВСЕ ссылки с / в начале! -->
                            <li><a href="/catalog">Все модели</a></li>
                            <li><a href="/catalog/DIA">DIA</a></li>
                            <li><a href="/catalog/D07">D07</a></li>
                            <li><a href="/catalog/D25">D25</a></li>
                            <li><a href="/catalog/D3">D3</a></li>
                        </ul>
                    </li>
                    <li class="nav__item">
                        <a href="/contacts" class="nav__link">Контакты</a>
                    </li>
                    <li class="nav__item">
                        <a href="/compare" class="nav__link">Сравнение</a>
                    </li>
                    <li class="nav__item nav__item--search">
                        <div class="sidebar-search">
                            <div class="search-box">
                                <!-- ФОРМА ТОЖЕ С / -->
                                <form action="/catalog" method="get" class="search-form">
                                    <input type="text" 
                                           name="search" 
                                           id="globalSearchInput" 
                                           placeholder="Поиск по каталогу..."
                                           value="<?php echo $current_search; ?>">
                                    <button type="submit">🔍</button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- КНОПКА ТОЖЕ С / -->
            <a href="/contacts#contactFormSplit" class="btn btn-primary header__order-btn">Заказать</a>
        </div>
    </div>
</header>