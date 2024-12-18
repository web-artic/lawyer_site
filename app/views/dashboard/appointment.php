<?php
session_start();

require __DIR__ . '/../../../vendor/autoload.php'; 
include __DIR__ .'/../../core/db.php';
require_once __DIR__ .'/../../../storage/logs/error_log.php';
include __DIR__ .'/../../helpers/auth.php';
include __DIR__ .'/../../helpers/validate.php'; 
include __DIR__ .'/../../helpers/relevance.php'; 
include __DIR__ .'/../../helpers/relevance4.php';
include __DIR__ .'/../../helpers/cookie.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (getUserSessionRole() !== 'client') {
    header('Location: /public/index.php');
    exit();
}

$userId = getUserIdSession();
$errors = ['name' => '', 'phone' => '', 'service' => ''];
$hasErrors = false;
$encryptionKey = getenv('ENCRYPTION_KEY');


$cacheKey = 'client_user_' . $userId;


if ($_SERVER['REQUEST_METHOD'] === 'POST' &&  $_POST['form_type'] === 'update_info_client') {

    $name = trim($_POST['name']);
    $name = preg_replace('/\s{2,}/', ' ', $name); // Заменяем два и более пробелов на один пробел
    $phone = trim($_POST['phone']);
    $service = $_POST['service'];

    // Проверка ФИО
    $error = validateName($name);
    if ($error) {
        $errors['name'] = $error;
        $hasErrors = true;
    }

    // Проверка телефона
    $error = validatePhone($phone);
    if ($error) {
        $errors['phone'] = $error;
        $hasErrors = true;
    }

    if (!array_filter($errors)) {
    $sql = "UPDATE clients SET name = :name, phone = :phone, service = :service WHERE user_id = :id";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
            'service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8'),
            'id' => getUserIdSession()
        ]);

        /*

        $cacheKey = 'client_user_' . getUserIdSession();
        $redis->del($cacheKey); // Удаляем старые данные из кеша

        $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
        $stmtClient = $pdo->prepare($sqlClient);
        $stmtClient->execute(['user_id' => getUserIdSession()]);
        $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

        $redis->set($cacheKey, serialize($client), 3600); // Кешируем на 1 час

        */

        header("Location: /app/views/dashboard/appointment.php?success-info");
    } catch (PDOException $e) {
        logError("Ошибка обновления клиента: " . $e->getMessage());
        echo "Ошибка обновления клиента. Попробуйте позже.";
    }
    }
}

try {

    // БЕЗ КЭШИРОВАНИЯ
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
    $stmtClient = $pdo->prepare($sqlClient);
    $stmtClient->execute(['user_id' => $userId]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $endMemory = memory_get_usage();
    $memoryUsed = $endMemory - $startMemory;

    $time_optimization [] = '';
    
    /*
$time_optimization [] = "Время выполнения запроса без кеша: " . ($executionTime * 1000) . " мс.  ";
$time_optimization [] = "Объем памяти, использованный при запросе: " . ($memoryUsed / 1024) . " Кбайт.";


// С КЭШИРОВАНИЯ

$startTime = microtime(true);
$startMemory = memory_get_usage();

$clientCache = $redis->get($cacheKey);

if ($clientCache === false) {

    $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
    $stmtClient = $pdo->prepare($sqlClient);
    $stmtClient->execute(['user_id' => $userId]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    $redis->set($cacheKey, serialize($client), 3600); // Кеш на 1 час
} else {
    $client = unserialize($clientCache);
}

$endTime = microtime(true);
$executionTime = $endTime - $startTime;
$endMemory = memory_get_usage();
$memoryUsed = $endMemory - $startMemory;

$time_optimization [] = "Время выполнения запроса с кешированием: " . ($executionTime * 1000) . " мс.";
$time_optimization [] = "Объем памяти, использованный при запросе: " . ($memoryUsed / 1024) . " Кбайт.";

/*
    // API INSOMNIA ----------------------


// Проверка параметра запроса, который определяет, использовать ли кеш
$isCacheEnabled = isset($_GET['cache']) && $_GET['cache'] === 'true';

// БЕЗ КЭШИРОВАНИЯ
if (!$isCacheEnabled) {
    // Засекаем время и память
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    // Выполняем запрос к базе данных без кеширования
    $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
    $stmtClient = $pdo->prepare($sqlClient);
    $stmtClient->execute(['user_id' => $userId]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    // Засекаем время окончания и использование памяти
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $endMemory = memory_get_usage();
    $memoryUsed = $endMemory - $startMemory;

    // Добавляем в массив результаты
    $time_optimization[] = "Время выполнения запроса без кеша: " . ($executionTime * 1000) . " мс.";
    $time_optimization[] = "Объем памяти, использованный при запросе: " . ($memoryUsed / 1024) . " Кбайт.";
    $time_optimization[] = "Данные клиента (без кеша): " . json_encode($client);

} else {
    // С КЭШИРОВАНИЕМ

    // Засекаем время и память
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    // Проверяем, есть ли данные в кеше
    $clientCache = $redis->get($cacheKey);

    if ($clientCache === false) {
        // Если данных нет в кеше, выполняем запрос к базе данных
        $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
        $stmtClient = $pdo->prepare($sqlClient);
        $stmtClient->execute(['user_id' => $userId]);
        $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

        // Кешируем результат на 1 час
        $redis->set($cacheKey, serialize($client), 3600); // Кеш на 1 час
    } else {
        // Если данные есть в кеше, десериализуем их
        $client = unserialize($clientCache);
    }

    // Засекаем время окончания и использование памяти
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    $endMemory = memory_get_usage();
    $memoryUsed = $endMemory - $startMemory;

    // Добавляем в массив результаты
    $time_optimization[] = "Время выполнения запроса с кешированием: " . ($executionTime * 1000) . " мс.";
    $time_optimization[] = "Объем памяти, использованный при запросе: " . ($memoryUsed / 1024) . " Кбайт.";
    $time_optimization[] = "Данные клиента (с кешем): " . json_encode($client);
}

    // API INSOMNIA ----------------------

*/
    $clientId = $client['id']; 
    $clientService = $client['service'];

    $sqlAllAppointments = "SELECT id, date, lawyer_name, service, status, client_id, lawyer_id, relevance, cases_won FROM appointments"; // Выборка только нужных полей
    $stmtAllAppointments = $pdo->query($sqlAllAppointments);
    $appointments = $stmtAllAppointments->fetchAll(PDO::FETCH_ASSOC);

    $sqlClientAppointments = "SELECT * FROM appointments WHERE client_id = :client_id";
    $stmtClientAppointments = $pdo->prepare($sqlClientAppointments);
    $stmtClientAppointments->execute(['client_id' => $clientId]);
    $clientAppointments = $stmtClientAppointments->fetchAll(PDO::FETCH_ASSOC);

    relevanceAppointments4($appointments, $clientService, $clientAppointments, $pdo);
    // relevanceAppointments($appointments, $clientService, $clientAppointments);
    $clientAppointments = array_filter($clientAppointments, function ($appointment) {
        $appointmentDate = new DateTime($appointment['date']);
        $currentDate = new DateTime();
    
        return $appointmentDate >= $currentDate;
    });

    $lastSearch = isset($_COOKIE["last_search_$userId"]) ? decryptCookieValue($_COOKIE["last_search_$userId"], $encryptionKey) : ''; 
    $search = isset($_GET['search']) ? preg_replace('/\s{2,}/', ' ', trim($_GET['search'])) : $lastSearch;

    $error_search = '';
    try {
        if (strlen($search) > 255){
            $error_search = "Поисковый запрос слишком длинный. Укоротите запрос и попробуйте снова.";
            $appointments = [];
        } elseif ($search) {
            $sql = "SELECT * FROM appointments WHERE lawyer_name LIKE :search OR service LIKE :search";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => "%$search%"]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($search !== $lastSearch){
                $encryptedSearch = encryptCookieValue($search, $encryptionKey);
                setcookie("last_search_$userId", $encryptedSearch, time() + 86400 * 30, '/', false, true);
            }

            relevanceAppointments4($appointments, $clientService, $clientAppointments, $pdo);
        } else{
            if ($search !== $lastSearch){
                $encryptedSearch = encryptCookieValue($search, $encryptionKey);
                setcookie("last_search_$userId", $encryptedSearch, time() + 86400 * 30, '/', false, true);
            }
        }
    } catch (PDOException $e) {
        logError("Ошибка при выполнении запроса: " . $e->getMessage());
        $error_search = "Произошла ошибка при загрузке данных.";
        $appointments = [];
    }
} catch (Exception $e) {
    logError("Ошибка: " . $e->getMessage());
    $appointments = [];
}

$errors_image = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'upload_client_photo') {
    $errors_image = validateImage($_FILES['client_photo']);

    if (!$errors_image) {
        $errors_image = [];
        try{
            $photoData = file_get_contents($_FILES['client_photo']['tmp_name']);
            $sql = "UPDATE clients SET photo = :photo WHERE user_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['photo' => $photoData, 'id' => $userId]);

            /*
            $cacheKey = 'client_user_' . getUserIdSession();
            $redis->del($cacheKey); // Удаляем старые данные из кеша

            $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
            $stmtClient = $pdo->prepare($sqlClient);
            $stmtClient->execute(['user_id' => getUserIdSession()]);
            $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

            $redis->set($cacheKey, serialize($client), 3600); // Кешируем на 1 час
            */
            echo json_encode([
                'status' => 'success',
                'message' => 'Файл успешно загружен!' // Успешная загрузка
            ]);

            // header("Location: /app/views/dashboard/appointment.php");
            exit;

        } catch (PDOException $e) {
            logError("Ошибка выполнения SQL-запроса: " . $e->getMessage());
            $errors_image[] = "Ошибка при обновлении записи в базе данных. Пожалуйста, попробуйте позже.";
            echo json_encode([
                'status' => 'error',
                'message' => $errors_image
            ]);
            exit;
        }
    }
    else{
        echo json_encode([
            'status' => 'error',
            'errors' => $errors_image // Передаем массив ошибок
        ]);
        exit;
    }
}

$errors_load_image = [];
$errors_image_delete = [];
if ($client['photo']){
    $errors_check = [];

    $errors_check = validateLoadBlobImage($client['photo']);
    if ($errors_check) {
        $errors_load_image = array_merge($errors_load_image, $errors_check);
    }

    if ($errors_load_image) {
        $photoPath = DEFAULT_PHOTO_PATH;
    }
    else {
        $imageInfo = getimagesizefromstring($client['photo']);
        if ($imageInfo === false) {
            $errors_load_image[] = "Изображение повреждено или не поддерживается.";
            $photoPath = DEFAULT_PHOTO_PATH;
        } else {
            $mimeType = $imageInfo['mime']; // Получение MIME-типа
        }
        $photoPath = "data:$mimeType;base64," . base64_encode($client['photo']);
    }
}
else{
    $photoPath = DEFAULT_PHOTO_PATH;
}

if ($photoPath === DEFAULT_PHOTO_PATH){ $errors_default_image = validateDefaultImage();}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'delete_client_photo') {
    if ($client['photo']){
        try {
        $sql = "UPDATE clients SET photo = NULL WHERE user_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);

        /*
        $cacheKey = 'client_user_' . getUserIdSession();
        $redis->del($cacheKey); // Удаляем старые данные из кеша

        $sqlClient = "SELECT * FROM clients WHERE user_id = :user_id";
        $stmtClient = $pdo->prepare($sqlClient);
        $stmtClient->execute(['user_id' => getUserIdSession()]);
        $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

        $redis->set($cacheKey, serialize($client), 3600); // Кешируем на 1 час

        */

        } 
        catch (PDOException $e) {
            logError("Ошибка выполнения SQL-запроса: " . $e->getMessage());
            $errors_image_delete[] = "Ошибка удаления из базы данных, попробуйте позже";
        } 

        header("Location: /app/views/dashboard/appointment.php");
        exit;
    }else {
        $errors_image_delete[] = "Поле ['photo'] не найдено в базе данных";
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
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
        <h1>Личный кабинет клиента</h1>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">Вы успешно записались на встречу!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error-message">Произошла ошибка. Попробуйте снова.</p>
        <?php endif; ?>

        <section>
            <h2>Ваши данные</h2>
            <div class="button-group-image-upload-container">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="form_type" value="upload_client_photo">
                    <input type="file" name="client_photo" id="clientPhoto" accept="image/*">
                    <?php if ($photoPath === DEFAULT_PHOTO_PATH): ?>
                        <button type="submit">Загрузить фото профиля</button>
                    <?php else: ?>
                        <button type="submit">Изменить фото профиля</button>
                    <?php endif; ?>
                </form>
                <?php if ($photoPath !== DEFAULT_PHOTO_PATH): ?>
                <form method="POST" class="delete-form">
                    <input type="hidden" name="form_type" value="delete_client_photo">
                    <button type="submit" class="delete-btn">Удалить фото профиля</button>
                </form>
                <?php endif; ?>
            </div>

            <?php foreach ($errors_image as $error): ?>
            <p class='error-ulode-file'><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
            <?php foreach ($errors_load_image as $error): ?>
            <p class='error-ulode-file'><?= htmlspecialchars($error) ?></p>
            <?php endforeach;?>
            <?php foreach ($errors_image_delete as $error): ?>
            <p class='error-ulode-file'><?= htmlspecialchars($error) ?></p>
            <?php endforeach;?>

            <!-- Контейнер для ошибок загрузки файла для JS-->
            <div class='error-ulode-file' id="error-container"></div>

            <?php foreach ($time_optimization as $time): ?>
            <?php if ($time): ?>
            <p><?= htmlspecialchars($time) ?></p>
            <?php endif; ?>
            <?php endforeach;?>

            <table class="table-type2">
                <tr>
                    <th>Фото</th>
                    <td>
                        <?php if (!empty($errors_default_image)): ?>
                            <?php foreach ($errors_default_image as $error): ?>
                            <p class='errors_default_image'><?= htmlspecialchars($error) ?></p>
                            <?php endforeach;?>
                        <?php else:?>
                        <img src="<?= htmlspecialchars($photoPath); ?>" alt="Фото">
                        <?php endif;?>
                    </td>
                </tr>
                <tr><th>Имя</th><td><?= htmlspecialchars($client['name']); ?></td></tr>
                <tr><th>Email</th><td><?= htmlspecialchars($client['email']); ?></td></tr>
                <tr><th>Телефон</th><td><?= htmlspecialchars($client['phone']); ?></td></tr>
                <tr><th>Услуга</th><td><?= htmlspecialchars($client['service'] ?? ''); ?></td></tr>
            </table>
            <button id="editClientData1">Редактировать данные</button>
            <?php if (isset($_GET['success-info'])): ?>
                <p class="success-message">Данные отредактированы</p>
            <?php endif; ?>
        </section>

        <div id="editClientModal1" class="update-info-client-modal">
            <div class="update-info-client-modal-content">
                <span class="close">&times;</span>
                <h2>Редактировать данные</h2>
                <form method="POST">
                    <input type="hidden" name="form_type" value="update_info_client">

                    <label for="name">ФИО:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($client['name']) ?>" required>
                    <small class="error"><?= $errors['name'] ?></small>

                    <label for="phone">Телефон:</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($client['phone']) ?>" required>
                    <small class="error"><?= $errors['phone'] ?></small>

                    <label for="service">Какая услуга адвоката потребуется?</label>
                    <select id="service" name="service" required>
                        <option value="">Выберите услугу</option>
                        <option value="Уголовное дело" <?= htmlspecialchars($client['service']) === 'Уголовное дело' ? 'selected' : '' ?>>Уголовное дело</option>
                        <option value="Гражданское дело" <?= htmlspecialchars($client['service']) === 'Гражданское дело' ? 'selected' : '' ?>>Гражданское дело</option>
                        <option value="Семейное дело" <?= htmlspecialchars($client['service']) === 'Семейное дело' ? 'selected' : '' ?>>Семейное дело</option>
                        <option value="Административное дело" <?= htmlspecialchars($client['service']) === 'Административное дело' ? 'selected' : '' ?>>Административное дело</option>
                    </select>
                    <small class="error"><?= $errors['service'] ?></small>

                    <button type="submit" id="submitButton">Изменить</button>

                    <div id="hasErrors" data-errors="<?= $hasErrors ? '1' : '0' ?>" style="display: none;"></div>
                </form>
            </div>
        </div>

        <h2>Ваши записи к адвокатам</h2>
        <section>
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Адвокат</th>
                        <th>Услуга</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientAppointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['date']); ?></td>
                            <td><?= htmlspecialchars($appointment['lawyer_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['service']); ?></td>
                            <td>
                            <form action="/public/book_appointment.php" method="POST" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                <input type="hidden" name="client_id" value="<?= getUserIdSession() ?>"> <!-- ID клиента -->
                                <button type="submit" class="btn-book">Отменить</button>
                            </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <h2>Все записи к адвокатам</h2>

        <form action="" method="GET" class="search-form">
        <label for="search">Поиск по параметрам:</label>
        <input type="text" id="search" name="search" placeholder="Введите адвоката или услугу" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Поиск</button>

        <?php if ($error_search): ?>
            <p class='error-ulode-file'><?= htmlspecialchars($error_search) ?></p>
        <?php endif;?>

        </form>
        <section>
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Адвокат</th>
                        <th>Услуга</th>
                        <th>Кол-во выйгранных дел</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['date']); ?></td>
                            <td><?= htmlspecialchars($appointment['lawyer_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['service']); ?></td>
                            <td><?= htmlspecialchars($appointment['cases_won']); ?></td>
                            <td>
                                <?php if ($appointment['status'] === 'свободно'): ?>
                                    <form action="/public/book_appointment.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                        <input type="hidden" name="client_id" value="<?= htmlspecialchars(getUserIdSession()) ?>">
                                        <button type="submit" class="btn-book" <?php if ($appointment['service'] !== $clientService) : ?>onclick="return confirm('У вас выбрана услуга (<?= htmlspecialchars ($clientService) ?>), вы уверены, что хотите забронировать запись на (<?= htmlspecialchars ($appointment['service']) ?>)?')"<?php endif; ?>>Записаться</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status-busy">Занято</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
    <script src="/public/js/exit-session.js"></script>
    <script src="/public/js/update_client.js"></script>

    <script>
    document.querySelector('.upload-form').addEventListener('submit', function (event) {
    event.preventDefault(); 

    const formData = new FormData(this);
    const errorContainer = document.getElementById('error-container'); 
    errorContainer.innerHTML = ''; 

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) 
    .then(data => {
        if (data.status === 'success') {
            errorContainer.innerHTML = '';
            location.reload(); // Перезагружаем страницу после успешной загрузки
        } else {
            if (data.errors && Array.isArray(data.errors)) {
                data.errors.forEach(error => {
                    const errorMessage = document.createElement('p');
                    errorMessage.textContent = error;
                    errorContainer.appendChild(errorMessage); 
                });
            } else {
                errorContainer.textContent = data.message; 
            }
        }
    })
    .catch(error => {
        alert('Произошла ошибка при загрузке файла. Попробуйте снова.');
    });
});
</script>




</body>
</html>
