<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cs/style.css">
    <link rel="stylesheet" href="cs/srav.css">
    <script src="j/srav.js" defer></script>
    <title>Сравнительная таблица дозаторов Dosatron</title>       
</head>
<body>
    
    <header class="header">
           <?php include 'includes/header.php'; ?>
    <div class="catalog-header">
        <h1>Сравнительная таблица дозаторов Dosatron</h1>
        <p>Основные характеристики различных моделей дозаторов. Варианты комплектации и дополнительные опции, которые адаптируют запасы под определенные требования.</p>
    </div>
    
    <div class="catalog-container">
        <aside class="catalog-sidebar">
            <h2 class="sidebar-title">Категории дозаторов</h2>
            
            <div class="filter-list">
                <a href="#adjustable" class="filter-item active">
                    Модели с регулируемой дозировкой
                    <span class="filter-count">25</span>
                </a>
                <a href="#fixed" class="filter-item">
                    Модели с фиксированной дозировкой
                    <span class="filter-count">4</span>
                </a>
                <a href="#discontinued" class="filter-item">
                    Снятые с производства модели
                    <span class="filter-count">17</span>
                </a>
            </div>
            
            <div class="consultation-box">
                <h3>Нужна консультация?</h3>
                <p>Наши специалисты помогут подобрать оптимальную модель для ваших задач</p>
                <a href="#consult-form" class="btn btn-primary">Получить консультацию</a>
            </div>
        </aside>
        
        <main class="catalog-content">
            <div class="catalog-info">
                <div class="catalog-count">
                    Всего моделей: <strong>46</strong>
                </div>
                <div class="current-filter">Активная категория: Регулируемые</div>
            </div>
            
            <h2 id="adjustable" class="section-title">Модели с регулируемой дозировкой</h2>
            <table class="dosatron-table">
                <thead>
                    <tr>
                        <th>Модель</th>
                        <th>Производительность</th>
                        <th>Пропорции дозирования</th>
                        <th>Рабочее давление воды</th>
                        <th>Соединительные патрубки</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="model-name">D07RE1A2AF</span></td>
                        <td>5 - 700 л/ч</td>
                        <td>0,15 - 1,25 % (1:666 - 1:80)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D07RE4S</span></td>
                        <td>5 - 700 л/ч</td>
                        <td>0,8 - 5,5 % (1:121 - 1:16)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DIAAAC (MARES) VE</span></td>
                        <td>5 - 2500 л/ч</td>
                        <td>0,1 - 4,0 % (1:100 - 1:10)</td>
                        <td>0,1 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D26RE1500 VE / AE</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,07 - 0,2 % (1:1500 - 1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D26RE09 AO</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,1 - 0,9 % (1:1000 - 1:12)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D26RE2 VE / AF / AO</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,2 - 2 % (1:500 - 1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D26RE5 VE / AF</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>1 - 5 % (1:100 - 1:20)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D26RE10 VE / AE / IE</span></td>
                        <td>10 - 2000 л/ч</td>
                        <td>3 - 10 % (1:33 - 1:10)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR53000 AF / NF / GL / WL</span></td>
                        <td>10 - 3000 л/ч</td>
                        <td>0,03 - 0,3 % (1:3000 - 1:33)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR52 VE / AF / GL / BP</span></td>
                        <td>10 - 3000 л/ч</td>
                        <td>0,02 - 2 % (1:500 - 1:30)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR53 VE / AF / GL</span></td>
                        <td>10 - 3000 л/ч</td>
                        <td>0,05 - 5 % (1:200 - 1:20)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR51VG VE / SF / GF / GL</span></td>
                        <td>10 - 3000 л/ч</td>
                        <td>1 - 10 % (1:100 - 1:10)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR52B BW</span></td>
                        <td>10 - 4000 л/ч</td>
                        <td>5 - 20 % (1:120 - 1:14)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D45R53000 VE</span></td>
                        <td>10 - 4500 л/ч</td>
                        <td>0,03 - 0,1 % (1:3000 - 1:1000)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D45RE LS VE</span></td>
                        <td>10 - 4500 л/ч</td>
                        <td>0,02 - 1,5 % (1:350 - 1:56)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D45RE3 VE / AE</span></td>
                        <td>10 - 4500 л/ч</td>
                        <td>0,5 - 3 % (1:200 - 1:33)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D45RE3 VE</span></td>
                        <td>10 - 4500 л/ч</td>
                        <td>3 - 8 % (1:33 - 1:12)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR1AX00 IE</span></td>
                        <td>500 - 6000 л/ч</td>
                        <td>0,06 - 0,12 % (1:3000 - 1:60)</td>
                        <td>0,16 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR52YE / AF / GL</span></td>
                        <td>500 - 8000 л/ч</td>
                        <td>0,02 - 2 % (1:500 - 1:50)</td>
                        <td>0,16 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR55 VE / AF</span></td>
                        <td>500 - 8000 л/ч</td>
                        <td>1 - 5 % (1:100 - 1:20)</td>
                        <td>0,16 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR12 VE / AL</span></td>
                        <td>500 - 9000 л/ч</td>
                        <td>0,02 - 2 % (1:500 - 1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR15 VE</span></td>
                        <td>500 - 9000 л/ч</td>
                        <td>1 - 5 % (1:100 - 1:20)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR5 VE / GL / WL</span></td>
                        <td>1000 - 2000 л/ч</td>
                        <td>0,02 - 2 % (1:500 - 1:50)</td>
                        <td>0,02 - 1,0 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR31 GJ</span></td>
                        <td>8000 - 3000 л/ч</td>
                        <td>0,01 - 0,2 % (1:5000 - 1:50)</td>
                        <td>0,02 - 1,0 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">DDR61 I</span></td>
                        <td>8000 - 3000 л/ч</td>
                        <td>0,1 - 1 % (1:1000 - 1:10)</td>
                        <td>0,5 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                </tbody>
            </table>
            
            <h2 id="fixed" class="section-title">Модели с фиксированной дозировкой</h2>
            <table class="dosatron-table">
                <thead>
                    <tr>
                        <th>Модель</th>
                        <th>Производительность</th>
                        <th>Пропорции дозирования</th>
                        <th>Рабочее давление воды</th>
                        <th>Соединительные патрубки</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="model-name">D25F02</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,2 % (1:500)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D25F</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,8 % (1:120)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D25F1</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>1 % (1:100)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr>
                        <td><span class="model-name">D25F2</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>2 % (1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                </tbody>
            </table>
            
            <h2 id="discontinued" class="section-title">Снятые с производства модели</h2>
            <table class="dosatron-table">
                <thead>
                    <tr>
                        <th>Модель</th>
                        <th>Производительность</th>
                        <th>Пропорции дозирования</th>
                        <th>Рабочее давление воды</th>
                        <th>Соединительные патрубки</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="discontinued">
                        <td><span class="model-name">D100RD128R</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>0,5,0,8,1 % (1:200, 1:126, 1:140)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D15F02</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>0,2 % (1:500)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D15F2</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>2 % (1:50)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D15F3</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>3 % (1:33)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D200REFREE</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>0,2 - 2 % (1:500 - 1:50)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D310REFREE</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>3 - 10 % (1:200 - 1:10)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D400REFREE</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>0,5 - 4 % (1:200 - 1:25)</td>
                        <td>0,5 - 5 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D25RE4</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,6 - 4 % (1:200 - 1:25)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D11500</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,07 - 0,2 % (1:1500 - 1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D105</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,2 - 1,5 % (1:500 - 1:20)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D116</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,2 - 1,6 % (1:200 - 1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D02</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>0,5 - 2 % (1:200 - 1:50)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D1150</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>1 - 5 % (1:100 - 1:20)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">D1110</span></td>
                        <td>10 - 2500 л/ч</td>
                        <td>1 - 10 % (1:100 - 1:10)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                    <tr class="discontinued">
                        <td><span class="model-name">DIS20</span></td>
                        <td>10 - 1500 л/ч</td>
                        <td>5 - 20 % (1:20 - 1:5)</td>
                        <td>0,3 - 6 бар</td>
                        <td>YF NPT8SP внеш. резьба</td>
                    </tr>
                </tbody>
            </table>
            
            <div id="consult-form" style="background: hsl(220 35% 8%); border-radius: 12px; padding: 40px; margin-top: 40px; border: 1px solid hsl(200 30% 18%); text-align: center;">
                <h2 style="color: white; margin-bottom: 20px; font-size: 2rem;">Нужна подробная консультация?</h2>
                <p style="color: hsl(200 20% 70%); font-size: 1.2rem; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Свяжитесь с нашими специалистами через форму обратной связи для подбора оптимальной модели дозатора
                </p>
                <a href="contacts.php#contactFormSplit" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">
                    Написать специалисту
                </a>
            </div>
            
        </main>
    </div>
    <?php include 'includes/footer.php';?>
</body>
</html>