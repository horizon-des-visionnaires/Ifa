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

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates'); // Initialise le loader de Twig avec le répertoire des templates.
        $this->twig = new Environment($this->loader); // Initialise l'environnement Twig avec le loader.
        $this->homeModel = new \home\homeModel(); // Crée une instance de la classe homeModel.
    }

    public function home()
    {
        session_start();

        $isConnected = false;
        $userId = null;
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        }

        $IsAdmin = false;
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }

        $this->logOut();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId !== null) {
            $this->getAddPostData($userId);
        }

        $postData = $this->homeModel->getPost();
        $this->getAddViewsData();

        echo $this->twig->render('home/home.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'postData' => $postData
        ]);
    }

    public function logOut()
    {
        if (isset($_POST['logOut'])) {
            session_unset();
            header("Location: /login");
        }
    }

    public function getAddPostData($userId)
    {
        if (isset($_POST['addPost'])) {
            $TitlePost = $_POST['TitlePost'];
            $ContentPost = $_POST['ContentPost'];

            $PicturesPost = [];
            if (isset($_FILES["PicturePost"])) {
                if (count($_FILES["PicturePost"]["tmp_name"]) > 5) {
                    echo "You can upload a maximum of 5 images.";
                    return;
                }
                foreach ($_FILES["PicturePost"]["tmp_name"] as $tmpName) {
                    if ($tmpName) {
                        $PicturesPost[] = file_get_contents($tmpName);
                    }
                }
            }

            $IdPost = $this->homeModel->addPost($TitlePost, $ContentPost, $PicturesPost, $userId);

            if ($IdPost) {
                header("Location: /");
                exit();
            }
        }
    }

    public function getAddViewsData()
    {
        if (isset($_POST['viewMore'])) {
            $idPost = $_POST['idPost'];

            $this->homeModel->updateViews($idPost);
        }
    }
}
