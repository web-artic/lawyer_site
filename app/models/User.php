<?php

namespace App\models;
require_once __DIR__ .'/../../vendor/autoload.php';

use PDO;
/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
*/

class User {
    protected $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $password, $email, $confirmationToken, $role = 'client') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email, confirmed, confirmation_token, role) VALUES (:username, :password, :email, '0', :confirmation_token, :role)");
        $isUserCreated = $stmt->execute(['username' => $username, 'password' => $hash, 'email' => $email,'confirmation_token' => $confirmationToken, 'role' => $role]);
 
        if (!$isUserCreated) {
            return false; // Если пользователь не создан, возвращаем false
        }
/*
        // Ссылка для подтверждения
        $confirmationLink = "http://localhost/public/confirm.php?token=$confirmationToken";

        // Отправка письма с использованием PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Настройки SMTP для Mail.ru
            $mail->isSMTP();
            $mail->Host = 'smtp.mail.ru'; // SMTP-сервер Mail.ru
            $mail->SMTPAuth = true;
            $mail->Username = 'web-artic@mail.ru'; // Ваш email на Mail.ru
            $mail->Password = 'qazxswedc123QAZ'; // Ваш пароль от email
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Шифрование
            $mail->Port = 465; // Порт SMTP

            // Настройки отправителя и получателя
            $mail->setFrom('web-artic@mail.ru', 'Lawyer_site'); // От кого
            $mail->addAddress($email, $username); // Кому

            // Содержание письма
            $mail->isHTML(true);
            $mail->Subject = "Подтверждение регистрации";
            $mail->Body = "
                <h1>Привет, $username!</h1>
                <p>Спасибо за регистрацию на нашем сайте.</p>
                <p>Перейдите по следующей ссылке, чтобы подтвердить вашу регистрацию:</p>
                <a href='$confirmationLink'>$confirmationLink</a>
            ";

            $mail->send(); // Отправляем письмо
            return true; // Успешное создание пользователя и отправка письма
        } catch (Exception $e) {
            // Логируем ошибку, если письмо не удалось отправить
            error_log("Ошибка при отправке письма: {$mail->ErrorInfo}");
            return false; // Возвращаем false в случае ошибки
        }
        */
    }

    public function getUserRole($userId) {
        $stmt = $this->db->prepare("SELECT users.role FROM users WHERE users.id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchColumn();
    }
}
?>
