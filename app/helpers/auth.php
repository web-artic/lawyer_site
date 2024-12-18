<?php
    include __DIR__ .'/../core/db.php';

    // Проверка авторизации пользователя
    function isAuthorized() {
    return isset($_SESSION['user_id']);
    }

    // Получение роли пользователя из сессии
    function getUserSessionRole() {
    return $_SESSION['role'] ?? null;
    }

    // Получение ID адвоката по user_id
    function getLawyerId(){
        global $pdo; 

        $sqlLawyer = "SELECT id FROM lawyers WHERE user_id = :user_id";
        $stmtLawyer = $pdo->prepare($sqlLawyer);
        $stmtLawyer->execute(['user_id' => getUserIdSession()]); 

        $lawyer = $stmtLawyer->fetch(PDO::FETCH_ASSOC); // Извлекаем результат
        return $lawyer['id'] ?? null; 
    }

    // Получение Name адвоката
    function getLawyerName(){
        global $pdo; 

        $sqlLawyer = "SELECT name FROM lawyers WHERE id = :lawyer_id";
        $stmtLawyer = $pdo->prepare($sqlLawyer);
        $stmtLawyer->execute(['lawyer_id' => getLawyerId()]); 

        $Lawyer = $stmtLawyer->fetch(PDO::FETCH_ASSOC); // Извлекаем результат
        return $Lawyer['name'] ?? null; 
    }

    // Получение специализации адвоката
    function getLawyerService(){
        global $pdo; 

        $sqlLawyer = "SELECT specialization FROM lawyers WHERE id = :lawyer_id";
        $stmtLawyer = $pdo->prepare($sqlLawyer);
        $stmtLawyer->execute(['lawyer_id' => getLawyerId()]); 

        $Lawyer = $stmtLawyer->fetch(PDO::FETCH_ASSOC); // Извлекаем результат
        if ($Lawyer['specialization']) {
            if($Lawyer['specialization'] === 'Адвокат по уголовным делам'){
                return 'Уголовное дело';
            }
            if($Lawyer['specialization'] === 'Адвокат по гражданским делам'){
                return 'Гражданское дело';
            }
            if($Lawyer['specialization'] === 'Адвокат по семейным делам'){
                return 'Семейное дело';
            }
            if($Lawyer['specialization'] === 'Адвокат по административным делам'){
                return 'Административное дело';
            }
        } 
        else return null;
    }

    // Получение Name клиента по clientID
    function getClientName($client_id){
        global $pdo; 

        $sqlClient = "SELECT name FROM clients WHERE id = :client_id";
        $stmtClient = $pdo->prepare($sqlClient);
        $stmtClient->execute(['client_id' => $client_id]); 

        $client = $stmtClient->fetch(PDO::FETCH_ASSOC); // Извлекаем результат
        return $client['name'] ?? null; 
    }

    // Получение user_id пользователя из сессии
    function getUserIdSession() {
    return $_SESSION['user_id'] ?? null;
    }
    
    function logoutwithoutclass() {
        session_unset();
        session_destroy();
        header("Location: /public/index.php");
    }
?>
