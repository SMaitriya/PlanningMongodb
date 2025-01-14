<?php
require_once __DIR__ . '/../config/database.php';

try {
    $client = getConnection();
    $collection = $client->Planning->users;
    
    // Vérifier si des utilisateurs existent déjà
    if ($collection->countDocuments() > 0) {
        echo "Les utilisateurs existent déjà !\n";
        exit;
    }
    
    // Liste des utilisateurs à créer
    $users = [
        [
            'username' => 'vincent',
            'password' => password_hash('tata', PASSWORD_DEFAULT),
        ],
        [
            'username' => 'david',
            'password' => password_hash('tata', PASSWORD_DEFAULT),
        ],
        [
            'username' => 'matt',
            'password' => password_hash('tata', PASSWORD_DEFAULT),
        ],
        [
            'username' => 'toto',
            'password' => password_hash('tata', PASSWORD_DEFAULT),
        ]
    ];
    
    // Insertion des utilisateurs
    $result = $collection->insertMany($users);
    echo "Utilisateurs créés avec succès ! \n";
    echo "Nombre d'insertions : " . $result->getInsertedCount() . "\n";
    
    // Afficher les utilisateurs pour vérifier
    $allUsers = $collection->find();
    foreach ($allUsers as $user) {
        echo "Utilisateur créé : " . $user->username . "\n";
    }
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}