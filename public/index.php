<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Initialisation de la connexion MongoDB et sélection de la collection des plannings
$client = getConnection();
$collection = $client->Planning->schedules;

// Récupération de l'année depuis l'URL, par défaut 2025
$year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;

// Récupération des plannings pour l'année sélectionnée, triés par date
$plannings = $collection->find(['year' => $year], ['sort' => ['date' => 1]]);
$planningsArray = iterator_to_array($plannings);

// Configuration de l'affichage en grille (4 colonnes)
$nbColumns = 4;
$nbRows = ceil(count($planningsArray) / $nbColumns);
?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/auth.css">
   <title>Planning des corvées - Vue générale</title>
</head>
<body>
   <h1>Planning des corvées d'épluchage</h1>

   <div class="navigation-container">
       <div class="year-selector">
           Année : 
           <!-- Boucle pour générer les liens des années -->
           <?php foreach([2024, 2025, 2026, 2027] as $y): ?>
               <a href="?year=<?php echo $y ?>" class="<?php echo $year == $y ? 'active' : '' ?>">
                   <?php echo $y ?>
               </a>
           <?php endforeach; ?>
       </div>

    <!-- Affichage du bouton de connexion -->
<div class="nav-buttons">
    <a href="login.php" class="nav-button login-button">Connexion</a>
</div>

   <!-- Conteneur principal du planning -->
   <div class="planning-container">
       <table>
           <?php for ($row = 0; $row < $nbRows; $row++): ?>
               <tr>
                   <?php for ($col = 0; $col < $nbColumns; $col++): ?>
                       <?php
                       // Calcul de l'index pour l'affichage en colonnes
                       $index = $row + ($col * $nbRows);
                       if ($index < count($planningsArray)):
                           $planning = $planningsArray[$index];
                       ?>
                           <td>
                               <!-- Cellule de planning avec date et assignation -->
                               <div class="planning-cell">
                                   <span class="date">
                                       <?php echo (new DateTime($planning['date']))->format('d/m/Y'); ?>
                                   </span>
                                   <span class="assignee <?php echo empty($planning['assignee']) ? 'not-assigned' : ''; ?>">
                                       <?php echo empty($planning['assignee']) ? 'Non assigné' : ucfirst($planning['assignee']); ?>
                                   </span>
                               </div>
                           </td>
                       <?php else: ?>
                           <td></td>
                       <?php endif; ?>
                   <?php endfor; ?>
               </tr>
           <?php endfor; ?>
       </table>
   </div>

   <?php

   // Requête d'agrégation MongoDB pour les statistiques
   $stats = $collection->aggregate([
       [
           // Filtre sur l'année et les corvées assignées
           '$match' => [
               'year' => (int)$year,
               'assignee' => ['$ne' => null]
           ]
       ],
       [
           // Groupement par assigné / comptage
           '$group' => [
               '_id' => '$assignee',
               'count' => ['$sum' => 1]
           ]
       ],
       [
           // Tri par nombre de corvées
           '$sort' => ['count' => 1]
       ]
   ])->toArray();
   ?>

   <!-- Section des statistiques -->
   <div class="statistics">
       <h2>Statistiques <?php echo $year ?></h2>
       <div class="stats-grid">
           <!-- Affichage des statistiques par personne -->
           <?php foreach($stats as $stat): ?>
               <div class="stat-card">
                   <div class="stat-name"><?php echo ucfirst($stat['_id']) ?></div>
                   <div class="stat-count"><?php echo $stat['count'] ?></div>
                   <div class="stat-label">corvées</div>
               </div>
           <?php endforeach; ?>
       </div>
   </div>
</body>
</html>