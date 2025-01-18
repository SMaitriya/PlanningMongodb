<?php
require_once 'config/database.php';

$client = getConnection();
$collection = $client->Planning->schedules;

$year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;
$plannings = $collection->find(['year' => $year], ['sort' => ['date' => 1]]);
$planningsArray = iterator_to_array($plannings);

$nbColumns = 4;
$nbRows = ceil(count($planningsArray) / $nbColumns);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">
    <title>Planning des corvées - Vue générale</title>
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
        <a href="index.php" class="edit-button">Mode édition</a>
    </div>

    <div class="planning-container">
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
    ?>

    <div class="statistics">
        <h2>Statistiques <?php echo $year ?></h2>
        <div class="stats-grid">
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