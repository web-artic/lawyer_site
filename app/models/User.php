<?php
namespace App\models;

use PDO;

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

    public function createUser($username, $password, $role = 'client') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        return $stmt->execute(['username' => $username, 'password' => $hash, 'role' => $role]);
    }

    public function getUserRole($userId) {
        $stmt = $this->db->prepare("SELECT users.role FROM users WHERE users.id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchColumn();
    }
}
?>
