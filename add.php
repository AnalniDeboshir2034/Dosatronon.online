<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Функция для сохранения файлов на сервер
function saveFile($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $originalName = basename($file['name']);
    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
    $destination = $uploadDir . $safeName;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $safeName;
    }
    
    return null;
}

// Функция для генерации slug (можно вынести в отдельный файл)
function generateSlugForAdmin($name) {
    if (empty($name)) {
        return 'product';
    }
    
    // Переводим в нижний регистр
    $name = mb_strtolower(trim($name), 'UTF-8');
    
    // Транслитерация русских букв
    $ru = ['а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' '];
    $en = ['a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','sch','','y','','e','yu','ya','-'];
    
    $slug = str_replace($ru, $en, $name);
    
    // Убираем все кроме букв, цифр и дефиса
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Если после всего slug пустой
    if (empty($slug)) {
        $slug = 'product';
    }
    
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Получаем данные из формы
        $name = $mysqli->real_escape_string($_POST['name']);
        $d_dosing = $mysqli->real_escape_string($_POST['d_dosing']);
        $performance = $mysqli->real_escape_string($_POST['performance']);
        $pressure = $mysqli->real_escape_string($_POST['pressure']);
        $temperature = $mysqli->real_escape_string($_POST['temperature']);
        $connections = $mysqli->real_escape_string($_POST['connections']);
        $m_seal = $mysqli->real_escape_string($_POST['m_seal']);
        $m_case = $mysqli->real_escape_string($_POST['m_case']);
        $dop = $mysqli->real_escape_string($_POST['dop']);
        $opis = $mysqli->real_escape_string($_POST['opis']);
        $filtr = $mysqli->real_escape_string($_POST['filtr']);
        
        // ГЕНЕРИРУЕМ SLUG
        $slug = generateSlugForAdmin($name);
        
        // Проверяем уникальность slug
        $check_sql = "SELECT id FROM medicator WHERE slug = ?";
        $stmt_check = $mysqli->prepare($check_sql);
        $stmt_check->bind_param("s", $slug);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        // Если slug уже существует, добавляем случайное число
        if ($stmt_check->num_rows > 0) {
            $slug = $slug . '-' . rand(1000, 9999);
        }
        $stmt_check->close();
        
        // Обработка файлов
        $img = null;
        $diag = null;
        $pdf = null;
        
        if (isset($_FILES['img']) && $_FILES['img']['error'] !== UPLOAD_ERR_NO_FILE) {
            $img = saveFile($_FILES['img']);
        }
        
        if (isset($_FILES['diag']) && $_FILES['diag']['error'] !== UPLOAD_ERR_NO_FILE) {
            $diag = saveFile($_FILES['diag']);
        }
        
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            $pdf = saveFile($_FILES['pdf']);
        }
        
        // Подготовленный запрос с slug
        $stmt = $mysqli->prepare("INSERT INTO medicator (name, d_dosing, performance, pressure, temperature, connections, m_seal, m_case, dop, img, diag, pdf, opis, filtr, slug) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception($mysqli->error);
        }
        
        $stmt->bind_param("sssssssssssssss", 
            $name, $d_dosing, $performance, $pressure, $temperature,
            $connections, $m_seal, $m_case, $dop, $img, $diag, $pdf, $opis, $filtr, $slug
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Запись успешно добавлена! Slug: $slug";
            header('Location: adminpanel.php');
            exit();
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch(Exception $e) {
        $_SESSION['error'] = "Ошибка: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить запись - medicator</title>
    <link rel="stylesheet" href="cs/admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Добавить новую запись</h1>
            <a href="adminpanel.php" class="btn">← Назад</a>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="form-card">
            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" placeholder="Dosatron DIAAAL VF" required>
            </div>
            
            <div class="form-group">
                <label for="d_dosing">d_dosing *</label>
                <input type="text" id="d_dosing" name="d_dosing" placeholder="0,2-2% [1:500-1:50]" required>
            </div>
            
            <div class="form-group">
                <label for="performance">performance *</label>
                <input type="text" id="performance" name="performance" placeholder="10 мл/ч - 2.5 л/ч" required>
            </div>
            
            <!-- ... остальные поля формы ... -->
            
            <div class="form-actions">
                <button type="reset" class="btn">Очистить</button>
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
            </div>
        </form>
    </div>
</body>
</html>