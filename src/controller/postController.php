<?php

namespace post;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/postModel.php';

class postController
{
    protected $twig;
    private $loader;
    private $postModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->postModel = new postModel();
    }

    public function post($idPost)
    {
        session_start();

        $isConnected = false;
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        }

        $postData = $this->postModel->getPost($idPost);
        $commentsData = $this->postModel->getComment($idPost);

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

        echo $this->twig->render('post/post.html.twig', [
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

            $IdComment = $this->postModel->addComment($idPost, $ContentComment, $IdUser);

            if ($IdComment) {
                header("Location: /post-$idPost");
                exit();
            }
        }
    }
}
