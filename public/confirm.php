<?php
include __DIR__ .'/../app/core/db.php';
require_once __DIR__ .'/../storage/logs/error_log.php';
include __DIR__ .'/../app/helpers/validate.php'; 
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
include __DIR__ .'/../app/helpers/auth.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Проверяем токен
    $stmt = $pdo->prepare("SELECT id FROM users WHERE confirmation_token = ? AND confirmed = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Подтверждаем пользователя
        $stmt = $pdo->prepare("UPDATE users SET confirmed = 1, confirmation_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        echo "Ваш аккаунт успешно подтверждён!";
    } else {
        echo "Неверный или устаревший токен.";
    }
} else {
    echo "Токен не указан.";
}
?>
