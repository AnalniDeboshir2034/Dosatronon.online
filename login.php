<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($login === 'admin' && $password === 'adminADMIN') {
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
    <link rel="stylesheet" href="cs/admin.css">

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
        <p style="margin-top:20px;color:#8d99ae;font-size:0.9rem;">Логин: admin | Пароль: adminADMIN</p>
    </div>
</body>
</html>