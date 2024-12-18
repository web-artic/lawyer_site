<div id="addAppointmentModal1" class="update-info-client-modal">
    <div class="update-info-client-modal-content">
        <span class="close">&times;</span>
        <h2>Добавить новый сеанс</h2>
        <form method="POST">

            <!-- Идентификатор формы -->
            <input type="hidden" name="form_type" value="add_appointment">

            <!-- Поле имени -->
            <label for="date">Дата:</label>
            <input type="datetime-local" id="date" name="date" required>
            <small class="error"><?= $errors['date'] ?></small>

            <button type="submit" id="submitButton">Добавить</button>

            <!-- Скрытое поле, чтобы передать состояние ошибок на JavaScript -->
            <div id="hasErrors" data-errors="<?= $hasErrors ? '1' : '0' ?>" style="display: none;"></div>
        </form>
    </div>
</div>
