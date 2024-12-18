<?php
session_start();

include __DIR__ .'/../../core/db.php';
require_once __DIR__ .'/../../../storage/logs/error_log.php';
include __DIR__ .'/../../helpers/auth.php';
include __DIR__ .'/../../helpers/validate.php'; 

$errors = ['date' => ''];
$errors_update = ['date' => ''];
$hasErrors = false;
$hasErrorsUpdate = false;

$action = $_GET['action'] ?? '';
$searchDate = $_GET['search_date'] ?? ''; 

if (getUserSessionRole() !== 'lawyer') {
    header('Location: /public/index.php');
    exit();
}

try {
    if (getLawyerId()){
        $lawyerId = getLawyerId();
    }
    else {
        echo "Вас не добавили на сайт";
        exit;
    }

    $lawyerSessionId = getUserIdSession();

    $sqlLawyer = "SELECT * FROM lawyers WHERE id = :id";
    $stmtLawyer = $pdo->prepare($sqlLawyer);
    $stmtLawyer->execute(['id' => $lawyerId]);
    $lawyer = $stmtLawyer->fetch(PDO::FETCH_ASSOC);

    $sqlClientAppointments = "SELECT * FROM appointments WHERE lawyer_id = :lawyer_id AND client_id IS NOT NULL";
    $stmtClientAppointments = $pdo->prepare($sqlClientAppointments);
    $stmtClientAppointments->execute(['lawyer_id' => $lawyerId]);
    $clientAppointments = $stmtClientAppointments->fetchAll(PDO::FETCH_ASSOC);

    if ($searchDate) {
        $sqlLawyerSessions = "SELECT * FROM appointments WHERE lawyer_id = :lawyer_id AND date LIKE :search_date";
        $stmtLawyerSessions = $pdo->prepare($sqlLawyerSessions);
        $stmtLawyerSessions->execute(['lawyer_id' => $lawyerId, 'search_date' => $searchDate . '%']);
    } else {
        $sqlLawyerSessions = "SELECT * FROM appointments WHERE lawyer_id = :lawyer_id";
        $stmtLawyerSessions = $pdo->prepare($sqlLawyerSessions);
        $stmtLawyerSessions->execute(['lawyer_id' => $lawyerId]);
    }

    $lawyerSessions = $stmtLawyerSessions->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logError("Ошибка: " . $e->getMessage());
    $lawyer = [];
    $clientAppointments = [];
    $lawyerSessions = [];
}

// Проверка, был ли отправлен запрос
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'add_appointment' ) {

    $date = $_POST['date'];
    
    // Проверка email
    $error = isFutureDate($date);
    if ($error) {
        $errors['date'] = $error;
        $hasErrors = true;
    }
    else {
        $error = isConflictingAppointment($date, $lawyerId, $pdo);
        if ($error) {
            $errors['date'] = $error;
            $hasErrors = true;
        }    
    }

    if (!array_filter($errors)){
    $sql = "INSERT INTO appointments (date, lawyer_name, service, status, lawyer_id) VALUES (:date, :lawyer_name, :service, 'свободно', :lawyer_id)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute(['date' => $date, 'lawyer_name' => getLawyerName(), 'service' => getLawyerService(), 'lawyer_id' => $lawyerId]);
        header("Location: /app/views/dashboard/lawyer.php");
    } catch (PDOException $e) {
        logError("Ошибка добавления сеанса: " . $e->getMessage());
        echo "Ошибка добавления сеанса. Попробуйте позже.";
    }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($_POST['form_type'] === 'update_appointment') {
        
        // Получаем appointment_id из POST
        if (isset($_POST['appointment_id'])) {
            $appointment_id = $_POST['appointment_id'];  // Переданное значение ID
        } else {
            echo "Ошибка: Не передан ID записи.<br>";
            exit();
        }

        $date = $_POST['date'];

        // Проверка даты
        $error = isFutureDate($date);
        if ($error) {
            $errors_update['date'] = $error;
            $hasErrorsUpdate = true;
        }
        else {
            $error = isConflictingAppointment($date, $lawyerId, $pdo);
            if ($error) {
                $errors_update['date'] = $error;
                $hasErrorsUpdate = true;
            }    
        }

        // Если ошибок нет, обновляем запись
        if (!array_filter($errors_update)){
            $sql = "UPDATE appointments SET date = :date WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute(['date' => $date, 'id' => $appointment_id]);
                header("Location: /app/views/dashboard/lawyer.php");
            } catch (PDOException $e) {
                logError("Ошибка обновления сеанса: " . $e->getMessage());
                echo "Ошибка обновления сеанса. Попробуйте позже.";
            }
        }
    }
}

//  -----------------------------         5 лаба        ----------------------------------------------------
///*
$baseDir = __DIR__ .'/../../../';
$errors_image = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'upload_lawyer_photo') {

    $errors_image = validateImage($_FILES['lawyer_photo']);

    if (!$errors_image) {
        $errors_image = [];
        $fileExtension = strtolower(pathinfo($_FILES['lawyer_photo']['name'], PATHINFO_EXTENSION));
        $uniqueFileName = uniqid('lawyer_', true) . '.' . $fileExtension;
        $photoPath = '/public/images/' . $uniqueFileName;
        $photoPathLoad = $baseDir . $photoPath;

        while (file_exists($photoPathLoad)) {
            $uniqueFileName = uniqid('lawyer_', true) . '.' . $fileExtension;
            $photoPath = '/public/images/' . $uniqueFileName;
            $photoPathLoad = $baseDir . $photoPath;
        }

        if (file_exists(dirname($photoPathLoad))) {
            if (!is_dir(dirname($photoPathLoad))) {
                $errors_image[] = "На сервере существует файл с именем, совпадающим с названием папки для загрузки изображений!";
            } else if (!is_writable(dirname($photoPathLoad))) {
                $errors_image[] = "Нет прав на запись в папку для загрузки изображений!";
            } else if (@move_uploaded_file($_FILES['lawyer_photo']['tmp_name'], $photoPathLoad)) {
                try {
                    $sql = "UPDATE lawyers SET photo_path = :photo_path WHERE user_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['photo_path' => $photoPath, 'id' => $lawyerSessionId]);

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Файл успешно загружен!' // Успешная загрузка
                    ]);
        
                    // header("Location: /app/views/dashboard/lawyer.php");
                    exit;
                } catch (PDOException $e) {
                    // Логируем ошибку
                    logError("Ошибка выполнения SQL-запроса: " . $e->getMessage());
                    $errors_image[] = "Ошибка при обновлении записи в базе данных. Пожалуйста, попробуйте позже.";
                }
            } else {
                $errors_image[] = "Ошибка при загрузке изображения!";
            }
            if ($errors_image){
                echo json_encode([
                    'status' => 'error',
                    'errors' => $errors_image // Передаем массив ошибок
                ]);
                exit;
            }
        } else {
            $errors_image[] = "Папки для загрузки изображений не существует на сервере! Попробуйте позже.";
            echo json_encode([
                'status' => 'error',
                'errors' => $errors_image // Передаем массив ошибок
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
if ($lawyer['photo_path']){
    $photoPath = $baseDir . $lawyer['photo_path'];
    if (file_exists(dirname($photoPath))) {
        if (!is_dir(dirname($photoPath))) {
            $errors_load_image[] = "На сервере существует файл с именем, совпадающим с названием папки для загрузки изображений!";
        } else if (!is_readable(dirname($photoPath))) {
            $errors_load_image[] = "Нет прав для взаимодействия с папкой для загрузки изображений!";
        } else if (!file_exists($photoPath)) {
            $errors_load_image[] = "Фото адвоката не найдено на сервере!";
        } else if (is_dir($photoPath)) {
            $errors_load_image[] = "На сервере существует папка с именем, совпадающим с названием изображения!";
        } else if (!is_readable($photoPath)) {
            $errors_load_image[] = "Изображение недоступно для чтения!";
        } else {
            $errors_check = [];
            $errors_check = validateLoadImage($photoPath);
            if (!empty($errors_check)) {
                $errors_load_image = array_merge($errors_load_image, $errors_check);
            }
        } 
    } else {
        $errors_load_image[] = "Папки для загрузки изображений не существует на сервере! Попробуйте позже.";
    }
    if ($errors_load_image) {
        $photoPath = DEFAULT_PHOTO_PATH;
    }
    else {
        $photoPath = $lawyer['photo_path'];
    }
}
else{
    $photoPath = DEFAULT_PHOTO_PATH;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'delete_lawyer_photo') {
    if ($lawyer['photo_path']){
        $photoPath = $baseDir . $lawyer['photo_path'];

        if (file_exists(dirname($photoPath))) {
            if (!is_dir(dirname($photoPath))) {
                $errors_image_delete[] = "Удаление невозможно! На сервере существует файл с именем, совпадающим с названием папки, где хранится изображение!";
            } else if (!is_writable(dirname($photoPath)) || !is_readable(dirname($photoPath))) {
                $errors_image_delete[] = "Удаление невозможно! Нет прав для взаимодействия с папкой, где хранится изображение!";
            } /* else if (!file_exists($photoPath)) {
                $errors_image_delete[] = "Удаление невозможно! Фото адвоката не найдено на сервере!";
            } */ 
            else if (is_dir($photoPath)) {
                $errors_image_delete[] = "Удаление невозможно! На сервере существует папка с именем, совпадающим с названием изображения!";
            } else if ((!is_readable($photoPath) || !is_writable($photoPath)) && file_exists($photoPath)) {
                $errors_image_delete[] = "Удаление невозможно! Нет прав для взаимодействия с изображением!";
            } else{
                unlink($photoPath);
            }
        } else {
            $errors_image_delete[] = "Удаление невозможно! Папка, где хранится изображение, не существует на сервере! Попробуйте позже.";
        }
        if (!$errors_image_delete){
            try {
            $sql = "UPDATE lawyers SET photo_path = NULL WHERE user_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $lawyerSessionId]);

            header("Location: /app/views/dashboard/lawyer.php");
            exit;
            } 
            catch (PDOException $e) {
                logError("Ошибка выполнения SQL-запроса: " . $e->getMessage());
                $errors_image_delete[] = "Ошибка удаления из базы данных, попробуйте позже";
            }
        } 
        else{
            $photoPath = DEFAULT_PHOTO_PATH;
        }
    } else {
        $errors_image_delete[] = "Удаление невозможно! Поле ['photo_path'] не найдено в базе данных";
    }
}
if ($photoPath === DEFAULT_PHOTO_PATH){ $errors_default_image = validateDefaultImage();}

//*/
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет адвоката</title>
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
        <h1>Личный кабинет адвоката</h1>

        <!-- Данные адвоката -->
        <section>
            <h2>Информация о вас</h2>

            <div class="button-group-image-upload-container">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="form_type" value="upload_lawyer_photo">
                    <input type="file" name="lawyer_photo" id="lawyerPhoto" accept="image/*">
                    <?php if ($photoPath === DEFAULT_PHOTO_PATH): ?>
                        <button type="submit">Загрузить фото профиля</button>
                    <?php else: ?>
                        <button type="submit">Изменить фото профиля</button>
                    <?php endif; ?>
                </form>
                <?php if ($photoPath !== DEFAULT_PHOTO_PATH): ?>
                <form method="POST" class="delete-form">
                    <input type="hidden" name="form_type" value="delete_lawyer_photo">
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
                <tr><th>Имя</th><td><?= htmlspecialchars($lawyer['name']); ?></td></tr>
                <tr><th>Email</th><td><?= htmlspecialchars($lawyer['email']); ?></td></tr>
                <tr><th>Телефон</th><td><?= htmlspecialchars($lawyer['phone']); ?></td></tr>
                <tr><th>Специализация</th><td><?= htmlspecialchars($lawyer['specialization']); ?></td></tr>
            </table>
        </section>

        <section>
            <h2>Записи ваших клиентов</h2>
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Клиент</th>
                        <th>Услуга</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientAppointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['date']); ?></td>
                            <td><?= htmlspecialchars(getClientName($appointment['client_id'])); ?></td>
                            <td><?= htmlspecialchars($appointment['service']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <h2>Ваши сеансы</h2>

        <form method="GET" action="lawyer.php" class="search-form">
            <label for="search_date">Поиск по дате:</label>
            <input type="date" id="search_date" name="search_date" value="<?= htmlspecialchars($searchDate) ?>">
            <button type="submit">Найти</button>
        </form>

        <section>
            <button id="addAppointment1">Добавить новый сеанс</button>
            <?php include __DIR__ .'/../../../public/add_appointment.php'; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Услуга</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lawyerSessions as $session): ?>
                        <tr>
                            <td><?= htmlspecialchars($session['id']); ?></td>
                            <td><?= htmlspecialchars($session['date']); ?></td>
                            <td><?= htmlspecialchars($session['service']); ?></td>
                            <td><?= htmlspecialchars($session['status']); ?></td>

                            <td>
                                <!-- Кнопка для редактирования -->
                                <a class="btn-edit" style="display:inline;" onclick="openModal('<?= $session['id'] ?>', '<?= $session['date'] ?>')">Редактировать</a>

                                <form action="/public/delete_appointment.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="appointment_id2" value="<?= $session['id'] ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Один модальный элемент вне цикла -->
                    <div id="updateAppointmentModal" class="update-info-client-modal" style="display:none;">
                        <div class="update-info-client-modal-content">
                            <span class="close-update" onclick="closeModal()">&times;</span>
                            <h2>Изменить запись</h2>
                            <form method="POST" id="updateForm">
                                <input type="hidden" name="form_type" value="update_appointment">
                                <input type="hidden" name="appointment_id" id="modalAppointmentId">
                                
                                <label for="date">Дата:</label>
                                <input type="datetime-local" id="modalDate" name="date" required>
                                <small class="error"><?= $errors_update['date'] ?></small>

                                <button type="submit" id="submitButton">Сохранить</button>

                                <div id="hasErrorsUpdate" data-errors-update="<?= $hasErrorsUpdate ? '1' : '0' ?>" style="display: none;"></div>
                            </form>
                        </div>
                    </div>
                </tbody>
            </table>
        </section>
    </main>
    <script src="/public/js/exit-session.js"></script>
    <script src="/public/js/add_appointment.js"></script>
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
