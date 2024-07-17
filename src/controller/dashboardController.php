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
        $requestPassProData = $this->dashboardModel->getAllRequestPassPro();

        echo $this->twig->render('dashboard/dashboard.html.twig', [
            'requestPassProData' => $requestPassProData
        ]);
    }
}
