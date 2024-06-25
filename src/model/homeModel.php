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

    public function addPost($TitlePost, $ContentPost, $PicturePost)
    {
        try {
            $stmt = $this->dsn->prepare("INSERT INTO Post (TitlePost, ContentPost, PicturePost) VALUES (:TitlePost, :ContentPost, :PicturePost)");
            $stmt->bindParam(':TitlePost', $TitlePost);
            $stmt->bindParam(':ContentPost', $ContentPost);
            $stmt->bindParam(':PicturePost', $PicturePost, PDO::PARAM_LOB);
            $stmt->execute();
            return $this->dsn->lastInsertId();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function addUserPost($userId, $IdPost)
    {
        try {
            $stmt = $this->dsn->prepare("INSERT INTO UserPost (IdUser, IdPost) VALUES (:IdUser, :IdPost)");
            $stmt->bindParam(':IdUser', $userId);
            $stmt->bindParam(':IdPost', $IdPost);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
