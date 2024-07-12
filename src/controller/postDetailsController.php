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

        $postData = $this->postDetailsModel->getPost($idPost);
        $commentsData = $this->postDetailsModel->getComment($idPost);

        if (!empty($postData)) {
            $firstName = $postData[0]['FirstName'];
            $lastName = $postData[0]['LastName'];
        } else {
            $firstName = '';
            $lastName = '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId !== null) {
            $this->addCommentData($idPost);
        }

        $this->getDeletePostData();
        $this->getDeleteCommentData();

        echo $this->twig->render('postDetails/postDetails.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'postData' => $postData,
            'firstName' => $firstName,
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
}
