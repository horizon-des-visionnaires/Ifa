<?php

namespace allPost;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/allPostModel.php';

class allPostController
{
    protected $twig;
    private $loader;
    private $allPostModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->allPostModel = new \allPost\allPostModel();
    }

    public function allPost()
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId !== null) {
            $this->getAddPostData($userId);
        }

        $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
        $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

        $postData = $this->allPostModel->getFilteredPosts($searchQuery, $sortBy, $order);
        $this->getAddViewsData();
        $this->getDataAddLike();
        $this->getDataAddFavorite();

        echo $this->twig->render('allPost/allPost.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'postData' => $postData,
            'searchQuery' => $searchQuery,
            'sortBy' => $sortBy,
            'order' => $order
        ]);
    }

    public function getAddPostData($userId)
    {
        if (isset($_POST['addPost'])) {
            $TitlePost = $_POST['TitlePost'];
            $ContentPost = $_POST['ContentPost'];

            $PicturesPost = [];
            if (isset($_FILES["PicturePost"])) {
                if (count($_FILES["PicturePost"]["tmp_name"]) > 5) {
                    echo "You can upload a maximum of 5 images.";
                    return;
                }
                foreach ($_FILES["PicturePost"]["tmp_name"] as $tmpName) {
                    if ($tmpName) {
                        $PicturesPost[] = file_get_contents($tmpName);
                    }
                }
            }

            $IdPost = $this->allPostModel->addPost($TitlePost, $ContentPost, $PicturesPost, $userId);

            if ($IdPost) {
                header("Location: /allPost");
                exit();
            }
        }
    }

    public function getAddViewsData()
    {
        if (isset($_POST['viewMore'])) {
            $idPost = $_POST['idPost'];

            $this->allPostModel->updateViews($idPost);
        }
    }

    public function getDataAddLike()
    {
        if (isset($_POST['AddLike'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->allPostModel->LikeData($IdUser, $IdPost);
            }
        }
    }

    public function getDataAddFavorite()
    {
        if (isset($_POST['AddFavorite'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->allPostModel->FavoriteData($IdUser, $IdPost);
            }
        }
    }
}
