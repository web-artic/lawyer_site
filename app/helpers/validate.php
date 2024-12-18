<?php
include __DIR__ .'/../core/db.php';
require_once __DIR__ .'/../../storage/logs/error_log.php';
/**
 * Проверка номера телефона на корректность
 *
 * @param string $phone Телефонный номер для проверки
 * @return string|null Возвращает сообщение об ошибке, если проверка не пройдена; иначе null
 */
function validatePhone($phone) {
    if (empty($phone)) {
        return "Поле Телефон обязательно для заполнения!";
    } elseif (!preg_match("/^[0-9+\-\(\)\s]+$/", $phone)) {
        return "Телефон должен содержать только цифры и символы '+', '-', '(', ')'.";
    } elseif (strlen($phone) > 25) {
        return "Телефон не должен быть длиннее 25 символов.";
    }
    return null; // Возвращает null, если ошибок нет
}
/**
 * Проверка поля e-mail на корректность
 *
 * @param string $email Почта для проверки
 * @return string|null Возвращает сообщение об ошибке, если проверка не пройдена; иначе null
 */
function validateEmail($email) {
    if (empty($email)) {
        return "Поле Email обязательно для заполнения!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Некорректный формат email.";
    return null; // Возвращает null, если ошибок нет
}
}

/**
 * Проверка поля name на корректность
 *
 * @param string $name ФИО для проверки
 * @return string|null Возвращает сообщение об ошибке, если проверка не пройдена; иначе null
 */
function validateName($name) {

    if (empty($name)) {
        return "Поле ФИО обязательно для заполнения!";
    } elseif (!preg_match("/^[a-zA-Zа-яА-ЯЁё\s]+$/u", $name)) {
        return "ФИО должно содержать только буквы и пробелы.";
    } elseif (mb_strlen($name) > 50) {
        return "ФИО не должно быть длиннее 50 символов.";
    }
    return null; // Возвращает null, если ошибок нет
}

/**
 * Проверка поля e-mail на уникальность в базе данных
 *
 * @param string $email Почта для проверки
 * @return string|null Возвращает сообщение об ошибке, если проверка не пройдена; иначе null
 */
function validateuniqueEmail($email, $pdo) {
    // Проверка уникальности email
    $checkEmailQuery = "SELECT COUNT(*) FROM clients WHERE email = :email";
    $checkStmt = $pdo->prepare($checkEmailQuery);
    try{

    $checkStmt->execute(['email' => $email]);
    $emailExists = $checkStmt->fetchColumn();
    if ($emailExists) {
        return "Такой email уже зарегистрирован!";
    }
    }catch (PDOException $e) {
        // Логируем ошибку выполнения запроса
        logError("Ошибка проверки email: " . $e->getMessage());
        return "Ошибка при проверке email. Пожалуйста, попробуйте позже.";
    }
    return null; // Возвращает null, если ошибок нет
}

function isFutureDate($date) {

    $currentDate = new DateTime('now', new DateTimeZone('Europe/Moscow')); 

    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $date)) {
        $appointmentDate = DateTime::createFromFormat('Y-m-d\TH:i', $date);
        if (!($appointmentDate > $currentDate)) {
            return "Ошибка: Невозможно назначить сеанс на прошедшую дату.";
        }
        return null;
    } else {
        return "Ошибка: Неправильный формат года или даты!";
    }
}

function isConflictingAppointment($date, $lawyerId, $pdo) {
    $sql = "SELECT COUNT(*) FROM appointments WHERE date = :date AND lawyer_id = :lawyer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['date' => $date, 'lawyer_id' => $lawyerId]);
    
    if ($stmt->fetchColumn() > 0){
        return "Ошибка: Время сеанса конфликтует с другим запланированным сеансом.";
    }
    return null;
}

// Константы
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5 MB

define('DEFAULT_PHOTO_PATH', '/public/images/defaultimage/default-image.png'); // Путь к дефолтной картинке

// Функция проверки изображения
function validateImage($file) {
    $errors_image = [];
    $allowedTypes = [
        'image/jpeg', 
        'image/png',  
        'image/gif',  
        'image/jpg',  
        'image/webp', 
        'image/bmp',  
        'image/tiff',
    ];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'bmp', 'tiff'];

    // Проверка наличия файла
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors_image[] = "Файл не был загружен. Убедитесь, что вы выбрали файл и попробуйте снова.";
        return $errors_image;
    } 
    else {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors_image[] = "Ошибка при загрузке файла! Код ошибки: " . $file['error'];
        }
        else {
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors_image[] = "Недопустимое расширение файла!";
            } else if (!@getimagesize($file['tmp_name'])) {    // Проверка, является ли файл изображением
                $errors_image[] = "Файл не является изображением!";
            } else if (!in_array($fileType, $allowedTypes)) { // Проверка MIME-типа файла
                $errors_image[] = "Файл не является изображением!";
            } else if ($file['size'] > MAX_IMAGE_SIZE) {     // Проверка размера
                $errors_image[] = "Размер изображения превышает 5МБ!";
            } else if (true){
                $imageInfo = getimagesize($file['tmp_name']);
                if ($imageInfo[0] < 100 || $imageInfo[1] < 100) {
                    $errors_image[] = "Изображение слишком маленькое!";
                }
            }
            return $errors_image;
        }   
    }
}

function validateLoadImage($file) {
    $errors_load_image = [];
    $allowedTypes = [
        'image/jpeg', 
        'image/png',  
        'image/gif',  
        'image/jpg',  
        'image/webp', 
        'image/bmp',  
        'image/tiff',
    ];

    $fileType = @mime_content_type($file);
    if (!@getimagesize($file)) {  
        $errors_load_image[] = "Файл не является изображением!";
    } else if (!in_array($fileType, $allowedTypes)) { 
        $errors_load_image[] = "Файл не является изображением!";
    }
    return $errors_load_image;
}  
function validateLoadBlobImage($imageData) {
    $errors = [];

    // Попробуем загрузить изображение в ресурс
    $image = @imagecreatefromstring($imageData);
    if (!$image) {
        $errors[] = "Файл не является допустимым изображением или повреждён.";
        return $errors; 
    }

    $width = @imagesx($image);
    $height = @imagesy($image);
    if ($width > 3000 || $height > 3000) {
        $errors[] = "Изображение слишком большое (максимум 2000x2000).";
    }

    @imagedestroy($image);

    return $errors;
} 


function validateDefaultImage() {
    $errors_default_image = [];
    $baseDir = __DIR__ .'/../../';
    $Path = $baseDir . DEFAULT_PHOTO_PATH;

if (file_exists(dirname(dirname($Path)))) {
    if (file_exists(dirname($Path))) {
        if (is_dir(dirname(dirname($Path)))) {
            if(is_dir(dirname($Path))){
                if (!is_readable(dirname($Path))) {
                    $errors_default_image[] = "Нет прав для взаимодействия с папкой для загрузки дефолтного изображения!";
                } else if (!file_exists($Path)) {
                    $errors_default_image[] = "Дефолтное изображение не найдено на сервере!";
                } else if (is_dir($Path)) {
                    $errors_default_image[] = "На сервере существует папка с именем, совпадающим с названием дефолтного изображения!";
                } else if (!is_readable($Path)) {
                    $errors_default_image[] = "Дефолтное изображение недоступно для чтения!";
                } else {
                    $errors_check = [];
                    $errors_check = validateLoadImage($Path);
                    if (!empty($errors_check)) {
                        $errors_default_image = array_merge($errors_default_image, $errors_check);
                    }
                }
            }else{
                $errors_default_image[] = "На сервере существует файл с именем, совпадающим с названием папки для загрузки дефолтного изображений!";
            }
        } else {
            $errors_default_image[] = "На сервере существует файл с именем, совпадающим с названием папки для загрузки дефолтного изображений!";
        }
    }
    else{
        $errors_default_image[] = "Папки для загрузки дефолтного изображения не существует на сервере! Попробуйте позже.";
    }
}
else  {
$errors_default_image[] = "Папки для загрузки дефолтного изображения не существует на сервере! Попробуйте позже.";
}
    return $errors_default_image;
}  
?>