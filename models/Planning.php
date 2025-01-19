<?php
require_once __DIR__. '/../config/database.php';

try {
    $client = getConnection();
    $collection = $client->Planning->schedules;
    
    if ($collection->countDocuments() > 0) {
        echo "Des plannings existent déjà dans la base !\n";
        exit;
    }
    
    $years = [2024, 2025, 2026, 2027];  
    $plannings = []; 
    
    foreach($years as $year) {  
        // Trouver le premier dimanche de l'année
        $startDate = new DateTime($year . '-01-01');
        while($startDate->format('w') != 0) {
            $startDate->modify('+1 day');
        }
        
        // Créer les 52 semaines
        for($week = 1; $week <= 52; $week++) {
            $plannings[] = [
                'date' => $startDate->format('Y-m-d'),
                'week_number' => $week,
                'year' => $year,
                'assignee' => null,
                'status' => 'pending'
            ];
            
            // Passer au dimanche suivant
            $startDate->modify('+7 days');
        }
    }
    
    // Insertion des plannings
    $result = $collection->insertMany($plannings);
    echo "Plannings créés avec succès !\n";
    echo "Nombre de semaines créées : " . $result->getInsertedCount() . "\n";

    // Afficher quelques dates pour vérification
    echo "\nExemples de dates créées :\n";
    $cursor = $collection->find([], ['limit' => 5]);
    foreach ($cursor as $planning) {
        echo "Date : " . $planning->date . " (Semaine " . $planning->week_number . " de " . $planning->year . ")\n";
    }
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}