<?php
include __DIR__ .'/../app/core/db.php';
require_once __DIR__ .'/../storage/logs/error_log.php';
include __DIR__ .'/../app/helpers/validate.php'; 

$errors = ['name' => '', 'email' => '', 'phone' => '', 'service' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&  $_POST['form_type'] === 'update_info_client') {

    $name = trim($_POST['name']);
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
    $sql = "UPDATE clients SET name = :name, email = :email, phone = :phone, service = :service WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
            'service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8'),
        ]);
        echo "Клиент успешно обновлён!";
    } catch (PDOException $e) {
        logError("Ошибка обновления клиента: " . $e->getMessage());
        echo "Ошибка обновления клиента. Попробуйте позже.";
    }
    }
}
?>

