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

        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        } else {
            $isConnected = false;
            $userId = null;
        }

        $IsAdmin = false;
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }

        $user = $this->profileModel->getUserById($id);

        if ($user === null) {
            http_response_code(404);
            echo "User not found";
            return;
        }

        $this->updateUserData($id);
        $userPost = $this->profileModel->getUserPosts($id);
        $this->getDeletePostData();
        $this->getRequestPassProData();
        $this->getDataAddLike();
        $this->getDataAddFavorite();
        $this->getAddViewsData();

        $postFav = $this->profileModel->getUserFavorites($id);
        $messages = $this->profileModel->getUserMessages($id);

        $this->profileModel->cleanupOldData();


        echo $this->twig->render('profile/profile.html.twig', [
            'user' => $user,
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'userPost' => $userPost,
            'postFav' => $postFav,
            'messages' => $messages
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

    public function getDeletePostData()
    {
        if (isset($_POST['deletePost'])) {
            $idPost = $_POST['idPost'];
            $idUser = $_POST['idUser'];
            $this->profileModel->deletePost($idPost, $idUser);
        }
    }

    public function getRequestPassProData()
    {
        if (isset($_POST['pushRequest'])) {
            $Job = $_POST['Job'];
            $Age = $_POST['Age'];
            $Description = $_POST['Description'];
            $idUser = $_POST['idUser'];

            $identityCardRecto = null;
            $identityCardVerso = null;
            $UserPicture = null;

            $Adress = $_POST['Adress'];

            if (isset($_FILES["identityCardRecto"]) && $_FILES["identityCardRecto"]["error"] == UPLOAD_ERR_OK) {
                $identityCardRecto = file_get_contents($_FILES["identityCardRecto"]["tmp_name"]);
            }
            if (isset($_FILES["identityCardVerso"]) && $_FILES["identityCardVerso"]["error"] == UPLOAD_ERR_OK) {
                $identityCardVerso = file_get_contents($_FILES["identityCardVerso"]["tmp_name"]);
            }
            if (isset($_FILES["UserPicture"]) && $_FILES["UserPicture"]["error"] == UPLOAD_ERR_OK) {
                $UserPicture = file_get_contents($_FILES["UserPicture"]["tmp_name"]);
            }

            $this->profileModel->insertRequestPassProData($Job, $Age, $Description, $idUser, $Adress, $identityCardRecto, $identityCardVerso, $UserPicture);
        }
    }

    public function getDataAddLike()
    {
        if (isset($_POST['AddLike'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->profileModel->LikeData($IdUser, $IdPost);
            }
        }
    }

    public function getDataAddFavorite()
    {
        if (isset($_POST['AddFavorite'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->profileModel->FavoriteData($IdUser, $IdPost);
            }
        }
    }

    public function getAddViewsData()
    {
        if (isset($_POST['viewMore'])) {
            $idPost = $_POST['idPost'];

            $this->profileModel->updateViews($idPost);
        }
    }
}