<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/models/Appointment.php';

use PHPUnit\Framework\TestCase;

class AppointmentTest extends TestCase {
    /** @var \PDO|\PHPUnit\Framework\MockObject\MockObject $pdo */
    private $pdo;
    private $appointment;

    protected function setUp(): void {
        // Мок объекта PDO
        $this->pdo = $this->createMock(PDO::class);
        $this->appointment = new Appointment($this->pdo);
    }

    // Тест для обновления статуса встречи
    public function testUpdateAppointmentStatusFree() {
        $appointmentId = 1;
        $status = 'свободно';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $this->appointment->updateAppointmentStatus($appointmentId, $status);
        $this->assertTrue(true); // Проверяем, что метод работает без ошибок
    }

    public function testUpdateAppointmentStatusBusy() {
        $appointmentId = 1;
        $status = 'занято';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $this->appointment->updateAppointmentStatus($appointmentId, $status);
        $this->assertTrue(true); // Проверяем, что метод работает без ошибок
    }

    public function testCheckStatusAvailable() {
        $appointmentId = 1;
    
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with([':id' => $appointmentId, ':status' => 'занято'])->willReturn(true);
        $stmt->method('fetchAll')->willReturn([['id' => 1, 'status' => 'занято']]);
    
        $this->pdo->method('prepare')->willReturn($stmt);
    
        $result = $this->appointment->checkStatus($appointmentId);
        $this->assertTrue($result); // Ожидаем, что статус встречи "занято"
    }
    
    public function testCheckStatusNotAvailable() {
        $appointmentId = 1;
    
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with([':id' => $appointmentId, ':status' => 'занято'])->willReturn(true);
        $stmt->method('fetchAll')->willReturn([]); // Статус "свободно"
    
        $this->pdo->method('prepare')->willReturn($stmt);
    
        $result = $this->appointment->checkStatus($appointmentId);
        $this->assertFalse($result); // Ожидаем, что статус встречи "свободно"
    }    

    // Тест для записи клиента на встречу
    public function testBookAppointmentSuccess() {
        $appointmentId = 1;
        $userId = 1;

        // Мокируем запрос для клиента
        $stmtClient = $this->createMock(PDOStatement::class);
        $stmtClient->method('execute')->with(['user_id' => $userId])->willReturn(true);
        $stmtClient->method('fetch')->willReturn(['id' => 1]);

        // Мокируем подготовку запроса для записи
        $stmtAppointment = $this->createMock(PDOStatement::class);
        $stmtAppointment->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->will($this->onConsecutiveCalls($stmtClient, $stmtAppointment));

        $result = $this->appointment->bookAppointment($appointmentId, $userId);

        $this->assertTrue($result); // Проверяем успешную запись клиента
    }

    public function testBookAppointmentFail() {
        $appointmentId = 1;
        $userId = 1;

        // Мокируем запрос для клиента, возвращаем null
        $stmtClient = $this->createMock(PDOStatement::class);
        $stmtClient->method('execute')->with(['user_id' => $userId])->willReturn(true);
        $stmtClient->method('fetch')->willReturn(null); // Клиент не найден

        $this->pdo->method('prepare')->willReturn($stmtClient);

        $result = $this->appointment->bookAppointment($appointmentId, $userId);

        $this->assertFalse($result); // Проверяем, что запись не удалась
    }

    // Тест для добавления встречи
    public function testAddAppointment() {
        $lawyerId = 1;
        $clientId = 1;
        $date = '2024-12-14 10:00:00';
        $status = 'свободно';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $this->appointment->addAppointment($lawyerId, $clientId, $date, $status);
        $this->assertTrue(true); // Проверяем, что метод работает без ошибок
    }

    protected function tearDown(): void {
        $this->pdo = null;
        $this->appointment = null;
    }
}

?>
