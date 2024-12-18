<?php
session_start();

include __DIR__ .'/../../core/db.php';
require_once __DIR__ .'/../../../storage/logs/error_log.php';
include __DIR__ .'/../../helpers/auth.php';
include __DIR__ .'/../../helpers/validate.php'; 

$errors = ['name' => '', 'email' => '', 'phone' => '', 'specialization' => ''];
$hasErrors = false;

// Проверка роли пользователя
if (getUserSessionRole() !== 'admin') {
    header('Location: /public/index.php');
    exit();
}

// Обработка формы добавления адвоката
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'add_lawyer') {

    $name = trim($_POST['name']);
    $name = preg_replace('/\s{2,}/', ' ', $name);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = $_POST['specialization'];

    $error = validateName($name);
    if ($error) {
        $errors['name'] = $error;
    }

    $error = validatePhone($phone);
    if ($error) {
        $errors['phone'] = $error;
    }
    
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
        $sql = "INSERT INTO lawyers (name, email, phone, specialization) VALUES (:name, :email, :phone, :specialization)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'specialization' => $specialization]);
            header("Location: /app/views/dashboard/admin.php?success");
        } catch (PDOException $e) {
            logError("Ошибка добавления адвоката: " . $e->getMessage());
            echo "Ошибка добавления адвоката. Попробуйте позже.";
        }
    }
}

try {
    $stmt = $pdo->query("SELECT criterion, weight, func FROM ranking_weights");
    $weights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Ошибка выполнения запроса: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'update_weights') {
    foreach ($_POST['weights'] as $criterion => $weight) {
        $stmt = $pdo->prepare("UPDATE ranking_weights SET weight = :weight WHERE criterion = :criterion");
        $stmt->execute(['weight' => $weight, 'criterion' => $criterion]);
    }
    header('Location: /app/views/dashboard/admin.php?success-rel');
}
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Личный кабинет администратора</title>
        <link rel="stylesheet" href="/public/css/style.css">
    </head>
    <body>
        <header>
            <nav>
                <ul>
                    <li><a href="/public/index.php">Главная</a></li>
                    <div class="auth-buttons">
                        <button id="exitauth" class="exit-button">Выход</button>
                    </div>
                    <form id="logoutForm" action="/public/index.php?action=logout" method="POST" style="display:none;">
                        <input type="hidden" name="form_type" value="logout">
                    </form>
                </ul>
            </nav>
        </header>
        <main>
            <h1>Личный кабинет администратора</h1>
            <h2>Настройка весов ранжирования</h2>

            <form method="POST">
                <input type="hidden" name="form_type" value="update_weights">
                <table>
                    <thead>
                        <tr>
                            <th>Критерий</th>
                            <th>Вес</th>
                            <th>Фунция подсчёта релевантности</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($weights as $weight): ?>
                            <tr>
                                <td><?= htmlspecialchars($weight['criterion']) ?></td>
                                <td>
                                    <input type="number" name="weights[<?= htmlspecialchars($weight['criterion']) ?>]" 
                                        value="<?= htmlspecialchars($weight['weight']) ?>" style="width: 100px;"
                                        <?php if ($weight['criterion'] === "cases_won"):?>
                                            min="0" max="20" 
                                        <?php elseif($weight['criterion'] === "date_proximity"): ?>
                                            min="0" max="30"
                                        <?php else: ?>
                                            min="0" max="100"
                                        <?php endif; ?>
                                    >
                                </td>
                                <td><?= htmlspecialchars($weight['func']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Сохранить</button>
            </form>

            <?php if (isset($_GET['success-rel'])): ?>
                <p class="success-message">Весовые коэффициенты успешно обновлены!</p>
            <?php endif; ?>  
            
            <h2>Добавить нового адвоката</h2>
            <form method="POST" class="registration-form">
                <input type="hidden" name="form_type" value="add_lawyer">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" maxlength = "200" value="<?= htmlspecialchars($name ?? '') ?>" required>
                <small class="error"><?= $errors['name'] ?></small>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                <small class="error"><?= $errors['email'] ?></small>

                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required>
                <small class="error"><?= $errors['phone'] ?></small>

                <label for="specialization">Специализация:</label>
                <input type="text" id="specialization" name="specialization" value="<?= htmlspecialchars($specialization ?? '') ?>" required>
                <small class="error"><?= $errors['specialization'] ?></small>

                <button type="submit">Добавить адвоката</button>
            </form>

            <?php if (isset($_GET['success'])): ?>
                <p class="success-message">Адвокат успешно добавлен!</p>
            <?php endif; ?>
        </main>
        <script src="/public/js/exit-session.js"></script>
    </body>
    <footer>
        <p>&copy; 2024 Адвокатская компания</p>
    </footer>
</html>
