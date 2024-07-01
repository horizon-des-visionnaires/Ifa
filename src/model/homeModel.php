<?php

namespace home;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class homeModel
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

    public function addPost($TitlePost, $ContentPost, $PicturePost, $IdUser)
    {
        try {
            $stmt = $this->dsn->prepare("INSERT INTO Post (TitlePost, ContentPost, PicturePost, IdUser) VALUES (:TitlePost, :ContentPost, :PicturePost, :IdUser)");
            $stmt->bindParam(':TitlePost', $TitlePost);
            $stmt->bindParam(':ContentPost', $ContentPost);
            $stmt->bindParam(':PicturePost', $PicturePost, PDO::PARAM_LOB);
            $stmt->bindParam(':IdUser', $IdUser);
            $stmt->execute();
            return $this->dsn->lastInsertId();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    public function getPost()
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.TitlePost, Post.ContentPost, Post.PicturePost, Post.DatePost, User.FirstName, User.LastName, User.ProfilPicture 
        FROM Post 
        JOIN User ON Post.IdUser = User.IdUser"
        );
        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            if ($post['PicturePost'] !== null) {
                $post['PicturePost'] = base64_encode($post['PicturePost']);
            } else {
                $post['PicturePost'] = '';
            }
            if ($post['ProfilPicture'] !== null) {
                $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
            } else {
                $post['ProfilPicture'] = '';
            }
            $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);
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
}
