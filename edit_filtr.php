<?php
session_start();
require_once 'includes/config.php';

if (!$mysqli || $mysqli->connect_error) {
    die("Ошибка соединения с БД");
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header('Location: filtr.php');
    exit();
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $slug = trim($_POST['slug']);
    $sort_order = intval($_POST['sort_order']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    if (!empty($name) && !empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $slug)));
        
        $check_sql = "SELECT id FROM filtr WHERE slug = ? AND id != ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("si", $slug, $id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Фильтр с таким slug уже существует!";
        } else {
            $update_sql = "UPDATE filtr SET name = ?, description = ?, slug = ?, sort_order = ?, active = ? WHERE id = ?";
            $update_stmt = $mysqli->prepare($update_sql);
            $update_stmt->bind_param("sssiii", $name, $description, $slug, $sort_order, $active, $id);
            
            if ($update_stmt->execute()) {
                $success = "Фильтр успешно обновлен!";
            } else {
                $error = "Ошибка обновления: " . $mysqli->error;
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "Заполните все обязательные поля!";
    }
}

$sql = "SELECT * FROM filtr WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$filter = $result->fetch_assoc();
$stmt->close();

if (!$filter) {
    header('Location: filtr.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать фильтр - Админ панель</title>
    <link rel="stylesheet" href="cs/admin.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .edit-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
        }
        .edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .edit-header h1 {
            font-size: 1.8rem;
            color: var(--text-primary);
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 14px;
        }
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
            font-family: inherit;
        }
        .form-group.checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group.checkbox input {
            width: auto;
        }
        .form-group.checkbox label {
            margin-bottom: 0;
            cursor: pointer;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background: #218838;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .slug-preview {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
        }
        .info-text {
            margin-top: 20px;
            padding: 15px;
            background: var(--bg-primary);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .info-text ul {
            margin: 10px 0 0 20px;
        }
        .info-text li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>📊 Админ панель</h1>
            </div>
            <nav class="nav-links">
                <a href="adminpanel.php">📋 Медикаторы</a>
                <a href="orders.php">📦 Заказы</a>
                <a href="report_orders.php">📊 Отчет по заказам</a>
                <a href="report_views.php">👁️ Просмотры</a>
                <a href="filtr.php" class="active">🏷️ Фильтры</a>
                <a href="add.php">➕ Добавить медикатор</a>
                <a href="logout.php" style="color: #f85149;">🚪 Выйти</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="edit-container">
                <div class="edit-card">
                    <div class="edit-header">
                        <h1>✏️ Редактировать фильтр</h1>
                        <a href="filtr.php" class="btn-back">← Назад к списку</a>
                    </div>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">✅ <?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">❌ <?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Название фильтра *</label>
                            <input type="text" name="name" id="filterName" value="<?php echo htmlspecialchars($filter['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Описание (отображается в каталоге)</label>
                            <textarea name="description" id="filterDesc" placeholder="Введите описание фильтра. Поддерживается HTML"><?php echo htmlspecialchars($filter['description'] ?? ''); ?></textarea>
                            <div class="slug-preview">📝 Поддерживаются теги: &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;h2&gt;, &lt;h3&gt;</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Slug (URL) *</label>
                            <input type="text" name="slug" id="filterSlug" value="<?php echo htmlspecialchars($filter['slug']); ?>" required>
                            <div class="slug-preview">🔗 URL: /catalog/<span id="slugPreview"><?php echo htmlspecialchars($filter['slug']); ?></span></div>
                        </div>
                        
                        <div class="form-group">
                            <label>Порядок сортировки</label>
                            <input type="number" name="sort_order" value="<?php echo $filter['sort_order']; ?>">
                            <div class="slug-preview">⬆️ Чем меньше число, тем выше в списке фильтров</div>
                        </div>
                        
                        <div class="form-group checkbox">
                            <input type="checkbox" name="active" id="active" <?php echo $filter['active'] ? 'checked' : ''; ?>>
                            <label for="active">✅ Активен (показывать в каталоге)</label>
                        </div>
                        
                        <div class="form-actions">
                            <a href="filtr.php" class="btn-cancel">Отмена</a>
                            <button type="submit" class="btn-submit">💾 Сохранить изменения</button>
                        </div>
                    </form>
                    
                    <div class="info-text">
                        <strong>ℹ️ Информация:</strong>
                        <ul>
                            <li>Slug используется в URL: <code>/catalog/название_фильтра</code></li>
                            <li>Описание выводится на странице каталога над товарами</li>
                            <li>Неактивные фильтры не отображаются в каталоге</li>
                            <li>В поле "filtr" у товаров указывается slug фильтра</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        const nameInput = document.getElementById('filterName');
        const slugInput = document.getElementById('filterSlug');
        const slugPreview = document.getElementById('slugPreview');
        
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                let slug = this.value.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '_')
                    .replace(/-+/g, '_');
                if (!slugInput.value || slugInput.value === '') {
                    slugInput.value = slug;
                    slugPreview.textContent = slug;
                }
            });
            
            slugInput.addEventListener('input', function() {
                let slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9_-]/g, '')
                    .replace(/-+/g, '_');
                this.value = slug;
                slugPreview.textContent = slug;
            });
        }
    </script>
</body>
</html>