<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Определяем пути к файлам логов
$log_files = [
    'bitrix' => [
        'name' => 'bitrix_log.txt',
        'path' => '/home/a7comby/dosatron.online/bitrix_log.txt', // Уточни путь!
        'content_type' => 'text/plain'
    ],
    'error' => [
        'name' => 'error_log.txt', 
        'path' => '/home/a7comby/dosatron.online/error_log', // Уточни путь!
        'content_type' => 'text/plain'
    ]
];

$file_key = isset($_GET['file']) ? $_GET['file'] : '';

if (!isset($log_files[$file_key])) {
    die("❌ Неверный файл для скачивания");
}

$log = $log_files[$file_key];

// Проверяем существует ли файл
if (!file_exists($log['path'])) {
    // Пробуем альтернативные пути
    $alternative_paths = [
        $_SERVER['DOCUMENT_ROOT'] . '/bitrix_log.txt',
        $_SERVER['DOCUMENT_ROOT'] . '/../bitrix_log.txt',
        '/home/a7comby/dosatron.online/bitrix_log.txt',
        '/home/a7comby/public_html/bitrix_log.txt',
        'bitrix_log.txt'
    ];
    
    $found = false;
    foreach ($alternative_paths as $path) {
        if (file_exists($path)) {
            $log['path'] = $path;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        die("❌ Файл лога не найден: " . $log['name']);
    }
}

// Отправляем файл для скачивания
header('Content-Description: File Transfer');
header('Content-Type: ' . $log['content_type']);
header('Content-Disposition: attachment; filename="' . $log['name'] . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($log['path']));

readfile($log['path']);
exit;
?>