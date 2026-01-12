<?php

$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';


$mysqli = new mysqli($host, $user, $pass, $db_name);


if ($mysqli->connect_error) {
    die("❌ Ошибка подключения к БД: " . $mysqli->connect_error . 
        "<br>Проверь:<br>" .
        "Хост: $host<br>" .
        "БД: $db_name<br>" .
        "Пользователь: $user");
}


$mysqli->set_charset("utf8mb4");

$pdo = null;
?>