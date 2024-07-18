<?php

namespace allPost;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class allPostModel
{
    private $dsn;

    public function __construct()
    {
        $this->connectDB();
    }

    public function connectDB()
    {
        $this->dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
        $this->dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function addPost($TitlePost, $ContentPost, $PicturesPost, $IdUser)
    {
        try {
            $this->dsn->beginTransaction();

            $stmt = $this->dsn->prepare("INSERT INTO Post (TitlePost, ContentPost, IdUser) VALUES (:TitlePost, :ContentPost, :IdUser)");
            $stmt->bindParam(':TitlePost', $TitlePost);
            $stmt->bindParam(':ContentPost', $ContentPost);
            $stmt->bindParam(':IdUser', $IdUser);
            $stmt->execute();
            $IdPost = $this->dsn->lastInsertId();

            $stmt = $this->dsn->prepare("INSERT INTO PicturePost (IdPost, PicturePost) VALUES (:IdPost, :PicturePost)");
            foreach ($PicturesPost as $PicturePost) {
                $stmt->bindParam(':IdPost', $IdPost);
                $stmt->bindParam(':PicturePost', $PicturePost, PDO::PARAM_LOB);
                $stmt->execute();
            }

            $this->dsn->commit();
            return $IdPost;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    public function getPost()
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, User.FirstName, User.LastName, User.ProfilPicture 
            FROM Post 
            JOIN User ON Post.IdUser = User.IdUser"
        );
        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            if ($post['ProfilPicture'] !== null) {
                $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
            } else {
                $post['ProfilPicture'] = '';
            }
            $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);

            $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
            $stmtPictures->bindParam(':IdPost', $post['IdPost']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($pictures as &$picture) {
                $picture = base64_encode($picture);
            }
            $post['PicturesPost'] = $pictures;
        }

        return $getPostData;
    }

    private function getRelativeTime($date)
    {
        $timestamp = strtotime($date);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return $diff . ' s';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' m';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' h';
        } else {
            return floor($diff / 86400) . ' J';
        }
    }

    public function updateViews($idPost)
    {
        try {
            $stmt = $this->dsn->prepare("UPDATE Post SET Views = Views + 1 WHERE IdPost = :IdPost");
            $stmt->bindParam(':IdPost', $idPost);


            if ($stmt->execute()) {
                header("Location: /postDetails-$idPost");
                exit();
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
