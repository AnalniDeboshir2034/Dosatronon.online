<?php

function parseContentFile($filename = 'content.txt') {
    if (!file_exists($filename)) {
        createDefaultContentFile($filename);
    }
    
    $content = file_get_contents($filename);
    $sections = [];
    $current_section = null;
    
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = rtrim($line);

        if (preg_match('/^\[([a-z_]+)\]$/i', $line, $matches)) {
            $current_section = $matches[1];
            $sections[$current_section] = '';
            continue;
        }
        

        if ($current_section !== null) {

            if ($sections[$current_section] === '' && trim($line) === '') {
                continue;
            }
            
  
            $sections[$current_section] .= $line . "\n";
        }
    }
    

    foreach ($sections as $key => $value) {
        $sections[$key] = rtrim($value);
    }
    
    return $sections;
}

function getContentSection($section, $default = '') {
    $sections = parseContentFile();
    return $sections[$section] ?? $default;
}

function saveContentFile($sections, $filename = 'content.txt') {
    $content = "";
    foreach ($sections as $section => $text) {
        $content .= "[$section]\n" . trim($text) . "\n\n";
    }
    
    return file_put_contents($filename, trim($content));
}

// function createDefaultContentFile($filename) {
//     $default_content = <<<TEXT
// [contact_phone]
// +375 33 680 07 07
// +375 29 883 00 07

// [contact_email]
// info@7company.by

// [contact_address]
// г. Минск, ул. Толбухина д.2

// [copyright]
// © 2025 7company. Все права защищены.

// [working_hours]
// Пн-Пт: 9:00-18:00
// Сб-Вс: Выходной

// [about_text]
// 7 company — поставщик профессионального оборудования для систем дозирования и орошения. Мы работаем с сельскохозяйственными предприятиями, тепличными комплексами и промышленными объектами.

// Наша специализация — медикаторы и дозаторы для точного внесения удобрений, средств защиты растений и химических реагентов.

// [meta_description]
// Каталог профессиональных медикаторов и дозаторов для сельского хозяйства

// [meta_keywords]
// медикаторы, дозаторы, сельское хозяйство, оборудование, Dosatron
// TEXT;
    
//     return file_put_contents($filename, $default_content);
// }

function getAllContentSections() {
    $sections = parseContentFile();
    $result = [];
    
    foreach ($sections as $key => $content) {
        $result[$key] = [
            'name' => getSectionName($key),
            'content' => $content,
            'lines' => substr_count($content, "\n") + 1,
            'chars' => strlen($content)
        ];
    }
    
    return $result;
}

function getSectionName($key) {
    $names = [
        'contact_phone' => '📞 Телефоны',
        'contact_email' => '📧 Email',
        'contact_address' => '📍 Адрес',
        'copyright' => 'Подвал:© Копирайт',
        'about_text' => 'Главная: Текст "О компании"',
        'working_hours' => '⏰ Часы работы',
        'meta_description' => '🔍 Meta описание',
        'meta_keywords' => '🔑 Meta keywords',
        'header_title' => '🏷️ Заголовок сайта',
        'footer_text' => '🦶 Текст в подвале',
        'contact_form_title' => '📝 Заголовок формы',
        'compare_title' => '⚖️ Заголовок сравнения',
        'head_name' =>'Название на шапке'
    ];
    
    return $names[$key] ?? $key;
}
function saveContent($section, $new_content) {
    try {
       
        // 2. Читаем текущие данные
        $sections = parseContentFile();
        
        // 3. Если секции нет - создаем
        $sections[$section] = trim($new_content);
        
        // 4. Сохраняем напрямую, без лишних проверок
        $content = "";
        foreach ($sections as $sec => $text) {
            $content .= "[$sec]\n" . trim($text) . "\n\n";
        }
        
        // 5. Простая запись в файл
        $result = file_put_contents('content.txt', trim($content));
        
        if ($result === false) {
            throw new Exception("Не могу записать в файл. Проверь права: chmod 755 filling");
        }
        
        return true;
        
    } catch(Exception $e) {
        // Показываем реальную ошибку
        error_log("Save error: " . $e->getMessage());
        throw new Exception("Ошибка: " . $e->getMessage());
    }
}
?>