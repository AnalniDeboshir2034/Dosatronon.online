<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $mysqli->prepare("SELECT * FROM medicator WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt->bind_result(
    $item_id, $name, $d_dosing, $performance, $pressure, $temperature,
    $connections, $m_seal, $m_case, $dop, $img, $diag, $pdf, $opis, $filtr, $slug
);

if (!$stmt->fetch()) {
    $_SESSION['error'] = "Запись не найдена!";
    header('Location: adminpanel.php');
    exit();
}

$stmt->close();

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

function saveFile($file, $oldFile = null) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }
    
    if (isset($_POST['delete_' . $file['name']]) || empty($file['name'])) {
        if ($oldFile && file_exists('uploads/' . $oldFile)) {
            unlink('uploads/' . $oldFile);
        }
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $oldFile;
    }
    
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if ($oldFile && file_exists($uploadDir . $oldFile)) {
        unlink($uploadDir . $oldFile);
    }
    
    $originalName = basename($file['name']);
    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $originalName);
    $destination = $uploadDir . $safeName;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $safeName;
    }
    
    return $oldFile;
}

function generateSlug($name, $mysqli, $excludeId = null) {
    if (empty(trim($name))) {
        return null;
    }
    
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9а-яё\-]+/u', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
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

function cleanValue($value) {
    $value = trim($value);
    return empty($value) ? null : $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = cleanValue($_POST['name']);
        $d_dosing = cleanValue($_POST['d_dosing']);
        $performance = cleanValue($_POST['performance']);
        $pressure = cleanValue($_POST['pressure']);
        $temperature = cleanValue($_POST['temperature']);
        $connections = cleanValue($_POST['connections']);
        $m_seal = cleanValue($_POST['m_seal']);
        $m_case = cleanValue($_POST['m_case']);
        $dop = cleanValue($_POST['dop']);
        $opis = cleanValue($_POST['opis']);
        $filtr = cleanValue($_POST['filtr']);
        
        if (!empty($name)) {
            $slug = generateSlug($name, $mysqli, $id);
        } else {
            $slug = null;
        }
        
        $img = saveFile($_FILES['img'], $item['img']);
        $diag = saveFile($_FILES['diag'], $item['diag']);
        $pdf = saveFile($_FILES['pdf'], $item['pdf']);
        
        $stmt = $mysqli->prepare("UPDATE medicator SET 
            name = ?, d_dosing = ?, performance = ?, pressure = ?, temperature = ?,
            connections = ?, m_seal = ?, m_case = ?, dop = ?, opis = ?, filtr = ?,
            img = ?, diag = ?, pdf = ?, slug = ?
            WHERE id = ?");
        
        if (!$stmt) {
            throw new Exception($mysqli->error);
        }
        
        $img = $img !== false ? $img : $item['img'];
        $diag = $diag !== false ? $diag : $item['diag'];
        $pdf = $pdf !== false ? $pdf : $item['pdf'];
        
        $img = empty($img) ? null : $img;
        $diag = empty($diag) ? null : $diag;
        $pdf = empty($pdf) ? null : $pdf;
        
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
        .clear-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 3px 8px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            margin-left: 5px;
        }
        .clear-btn:hover {
            background: #d32f2f;
        }
        .clear-file {
            margin-top: 5px;
        }
    </style>
    <script>
        function clearFile(fieldName) {
            let hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'delete_' + fieldName;
            hiddenInput.value = '1';
            
            let fileInput = document.querySelector('input[name="' + fieldName + '"]');
            fileInput.value = '';
            
            let currentFileDiv = fileInput.closest('.file-input-wrapper').querySelector('.current-file');
            currentFileDiv.innerHTML = '<em>Файл будет удален</em>';
            
            fileInput.parentElement.appendChild(hiddenInput);
        }
        
        function clearTextField(fieldId) {
            document.getElementById(fieldId).value = '';
        }
    </script>
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
                <label for="name">Название</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" style="flex: 1;">
                </div>
                <small>Из этого названия будет сгенерирован ЧПУ (URL). Оставьте пустым чтобы удалить.</small>
            </div>
            
            <div class="form-group">
                <label for="d_dosing">d_dosing</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="d_dosing" name="d_dosing" value="<?php echo htmlspecialchars($item['d_dosing']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="performance">performance</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="performance" name="performance" value="<?php echo htmlspecialchars($item['performance']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="pressure">pressure</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="pressure" name="pressure" value="<?php echo htmlspecialchars($item['pressure']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="temperature">temperature</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="temperature" name="temperature" value="<?php echo htmlspecialchars($item['temperature']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="connections">connections</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="connections" name="connections" value="<?php echo htmlspecialchars($item['connections']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="m_seal">m_seal</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="m_seal" name="m_seal" value="<?php echo htmlspecialchars($item['m_seal']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="m_case">m_case</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="m_case" name="m_case" value="<?php echo htmlspecialchars($item['m_case']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="dop">dop</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="dop" name="dop" value="<?php echo htmlspecialchars($item['dop']); ?>" style="flex: 1;">
                </div>
            </div>
            
            <div class="form-group">
                <label for="opis">opis</label>
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <textarea id="opis" name="opis" rows="4" style="flex: 1;"><?php echo htmlspecialchars($item['opis']); ?></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label for="filtr">filtr</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" id="filtr" name="filtr" value="<?php echo htmlspecialchars($item['filtr']); ?>" style="flex: 1;">
                </div>
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
                        <div class="clear-file">
                            <button type="button" class="clear-btn" onclick="clearFile('img')">Удалить файл</button>
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
                        <div class="clear-file">
                            <button type="button" class="clear-btn" onclick="clearFile('diag')">Удалить файл</button>
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
                        <div class="clear-file">
                            <button type="button" class="clear-btn" onclick="clearFile('pdf')">Удалить файл</button>
                        </div>
                        <input type="file" class="file-input" name="pdf" accept=".pdf">
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 10px;">
                    Оставьте поле пустым, чтобы сохранить текущий файл. Нажмите "Удалить файл" чтобы удалить существующий файл.
                </p>
            </div>
            
            <div class="form-actions">
                <button type="reset" class="btn">Сбросить</button>
                <button type="submit" class="btn btn-primary">💾 Сохранить изменения</button>
            </div>
        </form>
    </div>
</body>
</html>