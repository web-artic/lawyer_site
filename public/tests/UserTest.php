<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/models/User.php';

use PHPUnit\Framework\TestCase;
use App\models\User;

class UserTest extends TestCase {
    /** @var \PDO|\PHPUnit\Framework\MockObject\MockObject $pdo */
    private $pdo;
    private $user;

    protected function setUp(): void {
        // Мок объекта PDO
        $this->pdo = $this->createMock(PDO::class);
        $this->user = new User($this->pdo);
    }

    public function testFindUserByUsername() {
        $username = 'testuser';

        // Мок PDOStatement
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with(['username' => $username])->willReturn(true);
        $stmt->method('fetch')->willReturn([
            'id' => 1,
            'username' => $username,
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'client',
        ]);

        $this->pdo->method('prepare')->willReturn($stmt);

        $user = $this->user->findUserByUsername($username);

        $this->assertNotNull($user);
        $this->assertEquals($username, $user['username']);
    }

    public function testCreateUser() {
        $username = 'newuser';
        $password = 'password123';
        $role = 'client';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->user->createUser($username, $password, $role);

        $this->assertTrue($result);
    }

    public function testGetUserRole() {
        $userId = 1;

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with(['id' => $userId])->willReturn(true);
        $stmt->method('fetchColumn')->willReturn('client');

        $this->pdo->method('prepare')->willReturn($stmt);

        $role = $this->user->getUserRole($userId);

        $this->assertEquals('client', $role);
    }

    protected function tearDown(): void {
        $this->pdo = null;
        $this->user = null;
    }
}


?>