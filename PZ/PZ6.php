<?php

require __DIR__ . '/../vendor/autoload.php'; 

$mongoClient = new MongoDB\Client("mongodb://localhost:27017"); 
$database = $mongoClient->selectDatabase('PZ'); 
$filesCollection = $database->files;

$gridFS = $database->selectGridFSBucket(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadedFileName = $_FILES['file']['name']; 
    $uploadedFilePath = $_FILES['file']['tmp_name']; 

    $stream = fopen($uploadedFilePath, 'r'); 
    if ($stream) {

        $fileId = $gridFS->uploadFromStream($uploadedFileName, $stream);
        fclose($stream); 

        $filesCollection->insertOne([
            'filename' => $uploadedFileName, 
            'file_id' => $fileId,             
            'upload_date' => new MongoDB\BSON\UTCDateTime() 
        ]);

        echo "<p>Файл '{$uploadedFileName}' был успешно загружен в MongoDB с ID: {$fileId}</p>";
    } else {
        echo "<p>Ошибка при открытии файла для загрузки.</p>";
    }
}

if (isset($_GET['delete'])) {
    $fileId = $_GET['delete']; 
    $fileObjectId = new MongoDB\BSON\ObjectId($fileId); 

    $gridFS->delete($fileObjectId);
    $filesCollection->deleteOne(['file_id' => $fileObjectId]);

    echo "<p>Файл с ID {$fileId} был удален.</p>";
}
$files = $filesCollection->find();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление файлами</title>
    <link rel="stylesheet" href="/public/css/style1.css">
</head>
<body>
    <h1>Загрузка и управление файлами</h1>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Загрузить файл</button>
    </form>

    <h2>Список файлов</h2>
    <?php if ($files->isDead()): ?>
        <p>Файлов нет.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Название документа</th>
                <th>Действие</th>
                <th>Удалить</th>
            </tr>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td><?= htmlspecialchars($file['filename']) ?></td>
                    <td>
                        <a href="http://localhost/PZ/PZ4/<?= $file['filename'] ?>" target="_blank">Открыть</a>
                    </td>
                    <td>
                        <a href="?delete=<?= $file['file_id'] ?>" onclick="return confirm('Вы уверены, что хотите удалить этот файл?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
