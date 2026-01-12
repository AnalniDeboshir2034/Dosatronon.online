<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'includes/config.php';

// Проверяем подключение
if (!$mysqli || $mysqli->connect_error) {
    die("❌ Нет соединения с БД");
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Получаем все записи
$result = $mysqli->query("SELECT * FROM medicator ORDER BY id DESC");
if (!$result) {
    die("❌ Ошибка SQL: " . $mysqli->error);
}

$medicators = array();
while ($row = $result->fetch_assoc()) {
    $medicators[] = $row;
}
$result->free();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - medicator</title>
    <link rel="stylesheet" href="cs/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>📊 medicator DB</h1>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 5px;">Таблица: medicator</p>
            </div>
            
            <nav class="nav-links">
                <a href="adminpanel.php" class="active">📋 Все записи</a>
                <a href="add.php">➕ Добавить запись</a>
                <a href="download_log.php?file=bitrix" class="download-link">📥 Bitrix лог</a>
                <a href="download_log.php?file=error" class="download-link">📥 Error лог</a>
                <a href="logout.php" style="color: #f85149;">🚪 Выйти</a>
            </nav>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">СТАТИСТИКА</h3>
                <div style="font-size: 14px;">
                    <div style="margin-bottom: 10px;">
                        <span style="color: var(--text-muted);">Записей:</span>
                        <span style="float: right; color: var(--primary);"><?php echo count($medicators); ?></span>
                    </div>
                    <?php 
                    $stats_result = $mysqli->query("SELECT 
                        COUNT(img) as img_count,
                        COUNT(diag) as diag_count,
                        COUNT(pdf) as pdf_count
                        FROM medicator");
                    $stats = $stats_result->fetch_assoc();
                    $stats_result->free();
                    $total_files = ($stats['img_count'] + $stats['diag_count'] + $stats['pdf_count']);
                    ?>
                    <div style="margin-bottom: 10px;">
                        <span style="color: var(--text-muted);">Файлов:</span>
                        <span style="float: right; color: var(--primary);">
                            <?php echo $total_files; ?>
                        </span>
                    </div>
                </div>
            </div>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Таблица medicator</h1>
                <a href="add.php" class="btn btn-primary">➕ Добавить запись</a>
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
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>d_dosing</th>
                            <th>performance</th>
                            <th>pressure</th>
                            <th>temperature</th>
                            <th>connections</th>
                            <th>m_seal</th>
                            <th>m_case</th>
                            <th>dop</th>
                            <th>filtr</th>
                            <th>Файлы</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicators as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['d_dosing']); ?></td>
                            <td><?php echo htmlspecialchars($row['performance']); ?></td>
                            <td><?php echo htmlspecialchars($row['pressure']); ?></td>
                            <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                            <td><?php echo htmlspecialchars($row['connections']); ?></td>
                            <td><?php echo htmlspecialchars($row['m_seal']); ?></td>
                            <td><?php echo htmlspecialchars($row['m_case']); ?></td>
                            <td><?php echo htmlspecialchars($row['dop']); ?></td>
                            <td><?php echo htmlspecialchars($row['filtr']); ?></td>
                            <td>
                                <?php if ($row['img']): ?>
                                    <a href="uploads/<?php echo $row['img']; ?>" target="_blank" class="badge badge-file">img</a>
                                <?php endif; ?>
                                <?php if ($row['diag']): ?>
                                    <a href="uploads/<?php echo $row['diag']; ?>" target="_blank" class="badge badge-file">diag</a>
                                <?php endif; ?>
                                <?php if ($row['pdf']): ?>
                                    <a href="uploads/<?php echo $row['pdf']; ?>" target="_blank" class="badge badge-file">pdf</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Редактировать">✏️</a>
                                    <a href="product.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Просмотр">👁️</a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                       class="action-btn" 
                                       title="Удалить"
                                       onclick="return confirm('Удалить запись #<?php echo $row['id']; ?>?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>