<?php
require_once 'content_parser.php';

$logo__text = getContentSection('head_name');
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

            <a href="index.php" class="logo">
                <span class="logo__text"><?php echo nl2br($logo__text); ?></span>
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