<?php
require __DIR__ . '/autoload.php';
use App\Classes\Db;
use App\Classes\Upwork;

$configDb = json_decode(file_get_contents(__DIR__ . '/config.json'));
$db = new Db($configDb);

$result['data'] = Upwork::findAll($db);

foreach ($result['data'] as $value) {
    $created_at['display'] = date('d-m-Y H:i:s', $value->created_at);
    $created_at['timestamp'] = $value->created_at;
    $value->created_at = $created_at;

    $rating['display'] = $value->rating;
    $rating['sorting'] = $value->rating;
    $value->rating = $rating;
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);