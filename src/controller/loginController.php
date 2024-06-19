<?php

namespace login;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/loginModel.php';

class loginController
{
    protected $twig;
    private $loader;
    private $loginModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->loginModel = new \login\loginModel();
    }

    public function login()
    {
        session_start();
        // var_dump($_SESSION);
        $error = $this->getLoginData();
        echo $this->twig->render('login/login.html.twig', ['error' => $error]);
    }

    public function getLoginData()
    {
        if (isset($_POST['submit'])) {
            $email = $_POST['email'];
            $userPassword = $_POST['userPassword'];
            if (empty($email) || empty($userPassword)) {
                return "Veuillez remplir tous les champs";
            } else {
                return $this->loginModel->checkLoginData($email, $userPassword);
            }
        }
        return null;
    }
}
