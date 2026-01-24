<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ИСПРАВЛЕННЫЙ ЗАПРОС - добавлено поле slug
$stmt = $mysqli->prepare("SELECT * FROM medicator WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Привязываем результаты к переменным - добавлена переменная для slug
$stmt->bind_result(
    $item_id, $name, $d_dosing, $performance, $pressure, $temperature,
    $connections, $m_seal, $m_case, $dop, $img, $diag, $pdf, $opis, $filtr, $slug
);

// Получаем данные
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Запись не найдена!";
    header('Location: adminpanel.php');
    exit();
}

$stmt->close();

// Создаем массив для удобства - добавлено поле slug
$item = [
    'id' => $item_id,
    'name' => $name,
    'd_dosing' => $d_dosing,
    'performance' => $performance,
    'pressure' => $pressure,
    'temperature' => $temperature,
    'connections' => $connections,
    'm_seal' => $m_seal,
    'm_case' => $m_case,
    'dop' => $dop,
    'img' => $img,
    'diag' => $diag,
    'pdf' => $pdf,
    'opis' => $opis,
    'filtr' => $filtr,
    'slug' => $slug
];

// Функция для сохранения файлов
function saveFile($file, $oldFile = null) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile; // Файл не меняли
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Удаляем старый файл если есть
    if ($oldFile && file_exists($uploadDir . $oldFile)) {
        unlink($uploadDir . $oldFile);
    }
    
    $originalName = basename($file['name']);
    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
    $destination = $uploadDir . $safeName;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $safeName;
    }
    
    return null;
}

// Функция для генерации slug
function generateSlug($name, $mysqli, $excludeId = null) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9а-яё\-]+/u', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Делаем уникальным
    $original_slug = $slug;
    $counter = 1;
    
    do {
        $check_sql = "SELECT id FROM medicator WHERE slug = ?";
        if ($excludeId) {
            $check_sql .= " AND id != ?";
        }
        
        $check_stmt = $mysqli->prepare($check_sql);
        if ($excludeId) {
            $check_stmt->bind_param("si", $slug, $excludeId);
        } else {
            $check_stmt->bind_param("s", $slug);
        }
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows === 0) {
            $check_stmt->close();
            return $slug;
        }
        
        $check_stmt->close();
        $slug = $original_slug . '-' . $counter;
        $counter++;
    } while (true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        
        // Генерируем slug автоматически из названия
        $slug = generateSlug($name, $mysqli, $id);
        
        // Обработка файлов
        $img = saveFile($_FILES['img'], $item['img']);
        $diag = saveFile($_FILES['diag'], $item['diag']);
        $pdf = saveFile($_FILES['pdf'], $item['pdf']);
        
        // ИСПРАВЛЕННЫЙ ЗАПРОС UPDATE - добавлено поле slug
        $stmt = $mysqli->prepare("UPDATE medicator SET 
            name = ?, d_dosing = ?, performance = ?, pressure = ?, temperature = ?,
            connections = ?, m_seal = ?, m_case = ?, dop = ?, opis = ?, filtr = ?,
            img = ?, diag = ?, pdf = ?, slug = ?
            WHERE id = ?");
        
        if (!$stmt) {
            throw new Exception($mysqli->error);
        }
        
        // Если файл не меняли, используем старое значение
        $img = $img ?: $item['img'];
        $diag = $diag ?: $item['diag'];
        $pdf = $pdf ?: $item['pdf'];
        
        // ИСПРАВЛЕННЫЙ bind_param - добавлен slug
        $stmt->bind_param("sssssssssssssssi", 
            $name, $d_dosing, $performance, $pressure, $temperature,
            $connections, $m_seal, $m_case, $dop, $opis, $filtr,
            $img, $diag, $pdf, $slug, $id
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Запись #$id успешно обновлена!";
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
    <title>Редактировать запись - medicator</title>
    <link rel="stylesheet" href="cs/admin.css">
    <style>
        .current-file {
            margin-top: 5px;
            font-size: 12px;
            color: var(--text-muted);
        }
        .current-file a {
            color: var(--primary);
            text-decoration: none;
        }
        .current-file a:hover {
            text-decoration: underline;
        }
        .slug-info {
            background: var(--bg-secondary);
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Редактировать запись #<?php echo $item['id']; ?></h1>
            <a href="adminpanel.php" class="btn">← Назад</a>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="form-card">
            <div class="slug-info">
                <strong>ЧПУ (slug):</strong> <?php echo $item['slug'] ? htmlspecialchars($item['slug']) : 'Будет сгенерирован автоматически'; ?>
                <br>
                <small>Генерируется автоматически из названия. URL товара: /product/<?php echo $item['slug'] ? htmlspecialchars($item['slug']) : 'slug'; ?></small>
            </div>
            
            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                <small>Из этого названия будет сгенерирован ЧПУ (URL)</small>
            </div>
            
            <div class="form-group">
                <label for="d_dosing">d_dosing *</label>
                <input type="text" id="d_dosing" name="d_dosing" value="<?php echo htmlspecialchars($item['d_dosing']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="performance">performance *</label>
                <input type="text" id="performance" name="performance" value="<?php echo htmlspecialchars($item['performance']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="pressure">pressure *</label>
                <input type="text" id="pressure" name="pressure" value="<?php echo htmlspecialchars($item['pressure']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="temperature">temperature *</label>
                <input type="text" id="temperature" name="temperature" value="<?php echo htmlspecialchars($item['temperature']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="connections">connections *</label>
                <input type="text" id="connections" name="connections" value="<?php echo htmlspecialchars($item['connections']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="m_seal">m_seal *</label>
                <input type="text" id="m_seal" name="m_seal" value="<?php echo htmlspecialchars($item['m_seal']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="m_case">m_case *</label>
                <input type="text" id="m_case" name="m_case" value="<?php echo htmlspecialchars($item['m_case']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="dop">dop *</label>
                <input type="text" id="dop" name="dop" value="<?php echo htmlspecialchars($item['dop']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="opis">opis *</label>
                <textarea id="opis" name="opis" rows="4" required><?php echo htmlspecialchars($item['opis']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="filtr">filtr *</label>
                <input type="text" id="filtr" name="filtr" value="<?php echo htmlspecialchars($item['filtr']); ?>" required>
            </div>
            
            <div class="file-section">
                <h3 style="margin-bottom: 20px; color: var(--text);">Файлы</h3>
                <div class="file-grid">
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-label">img</div>
                        <div class="file-hint">Изображение</div>
                        <div class="current-file">
                            <?php if ($item['img'] && $item['img'] != '-'): ?>
                                Текущий: <a href="uploads/<?php echo $item['img']; ?>" target="_blank"><?php echo $item['img']; ?></a>
                            <?php else: ?>
                                Нет файла
                            <?php endif; ?>
                        </div>
                        <input type="file" class="file-input" name="img" accept="image/*">
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-label">diag</div>
                        <div class="file-hint">Диаграмма</div>
                        <div class="current-file">
                            <?php if ($item['diag'] && $item['diag'] != '-'): ?>
                                Текущий: <a href="uploads/<?php echo $item['diag']; ?>" target="_blank"><?php echo $item['diag']; ?></a>
                            <?php else: ?>
                                Нет файла
                            <?php endif; ?>
                        </div>
                        <input type="file" class="file-input" name="diag" accept="image/*">
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-icon">📄</div>
                        <div class="file-label">pdf</div>
                        <div class="file-hint">PDF документ</div>
                        <div class="current-file">
                            <?php if ($item['pdf'] && $item['pdf'] != '-'): ?>
                                Текущий: <a href="uploads/<?php echo $item['pdf']; ?>" target="_blank"><?php echo $item['pdf']; ?></a>
                            <?php else: ?>
                                Нет файла
                            <?php endif; ?>
                        </div>
                        <input type="file" class="file-input" name="pdf" accept=".pdf">
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 10px;">Оставьте поле пустым, чтобы сохранить текущий файл</p>
            </div>
            
            <div class="form-actions">
                <button type="reset" class="btn">Сбросить</button>
                <button type="submit" class="btn btn-primary">💾 Сохранить изменения</button>
            </div>
        </form>
    </div>
</body>
</html>