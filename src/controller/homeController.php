<?php

namespace home;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php'; 

require_once __DIR__ . '/../model/homeModel.php';

class homeController
{
    protected $twig;
    private $loader;
    private $homeModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->homeModel = new \home\homeModel();
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
        $userProData = $this->homeModel->get5UserProRandom();
        $randomPosts = $this->homeModel->get5RandomPostsFromTop10();

        echo $this->twig->render('home/home.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'userProData' => $userProData,
            'randomPosts' => $randomPosts
        ]);
    }

    public function logOut()
    {
        if (isset($_POST['logOut'])) {
            session_unset();
            header("Location: /login");
        }
    }
}
