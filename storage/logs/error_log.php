<?php
$logFile = 'file:///C:/xampp/htdocs/lawyers_site/storage/logs/error.log';

/**
 * Функция для записи ошибок в лог с добавлением даты и времени
 * @param string $message - Сообщение об ошибке
 */

function logError($message) {
    global $logFile;

    // Подготовка сообщения с датой и временем
    date_default_timezone_set('Europe/Moscow'); // Установить временную зону на московское время
    $date = date('Y-m-d H:i:s');
    $fullMessage = "[{$date}] " . $message . PHP_EOL;

    // Запись ошибки в лог-файл
    error_log($fullMessage, 3, $logFile);

    // Проверка на количество строк в лог-файле и очистка, если нужно
    manageLogSize($logFile);
}

/*
 * Функция для управления размером лог-файла
 * Если в лог-файле больше 100 строк, удаляет первые 50
 * @param string $logFile - Путь к лог-файлу
 */

function manageLogSize($logFile) {
    // Считываем файл в массив строк
    $lines = file($logFile);

    // Если строк больше 100, удаляем первые 50
    if (count($lines) > 100) {
        $lines = array_slice($lines, 50); // Удаляем первые 50 строк
        file_put_contents($logFile, implode('', $lines)); // Записываем оставшиеся строки обратно
    }
}
?>