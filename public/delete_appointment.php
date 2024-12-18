<?php

include __DIR__ .'/../app/core/db.php';


// Получение идентификатора записи для удаления
if (isset($_POST['appointment_id2'])) {
    $appointmentId = $_POST['appointment_id2'];
    
    $sql = "DELETE FROM appointments WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute(['id' => $appointmentId]);
        header("Location: /app/views/dashboard/lawyer.php");
    } catch (PDOException $e) {
        logError("Ошибка удаления сеанса: " . $e->getMessage());
        echo "Ошибка удаления сеанса. Попробуйте позже.";
    }
}
?>
