<?php
namespace App\controllers;

use App\models\User;

class AuthController {
    protected $userModel;

    public function __construct(User $userModel) {
        $this->userModel = $userModel;
    }

    public function login($username, $password) {
        $user = $this->userModel->findUserByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $role = $this->userModel->getUserRole($user['id']);
            header("Location: /app/views/dashboard/" . $role . ".php");
            $_SESSION['role'] = $role;
            exit();
        } else {
            return "Неверный логин или пароль";
        }
    }

    public function register($username, $password, $email, $confirmationToken ) {
        if ($this->userModel->findUserByUsername($username)) {
            return "Пользователь с таким именем уже существует.";
        }
        if($this->userModel->createUser($username, $password, $email, $confirmationToken)){
            return "Ошибка";
        }
        else{
            return "Регистрация прошла успешно. Авторизируйтесь в личном кабинете";
            //  return "Регистрация прошла успешно. Проверьте свою почту для подтверждения.";
        }
        
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: /public/index.php");
    }
}
?>
