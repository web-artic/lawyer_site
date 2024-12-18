<?php
include __DIR__ .'/../app/core/db.php';
require_once __DIR__ .'/../storage/logs/error_log.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
?>

<div id="updateAppointmentModal1" class="update-info-client-modal">
    <div class="update-info-client-modal-content">
        <span class="close-update">&times;</span>
        <h2>Изменить запись</h2>
        <form method="POST">

            <!-- Идентификатор формы -->
            <input type="hidden" name="form_type" value="update_appointment">

            <!-- Скрытое поле для передачи ID записи -->
            <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($id) ?>">
            <label for="date"><?= htmlspecialchars($id) ?></label>
            <!-- Поле имени -->
            <label for="date">Дата:</label>
            <input type="datetime-local" id="date" name="date" required>
            <small class="error"><?= $errors['date'] ?></small>

            <!-- Кнопка отправки -->
            <button type="submit" id="submitButton">Сохранить</button>

            <!-- Скрытое поле, чтобы передать состояние ошибок на JavaScript -->
            <div id="hasErrors1" data-errors="<?= $hasErrors ? '1' : '0' ?>" style="display: none;"></div>

        </form>
    </div>
</div>
