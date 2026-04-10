<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Экранируем входные данные
    $login = $mysqli->real_escape_string($login);
    
    $result = $mysqli->query("SELECT * FROM users WHERE login = '$login'");
    $user = $result->fetch_assoc();
    
    if ($user && $password === $user['password']) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: adminpanel.php');
        exit();
    } else {
        $error = "Неверный логин или пароль";
    }
}
$mysqli->close();
?>  
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
    <link rel="stylesheet" href="cs/admin.css">
</head>
<body>
    <div class="login-box">
        <h2>Вход в админ панель</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>