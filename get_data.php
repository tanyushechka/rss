<?php
require __DIR__ . '/autoload.php';
use App\Classes\Rss;
use App\Classes\Db;

$configDb = json_decode(file_get_contents(__DIR__ . '/config.json'));
$db = new Db($configDb);
$result = Rss::findAll($db);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
