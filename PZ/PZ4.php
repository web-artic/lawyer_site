<?php

require __DIR__ . '/../vendor/autoload.php'; // Подключение autoload из Composer

use MongoDB\Client;

try {
    // Подключение к серверу MongoDB
    $client = new Client("mongodb://localhost:27017");

    echo "Соединение с MongoDB установлено!<br>";

    // Выбор базы данных
    $db = $client->selectDatabase('test_database');

    // Выбор коллекции
    $collection = $db->selectCollection('test_collection');

    echo "Работаем с базой данных: test_database и коллекцией: test_collection<br>";

    // Добавление документа в коллекцию
    $insertResult = $collection->insertOne([
        'name' => 'John Doe',
        'age' => 29,
        'email' => 'john.doe@example.com'
    ]);

    echo "Добавлен документ с ID: " . $insertResult->getInsertedId() . "<br>";

    // Чтение документов из коллекции
    $documents = $collection->find();

    echo "Документы в коллекции:<br>";
    foreach ($documents as $doc) {
        echo json_encode($doc) . "<br>";
    }

} catch (Exception $e) {
    // Обработка ошибок
    echo "Ошибка подключения к MongoDB: " . $e->getMessage();
}