<?php
require_once 'config/database.php';

$client = getConnection();
$collection = $client->Planning->schedules;

$year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;
$plannings = $collection->find(['year' => $year], ['sort' => ['date' => 1]]);
$planningsArray = iterator_to_array($plannings);
$users = $client->Planning->users->find();
$usersArray = iterator_to_array($users);

// Nombre de colonnes souhaité
$nbColumns = 4;
// Calculer le nombre de lignes nécessaires
$nbRows = ceil(count($planningsArray) / $nbColumns);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Planning des corvées</title>
    <style>
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-bottom: 20px; 
            background: grey; 
        }
        th, td { 
            border: 1px solid black; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        select { 
            width: 120px;  /* Largeur fixe pour le select */
            padding: 4px;
            display: inline-block;  /* Pour être sur la même ligne */
            margin-left: 10px;      /* Espace entre la date et le select */
        }
        .submit-button {
            padding: 10px 20px;
        }
        .year-selector {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Planning des corvées d'épluchage</h1>
    
    <div class="year-selector">
        Année : 
        <?php foreach([2024, 2025, 2026, 2027] as $y): ?>
            <a href="?year=<?php echo $y ?>" style="margin-right: 10px; <?php echo $year == $y ? 'font-weight: bold;' : '' ?>">
                <?php echo $y ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="POST">
    <table>
        <?php for ($row = 0; $row < $nbRows; $row++): ?>
            <tr>
                <?php for ($col = 0; $col < $nbColumns; $col++): ?>
                    <?php
                    // Calcul de l'index dans le tableau des plannings
                    $index = $row + ($col * $nbRows);

                    // Vérifier si un planning existe à cet index
                    if ($index < count($planningsArray)):
                        $planning = $planningsArray[$index];
                    ?>
                        <td>
                            <!-- Affichage de la date au format dd/mm/yyyy -->
                            <span style="display: inline-block; min-width: 80px;">
                                <?php echo (new DateTime($planning['date']))->format('d/m/Y'); ?>
                            </span>

                            <!-- Champ caché pour envoyer la date dans le formulaire -->
                            <input type="hidden" name="dates[]" value="<?php echo htmlspecialchars($planning['date'], ENT_QUOTES, 'UTF-8'); ?>">

                            <!-- Menu déroulant pour choisir un utilisateur -->
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
                    <?php else: ?>
                        <!-- Cellule vide si aucun planning à cet index -->
                        <td></td>
                    <?php endif; ?>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>

    <!-- Bouton pour valider les assignations -->
    <button type="submit" name="assign" class="submit-button">Valider les assignations</button>
</form>

</body>
</html>


<?php 

    $client = getConnection();
    $collection = $client->Planning->schedules;
    
    $dates = $_POST['dates'] ?? [];
    $users = $_POST['users'] ?? [];
    
    // Met à jour chaque date avec l'utilisateur correspondant
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


    
    // Calcul des statistiques avec MongoDB Aggregation
    $stats = $collection->aggregate([
        [
            '$match' => [
                'year' => (int)$year,
                'assignee' => ['$ne' => null]
            ]
        ],
        [
            '$group' => [
                '_id' => '$assignee',
                'count' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => ['count' => 1]  // 1 pour ordre croissant
        ]
    ])->toArray();

    // Affichage des statistiques
    echo "<h2>Statistiques par ordre croissant</h2>";
    $i = 1;
    foreach($stats as $stat) {
        echo $i . ". " . ucfirst($stat['_id']) . " : " . $stat['count'] . "<br>";
        $i++;
    }

  

?>