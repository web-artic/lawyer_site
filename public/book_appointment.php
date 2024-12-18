<?php
session_start();

require_once __DIR__ .'/../app/core/db.php';
require_once __DIR__ .'/../app/models/Appointment.php';

$appointmentId = $_POST['appointment_id'] ?? null;
$clientId = $_POST['client_id'] ?? null;

// Создаем объект Appointment и обновляем статус
$appointment = new Appointment($pdo);

if ($appointment->checkStatus($appointmentId)){
        echo "1";
        // Обновляем статус на 'свободно'
        $appointment->updateAppointmentStatus($appointmentId, 'свободно');
        
        // Перенаправление пользователя на страницу со списком встреч или сообщение об успехе
        header("Location: /app/views/dashboard/appointment.php?success");
        exit;
}
else {
    if ($appointmentId && $clientId) {
        echo "2";
        // Обновляем статус на 'занято' и записываем клиента на встречу
        $appointment->bookAppointment($appointmentId, $clientId);
        
        // Перенаправление пользователя на страницу со списком встреч или сообщение об успехе
        header("Location: /app/views/dashboard/appointment.php?success");
        exit;
    } else {
        // Перенаправляем с ошибкой, если данные не переданы
        header("Location: /app/views/dashboard/appointment.php?error");
        exit;
    }
}

?>
