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
        
        // Подготовленный запрос
        $stmt = $mysqli->prepare("INSERT INTO medicator (name, d_dosing, performance, pressure, temperature, connections, m_seal, m_case, dop, img, diag, pdf, opis, filtr) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception($mysqli->error);
        }
        
        $stmt->bind_param("ssssssssssssss", 
            $name, $d_dosing, $performance, $pressure, $temperature,
            $connections, $m_seal, $m_case, $dop, $img, $diag, $pdf, $opis, $filtr
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Запись успешно добавлена!";
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
            
            <div class="form-group">
                <label for="pressure">pressure *</label>
                <input type="text" id="pressure" name="pressure" placeholder="0,3-6 бар" required>
            </div>
            
            <div class="form-group">
                <label for="temperature">temperature *</label>
                <input type="text" id="temperature" name="temperature" placeholder="5-40°C" required>
            </div>
            
            <div class="form-group">
                <label for="connections">connections *</label>
                <input type="text" id="connections" name="connections" placeholder="G¾\" наружная" required>
            </div>
            
            <div class="form-group">
                <label for="m_seal">m_seal *</label>
                <input type="text" id="m_seal" name="m_seal" placeholder="VITON – для кислот, масел, ветеринарных препаратов" required>
            </div>
            
            <div class="form-group">
                <label for="m_case">m_case *</label>
                <input type="text" id="m_case" name="m_case" placeholder="Полиацеталь" required>
            </div>
            
            <div class="form-group">
                <label for="dop">dop *</label>
                <input type="text" id="dop" name="dop" placeholder="-" required>
            </div>
            
            <div class="form-group">
                <label for="opis">opis *</label>
                <textarea id="opis" name="opis" rows="4" placeholder="Описание..." required></textarea>
            </div>
            
            <div class="form-group">
                <label for="filtr">filtr *</label>
                <input type="text" id="filtr" name="filtr" placeholder="DIA" required>
            </div>
            
            <div class="file-section">
                <h3 style="margin-bottom: 20px; color: var(--text);">Файлы *</h3>
                <div class="file-grid">
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-label">img *</div>
                        <div class="file-hint">Изображение</div>
                        <input type="file" class="file-input" name="img" accept="image/*" required>
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-label">diag </div>
                        <div class="file-hint">Диаграмма</div>
                        <input type="file" class="file-input" name="diag" accept="image/*" >
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-icon">📄</div>
                        <div class="file-label">pdf </div>
                        <div class="file-hint">PDF документ</div>
                        <input type="file" class="file-input" name="pdf" accept=".pdf" >
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 10px;">* Все файлы обязательны для загрузки</p>
            </div>
            
            <div class="form-actions">
                <button type="reset" class="btn">Очистить</button>
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
            </div>
        </form>
    </div>
</body>
</html>