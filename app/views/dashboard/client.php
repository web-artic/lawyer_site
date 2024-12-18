<?php
session_start();

include __DIR__ .'/../../core/db.php';
require_once __DIR__ .'/../../../storage/logs/error_log.php';
include __DIR__ .'/../../helpers/auth.php';
include __DIR__ .'/../../helpers/validate.php'; 

if (getUserSessionRole() !== 'client') {
    header('Location: /public/index.php');
    exit();
}

$sql = "SELECT * FROM CLIENTS WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => getUserIdSession()]);
$examle = $stmt->fetch(PDO::FETCH_ASSOC);

if ($examle) {
    header("Location: /app/views/dashboard/appointment.php");
}

$errors = ['name' => '', 'email' => '', 'phone' => '', 'service' => '','general' => ''];
$status = 'error';
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
        try {
            $sql = "INSERT INTO clients (user_id, name, email, phone, service) VALUES (:id, :name, :email, :phone, :service)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
                'service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8'),
                'id' => getUserIdSession()
            ]);
            header("Location: /app/views/dashboard/appointment.php"); 
            exit;
        } catch (PDOException $e) {
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
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/public">Главная</a></li>
            </ul>
            <?php if (getUserSessionRole() === 'client'): ?>
                <div class="auth-buttons">
                    <button id="exitauth" class="exit-button">Выход</button>
                </div>
                <!-- Скрытая форма для выхода -->
                <form id="logoutForm" action="/public/index.php?action=logout" method="POST" style="display:none;">
                    <input type="hidden" name="form_type" value="logout">
                </form>
            <?php else: 
                header("Location: /public/index.php");
            ?> 
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Дополните информацию о себе</h2>
        <?php require_once __DIR__ . '/../forms/add_client.php'?>
    </main>

    <footer>
        <p>&copy; 2024 Адвокатская компания</p>
    </footer>

    <script src="/public/js/service.js"></script>
    <script src="/public/js/exit-session.js"></script>
</body>
</html>
