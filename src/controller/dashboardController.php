<?php

namespace dashboard;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/dashboardModel.php';

class dashboardController
{
    protected $twig;
    private $loader;
    private $dashboardModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->dashboardModel = new dashboardModel();
    }

    public function dashboard()
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

        if (!$IsAdmin) {
            header("Location: /");
            exit();
        }

        $requestPassProData = $this->dashboardModel->getAllRequestPassPro();
        $this->getDataValidRequest();

        echo $this->twig->render('dashboard/dashboard.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'requestPassProData' => $requestPassProData
        ]);
    }

    public function getDataValidRequest()
    {
        if (isset($_POST['requestValid'])) {
            $IdRequest = $_POST['IdRequest'];

            $this->dashboardModel->requestValid($IdRequest);
        }
    }
}
