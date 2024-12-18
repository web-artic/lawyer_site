<?php
include __DIR__ .'/../core/db.php';

function relevanceAppointments4(&$appointments, $clientService, $clientAppointments, $pdo) {

    $weightsStmt = $pdo->query("SELECT criterion, weight FROM ranking_weights");
    $weights = $weightsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($appointments as $key => &$appointment) {
        $relevance = 0;
        $lawyerCount = 0;

        if ($appointment['service'] === $clientService) {
            $relevance += $weights['service_match'];
        }

        $lawyerCount = count (array_filter($clientAppointments, function($clientAppointment) use ($appointment) {
            return $clientAppointment['lawyer_id'] === $appointment['lawyer_id'];
        }));
        $relevance += $lawyerCount * $weights['lawyer_interaction'];

        $appointmentDate = new DateTime($appointment['date']);
        $currentDate = new DateTime();
        if ($appointmentDate > $currentDate) {
            $daysDiff = $currentDate->diff($appointmentDate)->days;
            $relevance += max($weights['date_proximity'] - $daysDiff, 0);
        }
        else {
            unset($appointments[$key]); 
            continue; 
        }

        $serviceCount = count(array_filter($clientAppointments, function ($clientAppointment) use ($appointment) {
            return $clientAppointment['service'] === $appointment['service'];
        }));
        $relevance += $serviceCount * $weights['client_history'];

        if ($appointment['cases_won'] >= 20) {
            $relevance += $appointment['cases_won'] * $weights['cases_won'] / 10;
        }
        else if ($weights['cases_won'] === 0){
            $relevance += $appointment['cases_won']  * $weights['cases_won'];
        }
        else {
            $relevance += $appointment['cases_won'];
        }

        if ($appointment['status'] === 'занято') {
            $relevance -= 1000; 
        }
        $appointment['relevance'] = $relevance;
    }

    usort($appointments, function ($a, $b) {
        return $b['relevance'] <=> $a['relevance'];
    });
}
?>
