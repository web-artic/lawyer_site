<?php

class Appointment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Обновление статуса встречи
    public function updateAppointmentStatus($appointmentId, $status) {
        if ($status === 'свободно'){
        $sql = "UPDATE appointments SET status = :status, client_id = :client_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['status' => $status,'client_id' => null,'id' => $appointmentId]);
        }
        if ($status === 'занято') {
            $sql = "UPDATE appointments SET status = :status WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['status' => $status, 'id' => $appointmentId]);
        }
    }

    // Проверка статуса встречи
    public function checkStatus($appointmentId) {
        $sql = "SELECT * FROM appointments WHERE id = :id AND status = :status";
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':id' => $appointmentId,
            ':status' => 'занято'
        ]);
        
        $result = $stmt->fetchAll();
        
        if ($result){return true;}
        else {return false;}
    }
    // Запись клиента на встречу
    public function bookAppointment($appointmentId, $userId) {

        $sql = "SELECT id FROM clients WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $client = $stmt->fetch();
    
        if ($client && isset($client['id'])) {
            $clientId = $client['id'];
    
            $sql = "UPDATE appointments SET status = 'занято', client_id = :client_id WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['client_id' => $clientId, 'id' => $appointmentId]);
    
            return true; 
        } else {
            return false;
        }
    }
    

    public function addAppointment($lawyerId, $clientId, $date, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO appointments (lawyer_id, client_id, date, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$lawyerId, $clientId, $date, $status]);
    }
}

?>
