<?php
require_once 'config/database.php'; 
require_once 'config/auth.php';     

// Variable pour stocker message d'erreur 
$error = null;  

// Si le formulaire est envoyé (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Récupère les données du formulaire
   $username = $_POST['username'] ?? '';
   $password = $_POST['password'] ?? '';     

   // connexion avec les identifiants
   if (login($username, $password)) {
       // Si connexion OK -> page d'accueil
       header('Location: index.php');
       exit();
   } else {
       // Si connexion échoue -> message d'erreur
       $error = "Identifiants incorrects";
   }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion - Planning des corvées</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="login-container">
        <h1>Connexion</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Se connecter</button>
        </form>
        
        <div class="back-link">
            <a href="home.php">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>