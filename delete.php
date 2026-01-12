<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Сначала получаем информацию о файлах
    $result = $mysqli->query("SELECT img, diag, pdf FROM medicator WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        // Удаляем физические файлы
        $uploadDir = 'uploads/';
        $files = ['img', 'diag', 'pdf'];
        
        foreach ($files as $file) {
            if ($row[$file]) {
                $filePath = $uploadDir . $row[$file];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        // Удаляем запись из БД
        $mysqli->query("DELETE FROM medicator WHERE id = $id");
        
        $_SESSION['success'] = "Запись #$id успешно удалена!";
    } else {
        $_SESSION['error'] = "Запись не найдена!";
    }
}

header('Location: adminpanel.php');
exit();
?>