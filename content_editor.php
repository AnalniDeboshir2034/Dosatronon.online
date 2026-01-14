<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/content_parser.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}


$sections = getAllContentSections();
$section_keys = array_keys($sections);


$selected_section = $_GET['section'] ?? ($section_keys[0] ?? 'contact_phone');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    try {
        saveContent($selected_section, $_POST['content']);
        $_SESSION['success'] = "✅ Раздел '{$selected_section}' обновлен!";
        header("Location: content_editor.php?section={$selected_section}");
        exit();
    } catch(Exception $e) {
        $_SESSION['error'] = "❌ Ошибка: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактор текстов - medicator</title>
    <link rel="stylesheet" href="cs/admin.css">
    <style>
        .section-selector {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .section-dropdown {
            width: 100%;
            padding: 12px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: 16px;
            cursor: pointer;
        }
        
        .section-dropdown option {
            background: var(--bg-card);
            color: var(--text);
            padding: 10px;
        }
        
        .editor-container {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .section-stats {
            color: var(--text-muted);
            font-size: 12px;
        }
        
        .char-count {
            text-align: right;
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 5px;
        }
        
        .char-count.warning {
            color: #e3b341;
        }
        
        .char-count.error {
            color: #f85149;
        }
        
        .save-btn {
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            margin-top: 20px;
            width: 100%;
        }
        
        .save-btn:hover {
            background: #1f6feb;
            transform: translateY(-2px);
        }
        
        .quick-nav {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .quick-nav-btn {
            padding: 6px 12px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text);
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .quick-nav-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .section-info {
            font-family: monospace;
            font-size: 12px;
            color: var(--text-muted);
            background: var(--bg-dark);
            padding: 3px 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
    </style>
    <script>
    function updateCharCount() {
        const textarea = document.getElementById('contentTextarea');
        const countElement = document.getElementById('charCount');
        const charCount = textarea.value.length;
        
        countElement.textContent = `Символов: ${charCount}`;
        countElement.className = 'char-count';
        
        if (charCount > 5000) {
            countElement.classList.add('error');
        } else if (charCount > 2000) {
            countElement.classList.add('warning');
        }
    }
    
    function changeSection() {
        const select = document.getElementById('sectionSelect');
        const selected = select.value;
        if (selected) {
            window.location.href = '?section=' + encodeURIComponent(selected);
        }
    }
    
    let autoSaveTimer;
    function setupAutoSave() {
        const textarea = document.getElementById('contentTextarea');
        textarea.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                if (confirm('Автосохранить изменения?')) {
                    document.querySelector('form').submit();
                }
            }, 30000);
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        updateCharCount();
        setupAutoSave();
    });
    </script>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>📝 Редактор текстов</h1>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 5px;">Выбери раздел для редактирования</p>
            </div>
            
            <nav class="nav-links">
                <a href="adminpanel.php">📋 Все записи</a>
                <a href="add.php">➕ Добавить запись</a>
                <a href="content_editor.php" class="active">📝 Тексты сайта</a>
                <a href="logout.php" style="color: #f85149;">🚪 Выйти</a>
            </nav>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">БЫСТРЫЙ ПЕРЕХОД</h3>
                <div class="quick-nav">
                    <a href="?section=contact_phone" class="quick-nav-btn">📞 Телефон</a>
                    <a href="?section=contact_email" class="quick-nav-btn">📧 Email</a>
                    <a href="?section=contact_address" class="quick-nav-btn">📍 Адрес</a>
                    <a href="?section=about_text" class="quick-nav-btn">🏢 О компании</a>
                    <a href="?section=copyright" class="quick-nav-btn">© Копирайт</a>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">СТАТИСТИКА</h3>
                <div style="font-size: 14px;">
                    <div style="margin-bottom: 10px;">
                        <span style="color: var(--text-muted);">Разделов:</span>
                        <span style="float: right; color: var(--primary);"><?php echo count($sections); ?></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="color: var(--text-muted);">Выбран:</span>
                        <span style="float: right; color: var(--primary); font-size: 12px;">
                            <?php echo getSectionName($selected_section); ?>
                        </span>
                    </div>
                </div>
            </div>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Редактор текстов сайта</h1>
                <a href="adminpanel.php" class="btn">← Назад</a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            

            <div class="section-selector">
                <h3>📁 Выбери раздел для редактирования:</h3>
                <select id="sectionSelect" class="section-dropdown" onchange="changeSection()">
                    <option value="">-- Выбери раздел --</option>
                    <?php foreach ($sections as $key => $section): ?>
                    <option value="<?php echo $key; ?>" <?php echo ($key == $selected_section) ? 'selected' : ''; ?>>
                        <?php echo getSectionName($key); ?> (<?php echo $key; ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <div style="margin-top: 15px; color: var(--text-muted); font-size: 14px;">
                    Всего разделов: <strong><?php echo count($sections); ?></strong>
                </div>
            </div>
            

            <?php if (isset($sections[$selected_section])): ?>
            <div class="editor-container">
                <div class="editor-header">
                    <div>
                        <h2><?php echo getSectionName($selected_section); ?></h2>
                        <div class="section-info">[<?php echo $selected_section; ?>]</div>
                    </div>
                    <div class="section-stats">
                        Строк: <?php echo $sections[$selected_section]['lines']; ?><br>
                        Символов: <?php echo $sections[$selected_section]['chars']; ?>
                    </div>
                </div>
                
                <form method="POST">
                    <textarea 
                        id="contentTextarea"
                        name="content" 
                        style="width:100%; min-height:300px; padding:15px; background:var(--bg-input); color:var(--text); border:1px solid var(--border); border-radius:6px; font-family: 'Segoe UI', Tahoma, sans-serif; font-size:14px; line-height:1.6;"
                        oninput="updateCharCount()"
                    ><?php echo htmlspecialchars($sections[$selected_section]['content']); ?></textarea>
                    
                    <div id="charCount" class="char-count">
                        Символов: <?php echo $sections[$selected_section]['chars']; ?>
                    </div>
                    
                    <button type="submit" name="save" class="btn btn-primary save-btn">
                        💾 Сохранить изменения в раздел "<?php echo getSectionName($selected_section); ?>"
                    </button>
                </form>
        
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <h3> Предпросмотр:</h3>
                    <div style="padding: 15px; background: var(--bg-dark); border-radius: 6px; border: 1px solid var(--border); margin-top: 10px; max-height: 200px; overflow-y: auto; white-space: pre-wrap;">
                        <?php echo nl2br(htmlspecialchars($sections[$selected_section]['content'])); ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="editor-container">
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <h3>🤷 Раздел не найден</h3>
                    <p>Выбери другой раздел из выпадающего списка выше</p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>