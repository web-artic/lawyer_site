<?php
session_start();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/controllers/LawyerController.php';
require_once __DIR__ . '/../app/helpers/auth.php';

// Проверка роли пользователя: доступ только для роли boss
if (!isAuthorized() || getUserSessionRole() !== 'boss') {
    header('Location: /public/index.php');
    exit('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $specialization = $_POST['specialization'];

    $lawyer = new Lawyer($pdo);
    $lawyer->addLawyer($username, $password, $specialization);
    echo "Адвокат успешно добавлен.";
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить адвоката</title>
</head>
<body>
    <h2>Добавить нового адвоката</h2>
    <form method="POST">
        <label>Имя пользователя:</label>
        <input type="text" name="username" required>
        
        <label>Пароль:</label>
        <input type="password" name="password" required>
        
        <label>Специализация:</label>
        <input type="text" name="specialization" required>
        
        <button type="submit">Добавить</button>
    </form>
</body>
</html>
