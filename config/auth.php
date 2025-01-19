<?php
session_start(); // Pour garder l'utilisateur connecté
require_once __DIR__ . '/database.php';  // Pour la base de données

// Vérifie si quelqu'un est connecté 
function isAuthenticated() {     
   return isset($_SESSION['user']); 
}  

// Force la connexion sinon -> page login
function requireAuth() {     
   if (!isAuthenticated()) {         
       header('Location: login.php');        
       exit();
   } 
}  

// Connexion: vérifie username/password dans la BD
function login($username, $password) {     
   try {         
       $client = getConnection();
       $result = $client->Planning->users->findOne(['username' => $username]);         
       
       if ($result && password_verify($password, $result->password)) {             
           $_SESSION['user'] = [                 
               'username' => $result->username,                 
               'id' => (string)$result->_id             
           ];             
           return true;
       }         
       return false;
   } catch (Exception $e) {         
       error_log("Erreur d'authentification : " . $e->getMessage());         
       return false;     
   } 
}  

// Déconnexion: supprime la session
function logout() {     
   unset($_SESSION['user']);     
   session_destroy(); 
}