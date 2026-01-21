<?php
// update-slugs.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

// Функция для генерации slug
function generateSlugForAdmin($name) {
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

echo "<pre>";
echo "<h2>Обновление slug для товаров</h2>";

$result = $mysqli->query("SELECT id, name FROM medicator WHERE slug IS NULL OR slug = ''");

if (!$result) {
    die("Ошибка запроса: " . $mysqli->error);
}

$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    $slug = generateSlugForAdmin($row['name']);
    
    // Проверяем уникальность
    $check = $mysqli->prepare("SELECT id FROM medicator WHERE slug = ?");
    $check->bind_param("s", $slug);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $slug = $slug . '-' . $row['id'];
    }
    $check->close();
    
    // Обновляем
    $update = $mysqli->prepare("UPDATE medicator SET slug = ? WHERE id = ?");
    $update->bind_param("si", $slug, $row['id']);
    
    if ($update->execute()) {
        echo "✅ ID {$row['id']}: '{$row['name']}' -> {$slug}\n";
    } else {
        echo "❌ Ошибка обновления ID {$row['id']}: " . $update->error . "\n";
    }
    
    $update->close();
}

if ($count == 0) {
    echo "✅ Все товары уже имеют slug!\n";
}

echo "\n<h3>Готово! Обработано: {$count} товаров</h3>";
echo "</pre>";
?>