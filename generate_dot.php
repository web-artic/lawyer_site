<?php
$directory = 'C:\\xampp\\htdocs\\lawyers_site'; // Путь к директории (с экранированием обратных слэшей)
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$dotFile = fopen("project_structure.dot", "w");

fwrite($dotFile, "digraph G {" . PHP_EOL); // Используем PHP_EOL для новой строки

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        // Получаем путь к файлу и заменяем обратные слэши на прямые
        $filePath = str_replace('\\', '/', $file->getRealPath());
        
        // Экранирование пути и добавление его в .dot файл
        $escapedFilePath = addslashes($filePath);
        fwrite($dotFile, '"' . $escapedFilePath . '" [label="' . basename($filePath) . '"];' . PHP_EOL); // Добавляем разрыв строки
    }
}

// Если нужно добавить минимальную связь:
$firstFile = null;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $firstFile = $file->getRealPath();
        break; // Выход из цикла, когда нашли первый файл
    }
}

if ($firstFile) {
    $firstFile = str_replace('\\', '/', $firstFile); // Преобразуем путь для корректной записи
    fwrite($dotFile, '"start" -> "' . addslashes($firstFile) . '";' . PHP_EOL);
}

fwrite($dotFile, "}" . PHP_EOL); // Закрываем граф

fclose($dotFile);

echo "DOT файл создан успешно!";
?>
