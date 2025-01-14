<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getConnection() {
    try {
        return new MongoDB\Client("mongodb+srv://username:password@your-cluster.mongodb.net/");
    } catch (Exception $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}