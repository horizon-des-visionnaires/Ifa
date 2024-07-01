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
        $stmt = $this->dsn->prepare("SELECT * FROM Post");
        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            if ($post['PicturePost'] !== null) {
                $post['PicturePost'] = base64_encode($post['PicturePost']);
            } else {
                $post['PicturePost'] = '';
            }
        }

        return $getPostData;
    }
}
