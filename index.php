<?php
require_once 'config/database.php';

$client = getConnection();
$collection = $client->Planning->schedules;

$year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;
$plannings = $collection->find(['year' => $year], ['sort' => ['date' => 1]]);
$planningsArray = iterator_to_array($plannings);
$users = $client->Planning->users->find();
$usersArray = iterator_to_array($users);

$nbColumns = 4;
$nbRows = ceil(count($planningsArray) / $nbColumns);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>Planning des corvées</title>
</head>
<body>
    <h1>Planning des corvées d'épluchage</h1>
    
    <div class="year-selector">
        Année : 
        <?php foreach([2024, 2025, 2026, 2027] as $y): ?>
            <a href="?year=<?php echo $y ?>" class="<?php echo $year == $y ? 'active' : '' ?>">
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
                    $index = $row + ($col * $nbRows);
                    if ($index < count($planningsArray)):
                        $planning = $planningsArray[$index];
                    ?>
                        <td>
                            <span class="date">
                                <?php echo (new DateTime($planning['date']))->format('d/m/Y'); ?>
                            </span>

                            <input type="hidden" name="dates[]" value="<?php echo htmlspecialchars($planning['date'], ENT_QUOTES, 'UTF-8'); ?>">

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
                        <td></td>
                    <?php endif; ?>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>

    <button type="submit" name="assign" class="submit-button">Valider les assignations</button>
</form>

<?php 
    $client = getConnection();
    $collection = $client->Planning->schedules;
    
    $dates = $_POST['dates'] ?? [];
    $users = $_POST['users'] ?? [];
    
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
            '$sort' => ['count' => 1]
        ]
    ])->toArray();
    
    echo "<div class='statistics'>";
    echo "<h2>Statistiques par ordre croissant</h2>";
    echo "<div class='stats-grid'>";
    foreach($stats as $stat) {
        echo "<div class='stat-card'>";
        echo "<div class='stat-name'>" . ucfirst($stat['_id']) . "</div>";
        echo "<div class='stat-count'>" . $stat['count'] . "</div>";
        echo "<div class='stat-label'>corvées</div>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
?>

</body>
</html>