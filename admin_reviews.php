<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'includes/config.php';
require_once 'includes/reviews_manager.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$reviewsManager = new ReviewsManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (!empty($_POST['name']) && !empty($_POST['text'])) {
                    $reviewsManager->addReview(
                        $_POST['name'],
                        $_POST['company'] ?? '',
                        $_POST['text'],
                        $_POST['rating'] ?? 5
                    );
                    $_SESSION['success'] = "✅ Отзыв успешно добавлен!";
                } else {
                    $_SESSION['error'] = "❌ Заполните имя и текст отзыва!";
                }
                break;
                
            case 'edit':
                if (isset($_POST['review_id']) && !empty($_POST['name']) && !empty($_POST['text'])) {
                    $reviewsManager->updateReview(
                        $_POST['review_id'],
                        $_POST['name'],
                        $_POST['company'] ?? '',
                        $_POST['text'],
                        $_POST['rating'] ?? 5,
                        $_POST['status'] ?? 1
                    );
                    $_SESSION['success'] = "✅ Отзыв обновлен!";
                }
                break;
                
            case 'delete':
                if (isset($_POST['review_id'])) {
                    $reviewsManager->deleteReview($_POST['review_id']);
                    $_SESSION['success'] = "✅ Отзыв удален!";
                }
                break;
                
            case 'toggle':
                if (isset($_POST['review_id'])) {
                    $reviewsManager->toggleStatus($_POST['review_id']);
                    $_SESSION['success'] = "✅ Статус отзыва изменен!";
                }
                break;
        }
        
        header('Location: admin_reviews.php');
        exit();
    }
}

$reviews = $reviewsManager->getAllReviews();

$editReview = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editReview = $reviewsManager->getReviewById($_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление отзывами - medicator</title>
    <link rel="stylesheet" href="cs/admin.css">
    <style>
        .reviews-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .reviews-table {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow-x: auto;
        }
        
        .reviews-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .reviews-table th,
        .reviews-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .reviews-table th {
            background: var(--bg-dark);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
        }
        
        .review-rating {
            color: #f5b342;
            letter-spacing: 2px;
        }
        
        .review-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .status-active {
            background: #238636;
            color: white;
        }
        
        .status-hidden {
            background: #6e7681;
            color: white;
        }
        
        .review-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .review-text-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
        }
        
        .rating-select {
            display: flex;
            gap: 10px;
        }
        
        .rating-select label {
            cursor: pointer;
        }
        
        .btn-danger {
            background: #f85149;
        }
        
        .btn-danger:hover {
            background: #da3633;
        }
        
        @media (max-width: 768px) {
            .reviews-table {
                font-size: 12px;
            }
            
            .reviews-table th,
            .reviews-table td {
                padding: 10px;
            }
            
            .review-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>📝 Управление отзывами</h1>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 5px;">Добавляй, редактируй, удаляй</p>
            </div>
            
            <nav class="nav-links">
                <a href="adminpanel.php">📋 Все записи</a>
                <a href="add.php">➕ Добавить запись</a>
                <a href="content_editor.php">📝 Тексты сайта</a>
                <a href="admin_reviews.php" class="active">⭐ Отзывы</a>
                <a href="logout.php" style="color: #f85149;">🚪 Выйти</a>
            </nav>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                <button class="btn btn-primary" style="width: 100%;" onclick="openAddModal()">
                    ➕ Добавить отзыв
                </button>
            </div>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Управление отзывами</h1>
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
            
            <div class="reviews-stats">
                <?php
                $total = count($reviews);
                $active = count(array_filter($reviews, function($r) { return $r['status'] == 1; }));
                $hidden = $total - $active;
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">Всего отзывов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #238636;"><?php echo $active; ?></div>
                    <div class="stat-label">Активных</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #6e7681;"><?php echo $hidden; ?></div>
                    <div class="stat-label">Скрытых</div>
                </div>
            </div>
            
            <!-- Таблица отзывов -->
            <div class="reviews-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Компания</th>
                            <th>Отзыв</th>
                            <th>Рейтинг</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    📭 Нет отзывов. Добавьте первый!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo $review['id']; ?></td>
                                <td><?php echo htmlspecialchars($review['name']); ?></td>
                                <td><?php echo htmlspecialchars($review['company'] ?: '-'); ?></td>
                                <td class="review-text-preview">
                                    <?php echo htmlspecialchars(mb_substr($review['text'], 0, 50)) . (mb_strlen($review['text']) > 50 ? '...' : ''); ?>
                                </td>
                                <td class="review-rating">
                                    <?php echo str_repeat('⭐', $review['rating']); ?>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($review['date'])); ?></td>
                                <td>
                                    <span class="review-status <?php echo $review['status'] ? 'status-active' : 'status-hidden'; ?>">
                                        <?php echo $review['status'] ? 'Активен' : 'Скрыт'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="review-actions">
                                        <button class="btn btn-sm" onclick="editReview('<?php echo $review['id']; ?>')">
                                            ✏️
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="btn btn-sm">
                                                <?php echo $review['status'] ? '🙈' : '👁️'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить отзыв?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Добавить отзыв</h2>
            <form method="POST" id="reviewForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="review_id" id="reviewId" value="">
                
                <div class="form-group">
                    <label for="name">Имя *</label>
                    <input type="text" name="name" id="name" required>
                </div>
                
                <div class="form-group">
                    <label for="company">Компания / Ферма</label>
                    <input type="text" name="company" id="company">
                </div>
                
                <div class="form-group">
                    <label for="text">Текст отзыва *</label>
                    <textarea name="text" id="text" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="rating">Рейтинг</label>
                    <select name="rating" id="rating">
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>
                </div>
                
                <div class="form-group" id="statusGroup" style="display: none;">
                    <label for="status">Статус</label>
                    <select name="status" id="status">
                        <option value="1">Активен</option>
                        <option value="0">Скрыт</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal()">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Добавить отзыв';
            document.getElementById('formAction').value = 'add';
            document.getElementById('reviewId').value = '';
            document.getElementById('name').value = '';
            document.getElementById('company').value = '';
            document.getElementById('text').value = '';
            document.getElementById('rating').value = '5';
            document.getElementById('statusGroup').style.display = 'none';
            document.getElementById('reviewModal').classList.add('active');
        }
        
        function editReview(id) {
            window.location.href = '?edit=' + id;
        }
        
        function closeModal() {
            document.getElementById('reviewModal').classList.remove('active');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        <?php if ($editReview): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modalTitle').textContent = 'Редактировать отзыв';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('reviewId').value = '<?php echo $editReview['id']; ?>';
            document.getElementById('name').value = '<?php echo htmlspecialchars($editReview['name']); ?>';
            document.getElementById('company').value = '<?php echo htmlspecialchars($editReview['company']); ?>';
            document.getElementById('text').value = '<?php echo htmlspecialchars($editReview['text']); ?>';
            document.getElementById('rating').value = '<?php echo $editReview['rating']; ?>';
            document.getElementById('status').value = '<?php echo $editReview['status']; ?>';
            document.getElementById('statusGroup').style.display = 'block';
            document.getElementById('reviewModal').classList.add('active');
        });
        <?php endif; ?>
    </script>
</body>
</html>