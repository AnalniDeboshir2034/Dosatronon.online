<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/forslug.php';
require_once 'includes/water_treatment.php';
include 'includes/content_parser.php';

$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

$mysqli = @new mysqli($host, $user, $pass, $db_name);

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

$selected_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$products = [];
$search_mode = !empty($search_query);

$filter_data = [];
$filter_result = $mysqli->query("SELECT id, name, slug, description, sort_order FROM filtr WHERE active = 1 ORDER BY sort_order ASC, id ASC");
if ($filter_result) {
    while ($row = $filter_result->fetch_assoc()) {
        $filter_data[] = $row;
    }
    $filter_result->free();
}

$waterTreatmentProduct = loadWaterTreatmentProduct();
$waterFilterSlug = 'uzel-vodopodgotovki';
$waterMatchesSearchQuery = static function ($name, $query) {
    if ($query === '') {
        return true;
    }
    if (function_exists('mb_stripos')) {
        return mb_stripos((string)$name, (string)$query, 0, 'UTF-8') !== false;
    }
    return stripos((string)$name, (string)$query) !== false;
};
if (is_array($waterTreatmentProduct)) {
    $filter_data[] = [
        'id' => 0,
        'name' => $waterTreatmentProduct['name'],
        'slug' => $waterFilterSlug,
        'description' => $waterTreatmentProduct['opis'] ?? '',
        'sort_order' => 99999
    ];
}

$filter_description = '';
if ($selected_filter != 'all') {
    foreach ($filter_data as $filter) {
        if ($filter['slug'] == $selected_filter) {
            $filter_description = $filter['description'] ?? '';
            break;
        }
    }
}

if ($selected_filter === $waterFilterSlug && is_array($waterTreatmentProduct)) {
    $filter_description = (string)($waterTreatmentProduct['opis'] ?? '');
}

if ($search_mode) {
    if ($selected_filter != 'all' && !empty($selected_filter)) {
        $sql = "SELECT *, slug FROM medicator WHERE filtr LIKE ? AND (name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?) ORDER BY name ASC";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $filter_param = "%" . $selected_filter . "%";
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("sssss", $filter_param, $search_param, $search_param, $search_param, $search_param);
            $stmt->execute();
            $stmt->store_result();
            
            $meta = $stmt->result_metadata();
            $fields = [];
            $fieldReferences = [];
            
            while ($field = $meta->fetch_field()) {
                $fields[$field->name] = null;
                $fieldReferences[] = &$fields[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $fieldReferences);
            
            while ($stmt->fetch()) {
                $row = [];
                foreach ($fields as $key => $value) {
                    $row[$key] = $value;
                }
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            
            $stmt->close();
        }
    } else {
        $sql = "SELECT *, slug FROM medicator WHERE name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ? ORDER BY name ASC";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
            $stmt->execute();
            $stmt->store_result();
            
            $meta = $stmt->result_metadata();
            $fields = [];
            $fieldReferences = [];
            
            while ($field = $meta->fetch_field()) {
                $fields[$field->name] = null;
                $fieldReferences[] = &$fields[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $fieldReferences);
            
            while ($stmt->fetch()) {
                $row = [];
                foreach ($fields as $key => $value) {
                    $row[$key] = $value;
                }
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            
            $stmt->close();
        }
    }
} else {
    if ($selected_filter != 'all' && !empty($selected_filter)) {
        $sql = "SELECT *, slug FROM medicator WHERE filtr LIKE ? ORDER BY name ASC";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $filter_param = "%" . $selected_filter . "%";
            $stmt->bind_param("s", $filter_param);
            $stmt->execute();
            $stmt->store_result();
            
            $meta = $stmt->result_metadata();
            $fields = [];
            $fieldReferences = [];
            
            while ($field = $meta->fetch_field()) {
                $fields[$field->name] = null;
                $fieldReferences[] = &$fields[$field->name];
            }
            
            call_user_func_array([$stmt, 'bind_result'], $fieldReferences);
            
            while ($stmt->fetch()) {
                $row = [];
                foreach ($fields as $key => $value) {
                    $row[$key] = $value;
                }
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            
            $stmt->close();
        }
    } else {
        $sql = "SELECT *, slug FROM medicator ORDER BY name ASC";
        $result = $mysqli->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['img_found'] = findFile($row['img'] ?? '');
                $products[] = $row;
            }
            $result->free();
        }
    }
}

if (is_array($waterTreatmentProduct)) {
    $waterTreatmentProduct['img_found'] = findFile($waterTreatmentProduct['main_img'] ?? '');
    $waterMatchesFilter = ($selected_filter === 'all' || $selected_filter === $waterFilterSlug);
    $waterMatchesSearch = !$search_mode || $waterMatchesSearchQuery($waterTreatmentProduct['name'], $search_query);
    if ($waterMatchesFilter && $waterMatchesSearch) {
        $products[] = $waterTreatmentProduct;
    }
}

function getContent($section) {
    require_once 'includes/content_parser.php';
    return getContentSection($section, '');
}

$meta_desc = getContent('meta_description');
$meta_keys = getContent('meta_keywords');
$page_title = getContent('header_title');
$favicon = getContent('favicon');
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
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/cs/style.css">
    <link rel="stylesheet" href="/cs/catalog.css">
    <script src="/j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
    <script src="/j/catalog.js"></script>
    <style>
        .filter-description {
            padding: 30px 0;
            background: var(--card);
            border-bottom: 1px solid var(--border);
        }
        .filter-description-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            color: var(--foreground);
            line-height: 1.6;
        }
        .filter-description-content h2 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        .filter-description-content h3 {
            font-size: 1.2rem;
            margin: 15px 0 10px;
        }
        .filter-description-content p {
            margin-bottom: 10px;
        }
        .filter-description-content ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .filter-description-content li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main">
        <section class="catalog-header">
            <div class="container">
                <h1>Каталог медикаторов</h1>
                <p>Полный ассортимент оборудования для дозирования и систем орошения</p>
            </div>
        </section>

        <?php if (!empty($filter_description) && $selected_filter != 'all'): ?>
        <section class="filter-description">
            <div class="container">
                <div class="filter-description-content">
                    <?php echo $filter_description; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <div class="catalog-container">
            <aside class="catalog-sidebar">
                <h2 class="sidebar-title">Фильтры по типу</h2>
                
                <div class="filter-list">
                    <?php 
                    $all_url = "/catalog";
                    if (!empty($search_query)) {
                        $all_url .= "?search=" . urlencode($search_query);
                    }
                    $all_count_sql = "SELECT COUNT(*) as count FROM medicator";
                    if ($search_mode) {
                        $all_count_sql .= " WHERE name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?";
                    }
                    $all_count = 0;
                    $stmt = $mysqli->prepare($all_count_sql);
                    if ($stmt) {
                        if ($search_mode) {
                            $search_param = "%" . $search_query . "%";
                            $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
                        }
                        $stmt->execute();
                        $stmt->bind_result($all_count);
                        $stmt->fetch();
                        $stmt->close();
                    }
                    if (is_array($waterTreatmentProduct) && (!$search_mode || $waterMatchesSearchQuery($waterTreatmentProduct['name'], $search_query))) {
                        $all_count++;
                    }
                    ?>
                    <a href="<?php echo $all_url; ?>" 
                       class="filter-item <?php echo $selected_filter == 'all' ? 'active' : ''; ?>">
                        Все товары
                        <span class="filter-count"><?php echo $all_count; ?></span>
                    </a>
                    
                    <?php foreach ($filter_data as $filter): 
                        $filter_url = "/catalog/" . $filter['slug'];
                        if (!empty($search_query)) {
                            $filter_url .= "?search=" . urlencode($search_query);
                        }
                        
                        $is_active = ($selected_filter == $filter['slug']);
                        
                        $count = 0;
                        if ($filter['slug'] === $waterFilterSlug) {
                            if (is_array($waterTreatmentProduct)) {
                                $count = (!$search_mode || $waterMatchesSearchQuery($waterTreatmentProduct['name'], $search_query)) ? 1 : 0;
                            }
                        } else {
                            $count_sql = "SELECT COUNT(*) as count FROM medicator WHERE filtr LIKE ?";
                            $params = ["%" . $filter['slug'] . "%"];
                            $types = "s";
                            
                            if ($search_mode) {
                                $count_sql .= " AND (name LIKE ? OR d_dosing LIKE ? OR performance LIKE ? OR filtr LIKE ?)";
                                $search_param = "%" . $search_query . "%";
                                $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
                                $types .= "ssss";
                            }
                            
                            $stmt = $mysqli->prepare($count_sql);
                            if ($stmt) {
                                $stmt->bind_param($types, ...$params);
                                $stmt->execute();
                                $stmt->bind_result($count);
                                $stmt->fetch();
                                $stmt->close();
                            }
                        }
                    ?>
                        <a href="<?php echo $filter_url; ?>" 
                           class="filter-item <?php echo $is_active ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($filter['name']); ?>
                            <span class="filter-count"><?php echo $count; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            
            <div class="catalog-content">
                <div class="catalog-info">
                    <?php if ($search_mode): ?>
                        <div class="search-results-info">
                            <strong>Результаты поиска:</strong> "<?php echo htmlspecialchars($search_query); ?>"
                            <a href="/catalog" class="clear-search">
                                ✕ Очистить поиск
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="catalog-count">
                        Найдено товаров: <strong id="productCount"><?php echo count($products); ?></strong>
                    </div>
                    
                    <?php if ($selected_filter != 'all'): ?>
                        <div class="current-filter">
                            Активный фильтр: <?php 
                            $filter_name = '';
                            foreach ($filter_data as $filter) {
                                if ($filter['slug'] == $selected_filter) {
                                    $filter_name = $filter['name'];
                                    break;
                                }
                            }
                            echo htmlspecialchars($filter_name ?: $selected_filter); 
                            ?>
                        </div>
                        <a href="/catalog<?php echo $search_mode ? '?search=' . urlencode($search_query) : ''; ?>" class="clear-filter">
                            Сбросить фильтр
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="catalog-grid" id="productsGrid">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                        <?php $isWaterTreatment = (($product['type'] ?? '') === 'water-treatment'); ?>
                        <div class="product-card" 
                             data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
                             data-product-dosing="<?php echo htmlspecialchars($product['d_dosing'] ?? ''); ?>"
                             data-product-performance="<?php echo htmlspecialchars($product['performance'] ?? ''); ?>"
                             data-product-filter="<?php echo htmlspecialchars($product['filtr'] ?? ''); ?>">
                            <div class="product-card__image">
                                <?php if (!empty($product['img_found'])): ?>
                                    <img src="/<?php echo htmlspecialchars($product['img_found']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                <?php elseif (!empty($product['img']) && $product['img'] != '-'): ?>
                                    <img src="/images/products/<?php echo htmlspecialchars($product['img']); ?>" 
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
                                <?php if (!empty($product['filtr']) && $product['filtr'] != '-'): ?>
                                    <div class="product-card__filter">
                                        <?php 
                                        $filters = explode(',', $product['filtr']);
                                        echo htmlspecialchars(trim($filters[0]));
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <h3 class="product-card__title">
                                    <a href="<?php echo $isWaterTreatment ? '/product/' . rawurlencode($product['slug']) : getProductUrl($product); ?>" style="color: inherit; text-decoration: none;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-card__desc">
                                    <?php if ($isWaterTreatment): ?>
                                        <?php echo htmlspecialchars(getExcerpt($product['opis'] ?? '', 130)); ?>
                                    <?php elseif (!empty($product['d_dosing'])): ?>
                                        <strong>Дозировка:</strong> <?php echo htmlspecialchars($product['d_dosing']); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($product['performance'])): ?>
                                        <strong>Производительность:</strong> <?php echo htmlspecialchars($product['performance']); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($product['filtr']) && $product['filtr'] != '-'): ?>
                                        <strong>Серия:</strong> <?php echo htmlspecialchars($product['filtr']); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="product-card__actions">
                                    <?php if (!$isWaterTreatment): ?>
                                    <button class="btn btn-secondary" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                        В сравнение
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?php echo $isWaterTreatment ? '/product/' . rawurlencode($product['slug']) : getProductUrl($product); ?>" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>
                                <?php if ($search_mode): ?>
                                    По запросу "<?php echo htmlspecialchars($search_query); ?>" ничего не найдено.
                                <?php elseif ($selected_filter != 'all'): ?>
                                    Товары не найдены. Попробуйте другой фильтр.
                                <?php else: ?>
                                    Товары не найдены. Добавьте товары в базу данных.
                                <?php endif; ?>
                            </p>
                            <a href="/catalog" class="btn btn-primary" style="margin-top: 20px;">
                                Показать все товары
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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

        function updateCompareButtons() {
            const compareItems = getCompareItems();
            
            document.querySelectorAll('.btn-secondary[data-product-id]').forEach(button => {
                const productId = Number.parseInt(button.getAttribute('data-product-id'), 10);
                const exists = compareItems.some(item => item.id === productId);
                
                if (exists) {
                    button.textContent = '✓ В сравнении';
                    button.classList.add('btn-success');
                    button.classList.remove('btn-secondary');
                } else {
                    button.textContent = 'В сравнение';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                }
            });
        }

        document.querySelectorAll('.btn-secondary[data-product-id]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = Number.parseInt(this.getAttribute('data-product-id'), 10);
                const productName = this.getAttribute('data-product-name');

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
                    
                    this.textContent = '✓ В сравнении';
                    this.classList.add('btn-success');
                    this.classList.remove('btn-secondary');
                    
                    const ids = compareItems.map(item => item.id).join(',');
                    showNotification(`Товар добавлен! <a href="compare.php?ids=${ids}" style="color: white; text-decoration: underline;">Перейти к сравнению</a>`);
                } else {
                    compareItems = compareItems.filter(item => item.id !== productId);
                    setCompareItems(compareItems);
                    
                    this.textContent = 'В сравнение';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    
                    showNotification(`Товар "${productName}" удален из сравнения!`);
                }
            });
        });
        
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = message;
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
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateCompareButtons();
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
            
            window.addEventListener('storage', function(e) {
                if (e.key === COMPARE_STORAGE_KEY) {
                    updateCompareButtons();
                }
            });
        });
        
        (function(w,d,u){
            var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
            var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.by/b15313854/crm/site_button/loader_6_ykawzi.js');
    </script>
</body>
</html>

<?php $mysqli->close(); ?>