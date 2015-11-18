<?php
require __DIR__ . '/autoload.php';
use App\Classes\Upwork;
use App\Classes\Db;

$configDb = json_decode(file_get_contents(__DIR__ . '/config.json'));
$db = new Db($configDb);
$result = Upwork::findAll($db);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
