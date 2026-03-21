<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

if (!$mysqli || $mysqli->connect_error) {
    die("Ошибка соединения с БД");
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['add_filter'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $slug = trim($_POST['slug']);
    $sort_order = intval($_POST['sort_order']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    if (!empty($name) && !empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $slug)));
        
        $check_sql = "SELECT id FROM filtr WHERE slug = ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("s", $slug);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Фильтр с таким slug уже существует!";
        } else {
            $insert_sql = "INSERT INTO filtr (name, description, slug, sort_order, active) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_sql);
            $insert_stmt->bind_param("sssii", $name, $description, $slug, $sort_order, $active);
            
            if ($insert_stmt->execute()) {
                header('Location: filtr.php?success=added');
                exit();
            } else {
                $error = "Ошибка добавления: " . $mysqli->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "Заполните все обязательные поля!";
    }
}

if (isset($_POST['edit_filter'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $slug = trim($_POST['slug']);
    $sort_order = intval($_POST['sort_order']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    if (!empty($name) && !empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $slug)));
        
        $update_sql = "UPDATE filtr SET name = ?, description = ?, slug = ?, sort_order = ?, active = ? WHERE id = ?";
        $update_stmt = $mysqli->prepare($update_sql);
        $update_stmt->bind_param("sssiii", $name, $description, $slug, $sort_order, $active, $id);
        
        if ($update_stmt->execute()) {
            header('Location: filtr.php?success=updated');
            exit();
        } else {
            $error = "Ошибка обновления: " . $mysqli->error;
        }
        $update_stmt->close();
    } else {
        $error = "Заполните все обязательные поля!";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $slug = $_GET['slug'];
    
    $check_sql = "SELECT COUNT(*) as count FROM medicator WHERE filtr LIKE ?";
    $check_stmt = $mysqli->prepare($check_sql);
    $filter_slug = "%" . $slug . "%";
    $check_stmt->bind_param("s", $filter_slug);
    $check_stmt->execute();
    $check_stmt->bind_result($product_count);
    $check_stmt->fetch();
    $check_stmt->close();
    
    if ($product_count > 0) {
        $error = "Невозможно удалить фильтр! Он используется в $product_count товарах.";
    } else {
        $delete_sql = "DELETE FROM filtr WHERE id = ?";
        $delete_stmt = $mysqli->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            header('Location: filtr.php?success=deleted');
            exit();
        } else {
            $error = "Ошибка удаления: " . $mysqli->error;
        }
        $delete_stmt->close();
    }
}

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $current_status = intval($_GET['status']);
    $new_status = $current_status == 1 ? 0 : 1;
    
    $update_sql = "UPDATE filtr SET active = ? WHERE id = ?";
    $update_stmt = $mysqli->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_status, $id);
    
    if ($update_stmt->execute()) {
        header('Location: filtr.php?success=toggle');
        exit();
    } else {
        $error = "Ошибка изменения статуса: " . $mysqli->error;
    }
    $update_stmt->close();
}

$filters = [];
$sql = "SELECT * FROM filtr ORDER BY sort_order ASC, id ASC";
$result = $mysqli->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $filters[] = $row;
    }
    $result->free();
}

$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'added') $success_message = 'Фильтр успешно добавлен!';
    if ($_GET['success'] == 'updated') $success_message = 'Фильтр успешно обновлен!';
    if ($_GET['success'] == 'deleted') $success_message = 'Фильтр успешно удален!';
    if ($_GET['success'] == 'toggle') $success_message = 'Статус фильтра изменен!';
}

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$edit_filter = null;
if ($edit_id > 0) {
    $edit_sql = "SELECT * FROM filtr WHERE id = ?";
    $edit_stmt = $mysqli->prepare($edit_sql);
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_stmt->store_result();
    
    if ($edit_stmt->num_rows > 0) {
        $edit_filter = [];
        $meta = $edit_stmt->result_metadata();
        $fields = [];
        $fieldReferences = [];
        
        while ($field = $meta->fetch_field()) {
            $fields[$field->name] = null;
            $fieldReferences[] = &$fields[$field->name];
        }
        
        call_user_func_array([$edit_stmt, 'bind_result'], $fieldReferences);
        
        if ($edit_stmt->fetch()) {
            foreach ($fields as $key => $value) {
                $edit_filter[$key] = $value;
            }
        }
    }
    $edit_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление фильтрами</title>
    <link rel="stylesheet" href="cs/admin.css">
    <style>
        .filters-container { padding: 20px; }
        .filters-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .btn-add { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; text-decoration: none; display: inline-block; }
        .filters-table { width: 100%; border-collapse: collapse; background: var(--bg-secondary); border-radius: 12px; overflow: hidden; }
        .filters-table th, .filters-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border); }
        .filters-table th { background: #667eea; color: white; font-weight: 600; }
        .filters-table tr:hover { background: var(--bg-hover); }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-active { background: #c3e6cb; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .action-buttons { display: flex; gap: 8px; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 4px; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-edit { background: #ffc107; color: #856404; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-toggle { background: #17a2b8; color: white; }
        .form-card { background: var(--bg-secondary); padding: 30px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary); }
        .form-group textarea { min-height: 120px; }
        .form-group.checkbox { display: flex; align-items: center; gap: 10px; }
        .form-group.checkbox input { width: auto; }
        .form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .btn-submit { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-cancel { background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .slug-preview { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h1>📊 Админ панель</h1></div>
            <nav class="nav-links">
                <a href="adminpanel.php">📋 Медикаторы</a>
                <a href="orders.php">📦 Заказы</a>
                <a href="report_orders.php">📊 Отчет по заказам</a>
                <a href="report_views.php">👁️ Просмотры</a>
                <a href="filtr.php" class="active">🏷️ Фильтры</a>
                <a href="add.php">➕ Добавить</a>
                <a href="logout.php" style="color: #f85149;">🚪 Выйти</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="filters-container">
                
                <?php if ($edit_filter): ?>
                    <div class="form-card">
                        <div class="filters-header">
                            <h2>✏️ Редактировать фильтр</h2>
                            <a href="filtr.php" class="btn-back">← Назад</a>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error">❌ <?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $edit_filter['id']; ?>">
                            <div class="form-group">
                                <label>Название *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_filter['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea name="description"><?php echo htmlspecialchars($edit_filter['description'] ?? ''); ?></textarea>
                                <div class="slug-preview">Поддерживается HTML</div>
                            </div>
                            <div class="form-group">
                                <label>Slug (URL) *</label>
                                <input type="text" name="slug" value="<?php echo htmlspecialchars($edit_filter['slug']); ?>" required>
                                <div class="slug-preview">URL: /catalog/<?php echo htmlspecialchars($edit_filter['slug']); ?></div>
                            </div>
                            <div class="form-group">
                                <label>Порядок сортировки</label>
                                <input type="number" name="sort_order" value="<?php echo $edit_filter['sort_order']; ?>">
                            </div>
                            <div class="form-group checkbox">
                                <input type="checkbox" name="active" id="active" <?php echo $edit_filter['active'] ? 'checked' : ''; ?>>
                                <label for="active">Активен</label>
                            </div>
                            <div class="form-actions">
                                <a href="filtr.php" class="btn-cancel">Отмена</a>
                                <button type="submit" name="edit_filter" class="btn-submit">Сохранить</button>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif (isset($_GET['add'])): ?>
                    <div class="form-card">
                        <div class="filters-header">
                            <h2>➕ Добавить фильтр</h2>
                            <a href="filtr.php" class="btn-back">← Назад</a>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error">❌ <?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label>Название *</label>
                                <input type="text" name="name" id="addName" required>
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea name="description"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Slug (URL) *</label>
                                <input type="text" name="slug" id="addSlug" required>
                                <div class="slug-preview">URL: /catalog/<span id="slugPreview"></span></div>
                            </div>
                            <div class="form-group">
                                <label>Порядок сортировки</label>
                                <input type="number" name="sort_order" value="0">
                            </div>
                            <div class="form-group checkbox">
                                <input type="checkbox" name="active" checked>
                                <label>Активен</label>
                            </div>
                            <div class="form-actions">
                                <a href="filtr.php" class="btn-cancel">Отмена</a>
                                <button type="submit" name="add_filter" class="btn-submit">Добавить</button>
                            </div>
                        </form>
                    </div>
                    
                    <script>
                        document.getElementById('addName')?.addEventListener('input', function() {
                            let slug = this.value.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '_').replace(/-+/g, '_');
                            document.getElementById('addSlug').value = slug;
                            document.getElementById('slugPreview').textContent = slug;
                        });
                        document.getElementById('addSlug')?.addEventListener('input', function() {
                            let slug = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '').replace(/-+/g, '_');
                            this.value = slug;
                            document.getElementById('slugPreview').textContent = slug;
                        });
                    </script>
                    
                <?php else: ?>
                    <div class="filters-header">
                        <div>
                            <h1>🏷️ Управление фильтрами</h1>
                            <p style="color: var(--text-muted); margin-top: 5px;">Фильтры для каталога товаров</p>
                        </div>
                        <a href="filtr.php?add=1" class="btn-add">+ Добавить фильтр</a>
                    </div>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">✅ <?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">❌ <?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <table class="filters-table">
                        <thead>
                            <tr><th>ID</th><th>Название</th><th>Описание</th><th>Slug</th><th>Сорт.</th><th>Статус</th><th>Действия</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filters)): ?>
                                <tr><td colspan="7" style="text-align: center; padding: 40px;">📭 Фильтров нет. <a href="filtr.php?add=1">Добавьте первый!</a></td>?</tr>
                            <?php else: ?>
                                <?php foreach ($filters as $filter): 
                                    $product_count_sql = "SELECT COUNT(*) FROM medicator WHERE filtr LIKE ?";
                                    $stmt = $mysqli->prepare($product_count_sql);
                                    $filter_slug = "%" . $filter['slug'] . "%";
                                    $stmt->bind_param("s", $filter_slug);
                                    $stmt->execute();
                                    $stmt->bind_result($product_count);
                                    $stmt->fetch();
                                    $stmt->close();
                                ?>
                                 <tr>
                                    <td><?php echo $filter['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($filter['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(mb_substr(strip_tags($filter['description'] ?? ''), 0, 50)) ?: '—'; ?></td>
                                    <td><code><?php echo htmlspecialchars($filter['slug']); ?></code></td>
                                    <td><?php echo $filter['sort_order']; ?></td>
                                    <td><span class="status-badge <?php echo $filter['active'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $filter['active'] ? 'Активен' : 'Неактивен'; ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="filtr.php?edit_id=<?php echo $filter['id']; ?>" class="action-btn btn-edit">✏️</a>
                                            <a href="filtr.php?toggle=<?php echo $filter['id']; ?>&status=<?php echo $filter['active']; ?>" class="action-btn btn-toggle" onclick="return confirm('Изменить статус?')"><?php echo $filter['active'] ? '🔴' : '🟢'; ?></a>
                                            <a href="filtr.php?delete=<?php echo $filter['id']; ?>&slug=<?php echo urlencode($filter['slug']); ?>" class="action-btn btn-delete" onclick="return confirm('Удалить? Используется в <?php echo $product_count; ?> товарах')">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 30px; padding: 20px; background: var(--bg-secondary); border-radius: 8px;">
                        <h3>📌 Информация</h3>
                        <ul style="margin: 10px 0 0 20px;">
                            <li>Фильтры используются для сортировки товаров по сериям</li>
                            <li>Описание выводится на странице каталога над товарами</li>
                            <li>Slug в URL: <code>/catalog/название</code></li>
                            <li>Неактивные фильтры не отображаются</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>