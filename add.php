<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $d_dosing = $_POST['d_dosing'];
        $performance = $_POST['performance'];
        $pressure_temperature_connections = $_POST['pressure_temperature_connections'];
        $m_seal = $_POST['m_seal'];
        $m_case = $_POST['m_case'];
        

        function saveFile($file) {
            if ($file['error'] === UPLOAD_ERR_OK) {

                return basename($file['name']);
            }
            return null;
        }
        
        $img = isset($_FILES['img']) ? saveFile($_FILES['img']) : null;
        $diag = isset($_FILES['diag']) ? saveFile($_FILES['diag']) : null;
        $pdf = isset($_FILES['pdf']) ? saveFile($_FILES['pdf']) : null;
        $filtr = isset($_FILES['filtr']) ? saveFile($_FILES['filtr']) : null;
        
        $stmt = $pdo->prepare("INSERT INTO medicator (name, d_dosing, performance, pressure_temperature_connections, m_seal, m_case, img, diag, pdf, filtr) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $name, $d_dosing, $performance, $pressure_temperature_connections,
            $m_seal, $m_case, $img, $diag, $pdf, $filtr
        ]);
        
        $_SESSION['success'] = "Запись успешно добавлена!";
        header('Location: admin.php');
        exit();
        
    } catch(PDOException $e) {
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
    <style>
        :root {
            --bg-dark: #0d1117;
            --bg-card: #161b22;
            --bg-input: #0d1117;
            --border: #30363d;
            --primary: #58a6ff;
            --text: #c9d1d9;
            --text-muted: #8b949e;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-dark);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .header h1 {
            color: var(--text);
            font-size: 1.5rem;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background: #1f6feb;
            color: white;
        }
        
        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: 14px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }
        
        .file-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .file-input-wrapper {
            border: 2px dashed var(--border);
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--bg-input);
        }
        
        .file-input-wrapper:hover {
            border-color: var(--primary);
        }
        
        .file-input {
            display: none;
        }
        
        .file-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-muted);
        }
        
        .file-label {
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .file-hint {
            color: var(--text-muted);
            font-size: 12px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid;
        }
        
        .alert-error {
            background: rgba(248, 81, 73, 0.1);
            border-color: #da3633;
            color: #f85149;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Добавить новую запись</h1>
            <a href="admin.php" class="btn">← Назад</a>
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
                <label for="pressure_temperature_connections">pressure_temperature_connections *</label>
                <input type="text" id="pressure_temperature_connections" name="pressure_temperature_connections" 
                       placeholder="5-40°C, Наружная резьба" required>
            </div>
            
            <div class="form-group">
                <label for="m_seal">m_seal</label>
                <input type="text" id="m_seal" name="m_seal" placeholder="VITON для животных препаратов">
            </div>
            
            <div class="form-group">
                <label for="m_case">m_case</label>
                <input type="text" id="m_case" name="m_case" placeholder="Полипропилен">
            </div>
            
            <div class="file-section">
                <h3 style="margin-bottom: 20px; color: var(--text);">Файлы</h3>
                <div class="file-grid">
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-label">img</div>
                        <div class="file-hint">Изображение</div>
                        <input type="file" class="file-input" name="img" accept="image/*">
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-label">diag</div>
                        <div class="file-hint">Диаграмма</div>
                        <input type="file" class="file-input" name="diag" accept="image/*,.pdf">
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-icon">📄</div>
                        <div class="file-label">pdf</div>
                        <div class="file-hint">PDF документ</div>
                        <input type="file" class="file-input" name="pdf" accept=".pdf">
                    </div>
                    
                    <div class="file-input-wrapper" onclick="this.querySelector('input').click()">
                        <div class="file-icon">⚗️</div>
                        <div class="file-label">filtr</div>
                        <div class="file-hint">Фильтр</div>
                        <input type="file" class="file-input" name="filtr" accept="image/*,.pdf">
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="reset" class="btn">Очистить</button>
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
            </div>
        </form>
    </div>
</body>
</html>