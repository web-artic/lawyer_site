<form method="POST" class="registration-form">

<!-- Идентификатор формы -->
<input type="hidden" name="form_type" value="form_client">

<!-- Поле имени -->
<label for="name">ФИО:</label>
<input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
<small class="error"><?= $errors['name'] ?></small>

<!-- Поле email -->
<label for="email">Email:</label>
<input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
<small class="error"><?= $errors['email'] ?></small>

<!-- Поле телефона -->
<label for="phone">Телефон:</label>
<input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required>
<small class="error"><?= $errors['phone'] ?></small>

<!-- Поле телефона с выпадающим списком -->
<label for="service">Какая услуга адвоката потребуется?</label>
<div class="input-group">
    <select id="service" name="service" required>
        <option value="">Выберите услугу</option>
        <option value="Уголовное дело"selected>Уголовное дело</option>
        <option value="Гражданское дело">Гражданское дело</option>
        <option value="Семейное дело">Семейное дело</option>
        <option value="Административное дело">Административное дело</option>
    </select>
    <small class="error"><?= $errors['service'] ?></small>
</div>

<!-- Основная кнопка "Добавить" -->
<button type="submit" id="submitButton" <?php echo $status === 'success' ? 'style="display:none;"' : '' ?>>Добавить</button>

<!-- Сообщение об успехе -->
<button type="button" id="successMessage" style="display: <?php echo $status === 'success' ? 'block' : 'none' ?>; background-color: green; color: white;">
    Заявка отправлена!
</button>
</form>