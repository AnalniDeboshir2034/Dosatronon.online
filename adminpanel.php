<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM medicator ORDER BY id DESC");
$medicators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - medicator</title>
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
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            padding: 20px;
        }
        
        .sidebar-header {
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        
        .sidebar-header h1 {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .nav-links a {
            display: block;
            padding: 10px 15px;
            color: var(--text);
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 5px;
            transition: all 0.2s;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(88, 166, 255, 0.1);
            color: var(--primary);
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 30px;
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
        
        .table-container {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: var(--bg-dark);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
            border-bottom: 1px solid var(--border);
        }
        
        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }
        
        tr:hover {
            background: rgba(88, 166, 255, 0.05);
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-file {
            background: rgba(88, 166, 255, 0.1);
            color: var(--primary);
            margin-right: 5px;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid;
        }
        
        .alert-success {
            background: rgba(46, 160, 67, 0.1);
            border-color: #238636;
            color: #56d364;
        }
        
        .alert-error {
            background: rgba(248, 81, 73, 0.1);
            border-color: #da3633;
            color: #f85149;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>📊 medicator DB</h1>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 5px;">Таблица: medicator</p>
            </div>
            
            <nav class="nav-links">
                <a href="admin.php" class="active">📋 Все записи</a>
                <a href="add.php">➕ Добавить запись</a>
                <a href="#">⚙️ Настройки</a>
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
                    $stmt = $pdo->query("SELECT 
                        COUNT(img) as img_count,
                        COUNT(diag) as diag_count,
                        COUNT(pdf) as pdf_count,
                        COUNT(filtr) as filtr_count
                        FROM medicator");
                    $stats = $stmt->fetch();
                    ?>
                    <div style="margin-bottom: 10px;">
                        <span style="color: var(--text-muted);">Файлов:</span>
                        <span style="float: right; color: var(--primary);">
                            <?php echo array_sum($stats); ?>
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
                            <th>pressure_temperature_connections</th>
                            <th>m_seal</th>
                            <th>m_case</th>
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
                            <td><?php echo htmlspecialchars($row['pressure_temperature_connections']); ?></td>
                            <td><?php echo htmlspecialchars($row['m_seal']); ?></td>
                            <td><?php echo htmlspecialchars($row['m_case']); ?></td>
                            <td>
                                <?php if ($row['img']): ?><span class="badge badge-file">img</span><?php endif; ?>
                                <?php if ($row['diag']): ?><span class="badge badge-file">diag</span><?php endif; ?>
                                <?php if ($row['pdf']): ?><span class="badge badge-file">pdf</span><?php endif; ?>
                                <?php if ($row['filtr']): ?><span class="badge badge-file">filtr</span><?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Редактировать">✏️</a>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Просмотр">👁️</a>
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