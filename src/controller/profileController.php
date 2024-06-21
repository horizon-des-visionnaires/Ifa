<?php

namespace profile;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/profileModel.php';

class profileController
{
    protected $twig;
    private $loader;
    private $profileModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->profileModel = new profileModel();
    }

    public function profile($id)
    {
        session_start();

        $isConnected = false;
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        }

        $user = $this->profileModel->getUserById($id);

        if ($user) {
            echo $this->twig->render('profile/profile.html.twig', [
                'user' => $user,
                'isConnected' => $isConnected,
                'userId' => $userId
            ]);
        } else {
            http_response_code(404);
            echo "User not found";
        }
    }
}
