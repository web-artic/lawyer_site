<?php
include __DIR__ .'/../app/core/db.php';
require_once __DIR__ .'/../storage/logs/error_log.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM clients WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute(['id' => $id]);
        echo "Клиент успешно удален!";
        header("Location: /app/views/clients_list.php"); 
    } catch (PDOException $e) {
        logError("Ошибка удаления клиента: " . $e->getMessage());
        echo "Ошибка удаления клиента. Попробуйте позже.";
    }
}
?>
