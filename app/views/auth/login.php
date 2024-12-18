<!-- Модальное окно для входа -->
<div id="authModal" class="loginmodal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Войти в личный кабинет</h2>
        <form action="/public/index.php?action=login" method="POST">
            <input type="hidden" name="form_type" value="form_login">
            <input type="text" name="username" placeholder="Имя пользователя" required maxlength="40" pattern="[a-zA-Z0-9_-]+" title="Используйте только только буквы, цифры, _ и -.">
            <input type="password" name="password" placeholder="Пароль" required maxlength="100" pattern="[a-zA-Z0-9_-]+" title="Используйте только только буквы, цифры, _ и -.">
            <button type="submit">Войти</button>
        </form>
    </div>
</div>