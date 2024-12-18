<?php
session_start();

include __DIR__ .'/../app/core/db.php';
require_once __DIR__ .'/../storage/logs/error_log.php';
include __DIR__ .'/../app/helpers/validate.php'; 
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
include __DIR__ .'/../app/helpers/auth.php';

use App\models\User;
use App\controllers\AuthController;

$db = $pdo;
$userModel = new User($db);
$authController = new AuthController($userModel);

$action = $_GET['action'] ?? '';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'form_login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $error = $authController->login($username, $password);
    if ($error) echo $error;
} elseif ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'form_reg') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $message = $authController->register($username, $password);
    echo $message;
} elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'logout') {
    $authController->logout();
} 

$errors = ['name' => '', 'email' => '', 'phone' => '', 'service' => ''];
$status = isset($_GET['status']) && $_GET['status'] === 'success' ? 'success' : 'error'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_type'] === 'form_client') {
    $name = trim($_POST['name']);
    $name = preg_replace('/\s{2,}/', ' ', $name);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $service = $_POST['service'];

    // Проверка ФИО
    $error = validateName($name);
    if ($error) {
        $errors['name'] = $error;
    }

    // Проверка телефона
    $error = validatePhone($phone);
    if ($error) {
        $errors['phone'] = $error;
    }
    
    // Проверка email
    $error = validateEmail($email);
    if ($error) {
        $errors['email'] = $error;
    }
    else {
        $error = validateuniqueEmail($email, $pdo);
        if ($error) {
            $errors['email'] = $error;
        }    
    }

    if (!array_filter($errors)) {
        $sql = "INSERT INTO clients_help (name, email, phone, service) VALUES (:name, :email, :phone, :service)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
                'service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8')
            ]);
            header("Location: /public/index.php?status=success"); 
            exit;
        } catch (PDOException $e) {
            // Логируем ошибку выполнения SQL-запроса
            logError("Ошибка выполнения SQL-запроса: " . $e->getMessage());
            $status = "error";
            $errors['general'] = "Ошибка при добавлении клиента. Пожалуйста, попробуйте позже.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Юридический портал</title>
        <link rel="stylesheet" href="/public/css/style.css"> 
        <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        h2{
            text-align: center;
        }

        section {
            padding: 20px;
            margin: 20px 0;
            margin-bottom: 40px;
        }
        .services {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .service {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            width: calc(25% - 20px);
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .service:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .service h3 {
            margin: 10px 0;
            color: #004085;
        }
    </style>
    </head>
    <body>
        <header>
            <nav>
                <ul>
                    <li><a href="#">Главная</a></li>
                    <?php if (getUserSessionRole() === 'client'):?>
                    <li><a href="/app/views/dashboard/client.php">Личный кабинет</a></li>
                    <?php endif; ?>
                    <?php if (getUserSessionRole() === 'lawyer'):?>
                    <li><a href="/app/views/dashboard/lawyer.php">Личный кабинет</a></li>
                    <?php endif; ?>
                    <?php if (getUserSessionRole() === 'admin'):?>
                    <li><a href="/app/views/dashboard/admin.php">Личный кабинет</a></li>
                    <?php endif; ?>
                </ul>
                <?php if (getUserSessionRole() === 'client' || getUserSessionRole() === 'lawyer' || getUserSessionRole() === 'admin'): ?>
                    <div class="auth-buttons">
                        <button id="exitauth" class="exit-button">Выход</button>
                    </div>
                    <!-- Скрытая форма для выхода -->
                    <form id="logoutForm" action="/public/index.php?action=logout" method="POST" style="display:none;">
                        <input type="hidden" name="form_type" value="logout">
                    </form>
                <?php else: ?> 
                    <div class="auth-buttons">
                        <button id="loginBtn" class="auth-button">Вход</button>
                        <button id="registrBtn" class="auth-button">Регистрация</button>
                    </div>
                <?php endif; ?>
                <?php require_once __DIR__ . '/../app/views/auth/login.php'?>
                <?php require_once __DIR__ . '/../app/views/auth/register.php'?>
            </nav>
        </header>

        <main>

            <section id="services">
                <h2>Наши услуги</h2>
                <div class="services">
                    <div class="service">
                        <h3>Уголовное дело</h3>
                        <p>Консультации и защита по уголовным делам любой сложности.</p>
                    </div>
                    <div class="service">
                        <h3>Гражданское дело</h3>
                        <p>Решение споров в гражданских делах, помощь в составлении договоров.</p>
                    </div>
                    <div class="service">
                        <h3>Семейное дело</h3>
                        <p>Помощь в вопросах развода, раздела имущества и опеки над детьми.</p>
                    </div>
                    <div class="service">
                        <h3>Административное дело</h3>
                        <p>Представление интересов в административных органах и судах.</p>
                    </div>
                </div>
            </section>

            <h2>Оставить заявку</h2>
            <?php require_once __DIR__ . '/../app/views/forms/add_client.php'?>
        </main>

        <!-- Подключение скриптов -->
        <script src="/public/js/service.js"></script>
        <script src="/public/js/modal-auth.js"></script>
        <script src="/public/js/exit-session.js"></script>
    </body>
    <footer>
            <p>&copy; 2024 Адвокатская компания</p>
    </footer>
</html>
