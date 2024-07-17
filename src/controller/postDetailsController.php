<?php

namespace postDetails;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/postDetailsModel.php';

class postDetailsController
{
    protected $twig;
    private $loader;
    private $postDetailsModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->postDetailsModel = new postDetailsModel();
    }

    public function post($idPost)
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

        $postData = $this->postDetailsModel->getPost($idPost);
        $commentsData = $this->postDetailsModel->getComment($idPost);

        foreach ($postData as &$post) {
            $post['IsLike'] = $this->postDetailsModel->getIsLike($userId, $post['IdPost']);
            $post['IsFavorites'] = $this->postDetailsModel->getIsFavorites($userId, $post['IdPost']);
        }

        if (!empty($postData)) {
            $firstName = $postData[0]['FirstName'];
            $lastName = $postData[0]['LastName'];
            $isPro = $postData[0]['IsPro'];
        } else {
            $firstName = '';
            $lastName = '';
            $isPro = '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId !== null) {
            $this->addCommentData($idPost);
        }

        $this->getDeletePostData();
        $this->getDeleteCommentData();
        $this->getDataAddLike();
        $this->getDataAddFavorite();

        echo $this->twig->render('postDetails/postDetails.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'postData' => $postData,
            'firstName' => $firstName,
            'isPro' => $isPro,
            'lastName' => $lastName,
            'commentsData' => $commentsData
        ]);
    }

    public function addCommentData($idPost)
    {
        if (isset($_POST['addComment'])) {
            $ContentComment = $_POST['ContentComment'];
            $IdUser = $_POST['IdUser'];

            $IdComment = $this->postDetailsModel->addComment($idPost, $ContentComment, $IdUser);

            if ($IdComment) {
                header("Location: /postDetails-$idPost");
                exit();
            }
        }
    }

    public function getDeletePostData()
    {
        if (isset($_POST['deletePost'])) {
            $idPost = $_POST['idPost'];
            $this->postDetailsModel->deletePost($idPost);
        }
    }

    public function getDeleteCommentData()
    {
        if (isset($_POST['deletePostComment'])) {
            $idComment = $_POST['idComment'];
            $idPost = $_POST['idPost'];
            $this->postDetailsModel->deleteComment($idComment, $idPost);
        }
    }

    public function getDataAddLike()
    {
        if (isset($_POST['AddLike'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->postDetailsModel->LikeData($IdUser, $IdPost);
            }
        }
    }

    public function getDataAddFavorite()
    {
        if (isset($_POST['AddFavorite'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->postDetailsModel->FavoriteData($IdUser, $IdPost);
            }
        }
    }
}
