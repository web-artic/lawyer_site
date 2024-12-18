<?php

class Lawyer {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addLawyer($username, $password, $specialization) {
        // Хешируем пароль
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Добавление нового пользователя
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'lawyer')");
        $stmt->execute([$username, $hashedPassword]);

        // Получаем ID нового пользователя
        $userId = $this->pdo->lastInsertId();

        // Добавление нового адвоката
        $stmt = $this->pdo->prepare("INSERT INTO lawyers (user_id, specialization) VALUES (?, ?)");
        $stmt->execute([$userId, $specialization]);
    }
}
?>