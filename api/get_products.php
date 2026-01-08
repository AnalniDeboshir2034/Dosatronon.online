<?php

header('Content-Type: application/json');

$host = 'localhost';
$user = 'a7comby_dosatron_user';
$pass = 'dosatron_user';
$db_name = 'a7comby_dosatron';


$mysqli = new mysqli($host, $user, $pass, $db_name);
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Ошибка подключения к БД']));
}
$mysqli->set_charset("utf8mb4");


$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

function findFile($dbPath) {
    if (empty($dbPath) || $dbPath == '-' || $dbPath == 'NULL') {
        return null;
    }
    
    $fileName = basename($dbPath);
    $searchFolders = ['', 'images/', 'img/', 'products/', 'uploads/', 'images/products/', 'img/products/'];
    
    foreach ($searchFolders as $folder) {
        $fullPath = $folder . $fileName;
        $fullPath = str_replace('\\', '/', $fullPath);
        $fullPath = preg_replace('#/+#', '/', $fullPath);
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return $fullPath;
        }
    }
    
    return null;
}


$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT * FROM medicator WHERE id IN ($placeholders)";

$stmt = $mysqli->prepare($sql);
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['img_found'] = findFile($row['img'] ?? '');
    $products[] = $row;
}

echo json_encode($products, JSON_UNESCAPED_UNICODE);

$stmt->close();
$mysqli->close();
?>