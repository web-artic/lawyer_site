<!-- Модальное окно для регистрации -->
<div id="registrModal" class="loginmodal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Регистрация клиента</h2>
        <form action="/public/index.php?action=register" method="POST">
            <input type="hidden" name="form_type" value="form_reg">
            <input type="text" name="username" placeholder="Имя пользователя" required maxlength="40" pattern="[a-zA-Z0-9_-]+" title="Используйте только только буквы, цифры, _ и -.">
            <input type="email" name="email" placeholder="Ваш Email" required>
            <input type="password" name="password" placeholder="Пароль" required maxlength="100" pattern="[a-zA-Z0-9_-]+" title="Используйте только только буквы, цифры, _ и -.">
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</div>