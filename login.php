<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($login === 'admin' && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: adminpanel.php');
        exit();
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
    <style>
        body { background: #1a1a2e; color: white; font-family: Arial; }
        .login-box { max-width: 400px; margin: 100px auto; background: #162447; padding: 40px; border-radius: 10px; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #00b4d8; color: white; border: none; padding: 10px; width: 100%; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2> Вход в админ панель</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <p style="margin-top:20px;color:#8d99ae;font-size:0.9rem;">Логин: admin | Пароль: admin123</p>
    </div>
</body>
</html>