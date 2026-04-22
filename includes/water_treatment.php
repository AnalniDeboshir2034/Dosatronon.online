<?php

function getWaterTreatmentPath()
{
    return __DIR__ . '/../storage/water-treatment.json';
}

function loadWaterTreatmentProduct()
{
    $path = getWaterTreatmentPath();
    if (!file_exists($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || empty($decoded)) {
        return null;
    }

    $entry = reset($decoded);
    if (!is_array($entry)) {
        return null;
    }

    $name = trim((string)($entry['Имя'] ?? 'Узел водоподготовки'));
    $slug = generateSlug($name);

    $mainImg = (string)($entry['img'] ?? '');
    if ($mainImg !== '' && strpos($mainImg, 'product/') === 0) {
        $mainImg = 'products/' . substr($mainImg, strlen('product/'));
    }

    return [
        'type' => 'water-treatment',
        'id' => 0,
        'name' => $name,
        'slug' => $slug,
        'filtr' => $name,
        'opis' => (string)($entry['описание'] ?? ''),
        'main_img' => $mainImg,
        'table_rows' => [
            ['label' => 'температура воздуха, °С', 'value' => (string)($entry['температура воздуха, °С'] ?? '')],
            ['label' => 'относительная влажность при 20° С', 'value' => (string)($entry['относительная влажность при 20° С'] ?? '')],
            ['label' => 'давление воды, устанавливаемое регулятором, кгс/см', 'value' => ''],
            ['label' => 'при работе с медикатором', 'value' => (string)($entry['при работе с медикатором'] ?? '')],
            ['label' => 'при работе без медикатора', 'value' => (string)($entry['при работе без медикатора'] ?? '')],
            ['label' => 'габаритные размеры системы водоподготовки, мм, не более', 'value' => (string)($entry['габаритные размеры системы водоподготовки, мм, не более'] ?? '')],
        ],
    ];
}
