<?php
if (!file_exists('includes/config.php')) {
    die('Ошибка: файл config.php не найден');
}

require_once 'includes/config.php';

/**
 * Генерация slug из названия товара
 * @param string $name Название товара
 * @return string Slug
 */
function generateSlug($name) {
    if (empty($name)) {
        return 'product';
    }
    
    // Переводим в нижний регистр
    $name = mb_strtolower(trim($name), 'UTF-8');
    
    // Транслитерация русских букв и специальных символов
    $ru = [
        'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
        'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
        ' ','.',',','!','?',':',';','(',')','[',']','{','}','"','\'','/','\\','|','+','=','*','&','^','%','$','#','@','~','`'
    ];
    
    $en = [
        'a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p',
        'r','s','t','u','f','h','ts','ch','sh','sch','','y','','e','yu','ya',
        '-','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''
    ];
    
    $slug = str_replace($ru, $en, $name);
    
    // Для английских символов оставляем как есть, остальное заменяем на дефис
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    
    // Убираем множественные дефисы
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Обрезаем дефисы по краям
    $slug = trim($slug, '-');
    
    // Если после всего slug пустой
    if (empty($slug)) {
        $slug = 'product';
    }
    
    return $slug;
}



function getProductUrl($product) {
    static $cache = [];
    
    $id = $product['id'] ?? 0;
    if (!$id) {
        return '/catalog';
    }
    
    // Проверяем кеш
    if (isset($cache[$id])) {
        return $cache[$id];
    }
    
    // Если есть slug в БД и он не пустой
    if (!empty($product['slug'])) {
        $url = "/product/{$product['slug']}";
    } else {
        // Если нет slug - показываем с ID (временно)
        $slug = generateSlug($product['name'] ?? '');
        $url = "/product/{$id}-{$slug}";
    }
    
    // Кешируем
    $cache[$id] = $url;
    
    return $url;
}

/**
 * Получение URL картинки товара
 * @param array $product Массив с данными товара
 * @return string|null URL картинки или null
 */
function getProductImage($product) {
    if (!empty($product['img_found'])) {
        return "/" . ltrim($product['img_found'], '/');
    }
    
    if (!empty($product['img']) && $product['img'] != '-') {
        return "/images/products/" . $product['img'];
    }
    
    return null;
}


function safeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}


function getExcerpt($text, $length = 150) {
    $text = strip_tags($text ?? '');
    if (mb_strlen($text, 'UTF-8') > $length) {
        $text = mb_substr($text, 0, $length, 'UTF-8') . '...';
    }
    return $text;
}
?>