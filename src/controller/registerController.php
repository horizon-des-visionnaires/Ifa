<?php

namespace register;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/registerModel.php';

class registerController
{
    protected $twig;
    private $loader;
    private $registerModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->registerModel = new \register\registerModel();
    }

    public function register()
    {
        $error = $this->getRegisterData();
        echo $this->twig->render('register/register.html.twig', ['error' => $error]);
    }

    public function getRegisterData()
    {
        if (isset($_POST['submit'])) {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $email = $_POST['email'];
            $userPassword = $_POST['userPassword'];

            if (strlen($userPassword) < 8) {
                return "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if (!preg_match('/[A-Z]/', $userPassword) || !preg_match('/[a-z]/', $userPassword) || !preg_match('/[0-9]/', $userPassword)) {
                return "Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre.";
            }

            $hased_password = password_hash($userPassword, PASSWORD_DEFAULT);

            return $this->registerModel->insertRegisterData($firstName, $lastName, $email, $hased_password);
        }

        return null;
    }
}
