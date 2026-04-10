<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/forslug.php';
include 'includes/content_parser.php';

$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

$mysqli = new mysqli($host, $user, $pass, $db_name);

if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

function findFile($dbPath) {
    if (empty($dbPath) || $dbPath == '-' || $dbPath == 'NULL') {
        return null;
    }
    
    $fileName = basename($dbPath);
    
    $searchFolders = [
        '', 'images/', 'img/', 'products/', 'uploads/', 
        'diagrams/', 'pdfs/', 'images/products/', 'img/products/'
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

$product_id = null;
$product = null;
$slug = null;

if (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = trim($_GET['slug']);
    
    $sql = "SELECT *, slug FROM medicator WHERE slug = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
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
                
                $product['img_found'] = findFile($product['img'] ?? '');
                $product['diag_found'] = findFile($product['diag'] ?? '');
                $product['pdf_found'] = findFile($product['pdf'] ?? '');
                
                $today = date('Y-m-d');
                $check_views = $mysqli->prepare("SELECT id, view_count FROM product_views WHERE product_id = ? AND view_date = ?");
                $check_views->bind_param("is", $product['id'], $today);
                $check_views->execute();
                $check_views->store_result();
                
                if ($check_views->num_rows > 0) {
                    $check_views->bind_result($view_id, $view_count);
                    $check_views->fetch();
                    $update_views = $mysqli->prepare("UPDATE product_views SET view_count = view_count + 1 WHERE id = ?");
                    $update_views->bind_param("i", $view_id);
                    $update_views->execute();
                    $update_views->close();
                } else {
                    $insert_views = $mysqli->prepare("INSERT INTO product_views (product_id, product_name, view_date, view_count) VALUES (?, ?, ?, 1)");
                    $insert_views->bind_param("iss", $product['id'], $product['name'], $today);
                    $insert_views->execute();
                    $insert_views->close();
                }
                $check_views->close();
            }
        }
        
        $stmt->close();
    }
}

if (!$product && isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    $sql = "SELECT *, slug FROM medicator WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
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
                
                $product['img_found'] = findFile($product['img'] ?? '');
                $product['diag_found'] = findFile($product['diag'] ?? '');
                $product['pdf_found'] = findFile($product['pdf'] ?? '');
                
                $today = date('Y-m-d');
                $check_views = $mysqli->prepare("SELECT id, view_count FROM product_views WHERE product_id = ? AND view_date = ?");
                $check_views->bind_param("is", $product['id'], $today);
                $check_views->execute();
                $check_views->store_result();
                
                if ($check_views->num_rows > 0) {
                    $check_views->bind_result($view_id, $view_count);
                    $check_views->fetch();
                    $update_views = $mysqli->prepare("UPDATE product_views SET view_count = view_count + 1 WHERE id = ?");
                    $update_views->bind_param("i", $view_id);
                    $update_views->execute();
                    $update_views->close();
                } else {
                    $insert_views = $mysqli->prepare("INSERT INTO product_views (product_id, product_name, view_date, view_count) VALUES (?, ?, ?, 1)");
                    $insert_views->bind_param("iss", $product['id'], $product['name'], $today);
                    $insert_views->execute();
                    $insert_views->close();
                }
                $check_views->close();
                
                if (!empty($product['slug'])) {
                    header("Location: /product/" . $product['slug'], true, 301);
                    exit;
                }
            }
        }
        
        $stmt->close();
    }
}

if (!$product && $slug && is_numeric($slug)) {
    $product_id = intval($slug);
    
    $sql = "SELECT slug FROM medicator WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($found_slug);
            $stmt->fetch();
            
            if (!empty($found_slug)) {
                header("Location: /product/" . $found_slug, true, 301);
                exit;
            }
        }
        
        $stmt->close();
    }
}

if (!$product) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

$similar_products = array();
$current_product_id = $product['id'];

$similar_sql = "SELECT *, slug FROM medicator WHERE id != ? ORDER BY RAND() LIMIT 6";
$similar_stmt = $mysqli->prepare($similar_sql);

if ($similar_stmt) {
    $similar_stmt->bind_param("i", $current_product_id);
    $similar_stmt->execute();
    $similar_stmt->store_result();
    
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
        
        $similar_product['img_found'] = findFile($similar_product['img'] ?? '');
        $similar_products[] = $similar_product;
    }
    
    $similar_stmt->close();
}

function getContent($section) {
    require_once 'includes/content_parser.php';
    return getContentSection($section, '');
}

$meta_desc = getContent('meta_description');
$meta_keys = getContent('meta_keywords');
$favicon = getContent('favicon');
$page_title = getContent('header_title');
$favicon = getContent('favicon');

$html_title = $product ? htmlspecialchars($product['name']) . ' | ' . $page_title : 'Товар не найден | ' . $page_title;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-P2N10VB842"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-P2N10VB842');
</script>
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=108454352', 'ym');

    ym(108454352, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/108454352" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <title><?php echo $html_title; ?></title>
    
    <link rel="stylesheet" href="/cs/style.css">
    <link rel="stylesheet" href="/cs/product.css">
    <script src="/j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js" defer></script>
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>
    </div>

    <main class="main">
        <?php if (!$product): ?>
            <section class="not-found" style="padding: 100px 0; text-align: center;">
                <div class="container">
                    <h1 style="color: var(--foreground); margin-bottom: 20px;">Товар не найден</h1>
                    <p style="color: var(--muted-foreground); margin-bottom: 40px;">
                        Запрашиваемый товар не существует или был удален.
                    </p>
                    <div>
                        <a href="/catalog" class="btn btn-primary">
                            Вернуться в каталог
                        </a>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <nav style="background: var(--card); padding: 15px 0; border-bottom: 1px solid var(--border);">
                <div class="container">
                    <a href="/" style="color: var(--muted-foreground); text-decoration: none;">Главная</a>
                    <span style="color: var(--muted-foreground); margin: 0 10px;">›</span>
                    <a href="/catalog" style="color: var(--muted-foreground); text-decoration: none;">Каталог</a>
                    <span style="color: var(--muted-foreground); margin: 0 10px;">›</span>
                    <span style="color: var(--foreground); font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
            </nav>

            <section class="product-page">
                <div class="product-layout">
                    <div class="product-gallery">
                        <div class="product-image-container">
                            <?php if ($product['img_found']): ?>
                                <img src="/<?php echo htmlspecialchars($product['img_found']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <span class="placeholder-icon">🏭</span>
                                    <p>Изображение отсутствует</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actions-bar">
                            <button class="btn btn-compare" data-product-id="<?php echo $product['id']; ?>">
                                <span style="margin-right: 8px;">⚖️</span>
                                Добавить к сравнению
                            </button>
                            <a href="/contacts#contactFormSplit" class="btn btn-request">
                                <span style="margin-right: 8px;">📧</span>
                                Заказать
                            </a>
                            <?php if ($product['pdf_found']): ?>
                                <a href="/<?php echo htmlspecialchars($product['pdf_found']); ?>" 
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
                                $opis = preg_replace('/\$(.*?)\$/', '<h2>$1</h2>', $opis);
                                $opis = preg_replace('/\%(.*?)\%/', '<h4>$1</h4>', $opis);
                                $opis = preg_replace('/\.\.(.*?)\.\./', '<b>$1</b>', $opis);
                                
                                echo $opis;
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="product-subtitle">
                            Профессиональный дозатор для систем полива и внесения удобрений
                        </p>
                        
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
                        
                        <?php if (!empty($product['diag']) && $product['diag'] != '-'): ?>
                        <div class="diagram-container">
                            <h2 style="color: var(--foreground); margin-bottom: 25px; font-size: 1.8rem;">Техническая схема</h2>
                            
                            <div class="diagram-image-container">
                                <?php if ($product['diag_found']): ?>
                                    <img src="/<?php echo htmlspecialchars($product['diag_found']); ?>" 
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
            
            <?php if (!empty($similar_products)): ?>
            <section class="similar-products">
                <div class="container">
                    <h2 style="text-align: center; color: var(--foreground); margin-bottom: 50px; font-size: 2rem;">Похожие товары</h2>
                    
                    <div class="products-grid-desktop">
                        <?php foreach ($similar_products as $similar): ?>
                        <div class="product-card">
                            <div class="product-card__image">
                                <?php if ($similar['img_found']): ?>
                                    <img src="/<?php echo htmlspecialchars($similar['img_found']); ?>" 
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
                                   <a href="<?php echo getProductUrl($similar); ?>" class="product-link">
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
                                    <a href="<?php echo getProductUrl($similar); ?>" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="products-swiper-mobile">
                        <div class="swiper products-swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($similar_products as $similar): ?>
                                <div class="swiper-slide">
                                    <div class="product-card">
                                        <div class="product-card__image">
                                            <?php if ($similar['img_found']): ?>
                                                <img src="/<?php echo htmlspecialchars($similar['img_found']); ?>" 
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
                                               <a href="<?php echo getProductUrl($similar); ?>" class="product-link">
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
                                                <a href="<?php echo getProductUrl($similar); ?>" class="btn btn-primary">Подробнее</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                    
                    <div class="text-center" >
                        <a href="/catalog" class="btn btn-large">Весь каталог →</a>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        const COMPARE_STORAGE_KEY = 'compareItems';

        function getCompareItems() {
            try {
                const raw = JSON.parse(localStorage.getItem(COMPARE_STORAGE_KEY)) || [];
                const unique = new Map();

                raw.forEach(item => {
                    const id = Number.parseInt(item?.id, 10);
                    if (!Number.isNaN(id) && id > 0 && !unique.has(id)) {
                        unique.set(id, {
                            id,
                            name: item?.name || `Товар ${id}`,
                            date: item?.date || null
                        });
                    }
                });

                return Array.from(unique.values());
            } catch (error) {
                return [];
            }
        }

        function setCompareItems(items) {
            if (!items.length) {
                localStorage.removeItem(COMPARE_STORAGE_KEY);
                return;
            }
            localStorage.setItem(COMPARE_STORAGE_KEY, JSON.stringify(items));
        }

        document.querySelectorAll('[data-product-id]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = Number.parseInt(this.getAttribute('data-product-id'), 10);
                let productName = this.getAttribute('data-product-name');

                if (Number.isNaN(productId) || productId <= 0) return;
                
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
                    let compareItems = getCompareItems();
                    
                    const exists = compareItems.some(item => item.id === productId);
                    if (!exists) {
                        compareItems.push({
                            id: productId,
                            name: productName,
                            date: new Date().toISOString()
                        });
                        setCompareItems(compareItems);
                        
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
        
        const compareBtn = document.querySelector('.btn-compare');
        if (compareBtn) {
            compareBtn.addEventListener('click', function() {
                const productId = Number.parseInt(this.getAttribute('data-product-id'), 10);
                const productName = document.querySelector('.product-title').textContent.trim();

                if (Number.isNaN(productId) || productId <= 0) return;

                let compareItems = getCompareItems();
                
                const exists = compareItems.some(item => item.id === productId);
                if (!exists) {
                    compareItems.push({
                        id: productId,
                        name: productName,
                        date: new Date().toISOString()
                    });
                    setCompareItems(compareItems);
                    
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
        
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 991) {
                if (window.productsSwiper) {
                    window.productsSwiper.destroy();
                }
                
                window.productsSwiper = new Swiper('.products-swiper', {
                    loop: true,
                    speed: 400,
                    slidesPerView: 1,
                    spaceBetween: 15,
                    centeredSlides: true,
                    grabCursor: true,
                    
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    
                    breakpoints: {
                        576: {
                            slidesPerView: 1.2,
                        },
                        768: {
                            slidesPerView: 1.5,
                        }
                    },
                    
                    on: {
                        init: function() {
                            if (this.navigation.nextEl) this.navigation.nextEl.style.display = 'flex';
                            if (this.navigation.prevEl) this.navigation.prevEl.style.display = 'flex';
                        },
                    }
                });
            } else {
                const nextBtn = document.querySelector('.swiper-button-next');
                const prevBtn = document.querySelector('.swiper-button-prev');
                if (nextBtn) nextBtn.style.display = 'none';
                if (prevBtn) prevBtn.style.display = 'none';
            }
            
            window.addEventListener('resize', function() {
                clearTimeout(window.resizeTimer);
                window.resizeTimer = setTimeout(function() {
                    const isMobile = window.innerWidth <= 991;
                    const hasSwiper = !!window.productsSwiper;
                    
                    if (isMobile && !hasSwiper) {
                        window.location.reload();
                    } else if (!isMobile && hasSwiper) {
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
$mysqli->close();
?>