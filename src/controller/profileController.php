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
        $userLink = $this->profileModel->getLinkUserData($id);

        if ($user === null) {
            http_response_code(404);
            echo "User not found";
            return;
        }

        $this->updateUserData($id);
        $userPost = $this->profileModel->getUserPosts($id);

        echo $this->twig->render('profile/profile.html.twig', [
            'user' => $user,
            'isConnected' => $isConnected,
            'userId' => $userId,
            'userLink' => $userLink,
            'userPost' => $userPost
        ]);
    }


    public function updateUserData($id)
    {
        if (isset($_POST['updateProfile'])) {
            $FirstName = $_POST['FirstName'] ?? null;
            $LastName = $_POST['LastName'] ?? null;
            $ProfilDescription = $_POST['ProfilDescription'] ?? null;

            $ProfilPicture = null;

            if (isset($_FILES["ProfilPicture"]) && $_FILES["ProfilPicture"]["error"] == UPLOAD_ERR_OK) {
                $ProfilPicture = file_get_contents($_FILES["ProfilPicture"]["tmp_name"]);
            }

            $this->profileModel->updateUserData($id, $FirstName, $LastName, $ProfilDescription, $ProfilPicture);
        }
    }
}
