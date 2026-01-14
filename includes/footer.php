<?php
require_once 'content_parser.php';

$contact_phone = getContentSection('contact_phone');
$contact_email = getContentSection('contact_email');
$contact_address = getContentSection('contact_address');
$working_hours = getContentSection('working_hours');
$copyright = getContentSection('copyright');
?>
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
                        <?php if ($contact_phone): ?>
                        <li>📞 <?php echo nl2br(htmlspecialchars($contact_phone)); ?></li>
                        <?php endif; ?>
                        
                        <?php if ($contact_email): ?>
                        <li>✉️ <?php echo htmlspecialchars($contact_email); ?></li>
                        <?php endif; ?>
                        
                        <?php if ($contact_address): ?>
                        <li>📍 <?php echo htmlspecialchars($contact_address); ?></li>
                        <?php endif; ?>
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
                        <?php if ($working_hours): ?>
                        <li><?php echo nl2br(htmlspecialchars($working_hours)); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <?php if ($copyright): ?>
                <p><?php echo htmlspecialchars($copyright); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    