<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireAuth(); // Force l'authentification pour accéder à cette page

// Initialisation de la connexion MongoDB et récupération des collections
$client = getConnection();
$collection = $client->Planning->schedules;

$year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;

// Récupération des plannings et des utilisateurs depuis la base de données
$plannings = $collection->find(['year' => $year], ['sort' => ['date' => 1]]);
$planningsArray = iterator_to_array($plannings);
$users = $client->Planning->users->find();
$usersArray = iterator_to_array($users);

// Configuration de l'affichage en grille
$nbColumns = 4;
$nbRows = ceil(count($planningsArray) / $nbColumns);
?>

<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/auth.css">
   <title>Planning des corvées</title>
</head>
<body>
  <h1>Planning des corvées d'épluchage</h1>

   <!-- Navigation et sélection d'année -->
   <div class="navigation-container">
   <div class="year-selector">
        Année : 
        <?php foreach([2024, 2025, 2026, 2027] as $y): ?>
            <a href="?year=<?php echo $y ?>" class="<?php echo $year == $y ? 'active' : '' ?>">
                <?php echo $y ?>
            </a>
        <?php endforeach; ?>
    </div>

       <!-- Informations utilisateur et bouton de déconnexion -->
       <div class="nav-buttons">
           <span class="user-info">Salut <?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
           <a href="logout.php" class="nav-button logout-button">Déconnexion</a>
       </div>
   </div>

   <!-- Formulaire d'assignation des corvées -->
   <form method="POST">
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
                               <!-- Affichage de la date et sélection de l'utilisateur -->
                               <span class="date">
                                   <?php echo (new DateTime($planning['date']))->format('d/m/Y'); ?>
                               </span>

                               <!-- Champ caché pour la date -->
                               <input type="hidden" name="dates[]" value="<?php echo htmlspecialchars($planning['date'], ENT_QUOTES, 'UTF-8'); ?>">

                               <!-- Liste déroulante des utilisateurs avec sélection actuelle -->
                               <select name="users[]">
                                   <option value="">--Choisir--</option>
                                   <?php foreach ($usersArray as $user): ?>
                                       <option value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" 
                                               <?php echo ($planning['assignee'] ?? '') === $user['username'] ? 'selected' : ''; ?>>
                                           <?php echo ucfirst(htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8')); ?>
                                       </option>
                                   <?php endforeach; ?>
                               </select>
                           </td>
                       <?php endif; ?>
                   <?php endfor; ?>
               </tr>
           <?php endfor; ?>
       </table>

       <button type="submit" name="assign" class="submit-button">Valider les assignations</button>
   </form>

   <?php 
   // Traitement du formulaire
   $dates = $_POST['dates'] ?? [];
   $users = $_POST['users'] ?? [];
   
   // Mise à jour des assignations dans la base de données
   foreach($dates as $index => $date) {
       if (isset($users[$index]) && !empty($users[$index])) {
           $collection->updateOne(
               ['date' => $date],
               ['$set' => [
                   'assignee' => $users[$index],
                   'status' => 'assigned'
               ]]
           );
       }
   }
   
   // Calcul et affichage des statistiques
   $stats = $collection->aggregate([
       [
           // Filtre sur l'année et les corvées assignées
           '$match' => [
               'year' => (int)$year,
               'assignee' => ['$ne' => null]
           ]
       ],
       [
           // Groupement par assigné avec comptage
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