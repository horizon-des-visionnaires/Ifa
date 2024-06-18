<?php

namespace home; // Déclare le namespace pour ce fichier.

use PDO; // Importe la classe PDO pour la connexion à la base de données.
use Twig\Environment; // Importe la classe Environment de Twig pour gérer l'environnement de templates.
use Twig\Loader\FilesystemLoader; // Importe la classe FilesystemLoader de Twig pour charger les templates à partir du système de fichiers.

require 'vendor/autoload.php'; // Charge automatiquement les classes installées via Composer.

require_once __DIR__ . '/../model/homeModel.php'; // Inclut le fichier contenant la classe homeModel.

class homeController
{
    protected $twig; // Propriété pour l'objet Twig.
    private $loader; // Propriété pour le loader de Twig.
    private $homeModel; // Propriété pour l'objet homeModel.
    private $dsn; // Propriété pour l'objet PDO (connexion à la base de données).

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates'); // Initialise le loader de Twig avec le répertoire des templates.
        $this->twig = new Environment($this->loader); // Initialise l'environnement Twig avec le loader.
        $this->homeModel = new \home\homeModel(); // Crée une instance de la classe homeModel.
    }

    public function connectDB()
    {
        session_start();
        var_dump($_SESSION);
        // Crée une nouvelle connexion PDO à la base de données MySQL.
        $this->dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
        $this->dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Définit le mode d'erreur de PDO pour lancer des exceptions.
    }

    public function home()
    {
        // Utilise Twig pour rendre le template 'home/home.html.twig' et l'affiche.
        echo $this->twig->render('home/home.html.twig');
    }
}

