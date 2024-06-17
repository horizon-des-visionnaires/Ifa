<?php

namespace register;

use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/registerModel.php';

class registerController
{
    protected $twig;
    private $loader;
    private $registerModel;
    private $dsn;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->registerModel = new \register\registerModel();
    }

    public function connectDB()
    {
        $this->dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
        $this->dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Définit le mode d'erreur de PDO pour lancer des exceptions.
    }

    public function register()
    {
        $this->getRegisterData();
        echo $this->twig->render('register/register.html.twig');
    }

    public function getRegisterData()
    {
        if (isset($_POST['submit'])) {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $email = $_POST['email'];
            $userPassword = $_POST['userPassword'];

            if (strlen($userPassword) < 8) {
                echo "Le mot de passe doit contenir au moins 8 caractères.";
                return;
            }

            if (!preg_match('/[A-Z]/', $userPassword) || !preg_match('/[a-z]/', $userPassword) || !preg_match('/[0-9]/', $userPassword)) {
                echo "Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre.";
                return;
            }

            $hased_password = password_hash($userPassword, PASSWORD_DEFAULT);

            $this->registerModel->insertRegisterData($firstName, $lastName, $email, $hased_password);
        }
    }
}
