<?php
require_once __DIR__ . '/../../storage/logs/error_log.php';

$config = require __DIR__ . '/../../config/config.php';
$dbConfig = $config['db'];
 //$redisConfig = $config['redis'];

global $pdo;
//global $redis;

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    logError("Ошибка подключения к базе данных: " . $e->getMessage());
    die("Не удалось подключиться к базе данных. Пожалуйста, попробуйте позже.");
}


try {
    //$redis = new Redis();
   // $redis->connect($redisConfig['host'], $redisConfig['port']);
    
} catch (Exception $e) {
    logError("Ошибка подключения к Redis: " . $e->getMessage());
    die("Не удалось подключиться к Redis. Пожалуйста, попробуйте позже.");
}

?>
