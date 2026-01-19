<?php
// Включаем отладку
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';

$mysqli = new mysqli($host, $user, $pass, $db_name);
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных");
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

$compare_items = [];
$compare_ids = [];

if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    echo '<script>localStorage.removeItem("compareItems"); window.location.href = "compare.php";</script>';
    exit();
}

if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    $compare_ids = [];
    
    if (isset($_GET['ids'])) {
        $ids = explode(',', $_GET['ids']);
        $ids = array_filter(array_map('intval', $ids));
        $compare_ids = array_values(array_filter($ids, function($id) use ($remove_id) {
            return $id != $remove_id;
        }));
    }
    
    if (!empty($compare_ids)) {
        header('Location: compare.php?ids=' . implode(',', $compare_ids));
    } else {
        header('Location: compare.php');
    }
    exit();
}

if (isset($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    $ids = array_filter(array_map('intval', $ids));
    $compare_ids = $ids;
}

if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $sql = "SELECT * FROM medicator WHERE id IN ($placeholders)";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $types = str_repeat('i', count($compare_ids));
        $stmt->bind_param($types, ...$compare_ids);
        $stmt->execute();
        
        // Вместо get_result() используем bind_result()
        $stmt->store_result();
        
        // Получаем информацию о колонках
        $meta = $stmt->result_metadata();
        $fields = array();
        $fieldReferences = array();
        
        while ($field = $meta->fetch_field()) {
            $fields[$field->name] = null;
            $fieldReferences[] = &$fields[$field->name];
        }
        
        call_user_func_array(array($stmt, 'bind_result'), $fieldReferences);
        
        while ($stmt->fetch()) {
            $row = array();
            foreach ($fields as $key => $value) {
                $row[$key] = $value;
            }
            $row['img_found'] = findFile($row['img'] ?? '');
            $compare_items[] = $row;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сравнение товаров | 7 company</title>
    <link rel="stylesheet" href="cs/style.css">
        <link rel="stylesheet" href="cs/compare.css">
    <script src="j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
        <script src="j/compare.js" ></script>


</head>
<body>

<?php include 'includes/header.php'; ?>

    <main class="main">
        <div class="compare-page">
            <div class="compare-header">
                <h1>Сравнение товаров</h1>
                <p>Сравните характеристики выбранных медикаторов</p>
                <a href="srav.php"class="btn btn-primary header__order-btn">Общая Сравнительная Таблица</a>
            </div>
            
            <?php if (empty($compare_items)): ?>
                <div class="compare-empty">
                    <div class="empty-icon">⚖️</div>
                    <h2 class="empty-title">Список сравнения пуст</h2>
                    <p class="empty-text">Добавьте товары для сравнения из каталога.</p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="catalog.php" class="compare-action-btn primary">
                            Перейти в каталог
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="compare-table-container">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th class="product-column">Характеристики</th>
                                <?php foreach ($compare_items as $product): ?>
                                <th>
                                    <div class="compare-product-card">
                                        <div class="compare-product-image">
                                            <?php if ($product['img_found']): ?>
                                                <img src="<?php echo htmlspecialchars($product['img_found']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php else: ?>
                                                <img src="medikator.jpg" alt="Нет изображения">
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="compare-product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <a href="compare.php?remove_id=<?php echo $product['id']; ?>&ids=<?php echo isset($_GET['ids']) ? $_GET['ids'] : ''; ?>" class="compare-remove-btn" onclick="return confirm('Удалить товар из сравнения?')">
                                            Удалить
                                        </a>
                                    </div>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $display_fields = [
                                'd_dosing' => 'Диапазон дозирования',
                                'performance' => 'Производительность',
                                'pressure' => 'Рабочее давление',
                                'temperature' => 'Температура жидкости',
                                'connections' => 'Тип подключения',
                                'm_seal' => 'Материал уплотнений',
                                'm_case' => 'Материал корпуса',
                                'dop' => 'Дополнительные характеристики',
                                'filtr' => 'Серия'
                            ];
                            
                            foreach ($display_fields as $field => $label): 
                                $has_data = false;
                                foreach ($compare_items as $product) {
                                    if (!empty($product[$field]) && $product[$field] != '-') {
                                        $has_data = true;
                                        break;
                                    }
                                }
                                
                                if ($has_data):
                            ?>
                            <tr>
                                <td class="product-column"><div class="spec-label"><?php echo $label; ?></div></td>
                                <?php foreach ($compare_items as $product): ?>
                                <td>
                                    <div class="spec-value <?php echo !empty($product[$field]) && $product[$field] != '-' ? 'highlight' : ''; ?>">
                                        <?php echo !empty($product[$field]) && $product[$field] != '-' ? htmlspecialchars($product[$field]) : '—'; ?>
                                    </div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                            
                            <tr>
                                <td class="product-column"><div class="spec-label">Действия</div></td>
                                <?php foreach ($compare_items as $product): ?>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                                           class="compare-action-btn secondary">
                                            Подробнее
                                        </a>
                                        <a href="contacts.php?product=<?php echo urlencode($product['name']); ?>" 
                                           class="compare-action-btn primary">
                                            Заказать
                                        </a>
                                    </div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="compare-actions">
                        <a href="compare.php?action=clear" class="compare-action-btn danger" onclick="return confirm('Очистить весь список сравнения?')">
                            Очистить сравнение
                        </a>
                        <a href="catalog.php" class="compare-action-btn secondary">
                            Добавить еще товары
                        </a>
                        <a href="contacts.php" class="compare-action-btn primary">
                            Заказать выбранное
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php include 'includes/footer.php';?>
<script>
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