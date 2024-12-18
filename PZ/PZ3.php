<?php
$directory = 'C:/xampp/htdocs/lawyers_site/'; 


if (isset($_POST['file_name'])) {
    $file_name = $_POST['file_name'];
    $file_path = $directory . $file_name;

    if (file_exists($file_path)) {

        if (unlink($file_path)) {
            echo "Файл $file_name был успешно удалён.";
        } else {
            echo "Ошибка: не удалось удалить файл $file_name.";
        }
    } else {
        echo "Файл $file_name не существует.";
    }
}

$files = scandir($directory);
$files = array_diff($files, array('.', '..'));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление файлов</title>
</head>
<body>
<h2>Список файлов в директории:</h2>

<form method="post">
    <label for="file_name">Выберите файл для удаления:</label>
    <select name="file_name" id="file_name">
        <?php foreach ($files as $file): ?>
            <option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <input type="submit" value="Удалить файл">
</form>

</body>
</html>
