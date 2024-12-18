<?php
function relevanceAppointments(&$appointments, $clientService, $clientAppointments) {
    
    $bookedLawyers = array_unique(array_column($clientAppointments, 'lawyer_id'));
    foreach ($appointments as &$appointment) {
        $relevance = 0;
        if ($appointment['service'] === $clientService) {
            $relevance += 40;
        }

        if (in_array($appointment['lawyer_id'], $bookedLawyers)) {
            $relevance += 30; 
        }

        $appointmentDate = new DateTime($appointment['date']);
        $currentDate = new DateTime();

        if ($appointmentDate > $currentDate) {
            $daysDiff = $currentDate->diff($appointmentDate)->days;
            $relevance += max(20 - $daysDiff, 0);
        }

        $serviceCount = count(array_filter($clientAppointments, function($clientAppointment) use ($appointment) {
            return $clientAppointment['service'] === $appointment['service'];
        }));
        if ($serviceCount >= 1){
            $relevance += 25 * $serviceCount;
        }
        if ($appointment['status'] === 'занято') {
            $relevance = $relevance - 1000;
        }

        $appointment['relevance'] = $relevance;
    }
    usort($appointments, function ($a, $b) {
        return $b['relevance'] <=> $a['relevance'];
    });
}
?>

