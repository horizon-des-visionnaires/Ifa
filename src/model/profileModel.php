<?php

namespace profile;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class profileModel
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

    public function getUserById($id)
    {
        $stmt = $this->dsn->prepare("SELECT * FROM User WHERE IdUser = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $getUserData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($getUserData === false) {
            return null;
        }

        foreach ($getUserData as $key => &$User) {
            if ($key === 'ProfilPicture') {
                if ($User !== null) {
                    $User = base64_encode($User);
                } else {
                    $User = '';
                }
            }
        }

        return $getUserData;
    }


    public function updateUserData($IdUser, $FirstName, $LastName, $ProfilDescription, $ProfilPicture = null)
    {
        try {
            $setClauses = [];

            if (!empty($FirstName)) {
                $setClauses[] = "FirstName = :FirstName";
            }
            if (!empty($LastName)) {
                $setClauses[] = "LastName = :LastName";
            }
            if (!empty($ProfilDescription)) {
                $setClauses[] = "ProfilDescription = :ProfilDescription";
            }
            if (!empty($ProfilPicture)) {
                $setClauses[] = "ProfilPicture = :ProfilPicture";
            }

            if (empty($setClauses)) {
                echo "Aucun champ à mettre à jour.";
                return;
            }

            $query = "UPDATE User SET " . implode(', ', $setClauses) . " WHERE IdUser = :IdUser";
            $stmt = $this->dsn->prepare($query);

            $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);

            if (!empty($FirstName)) {
                $stmt->bindParam(':FirstName', $FirstName);
            }
            if (!empty($LastName)) {
                $stmt->bindParam(':LastName', $LastName);
            }
            if (!empty($ProfilDescription)) {
                $stmt->bindParam(':ProfilDescription', $ProfilDescription);
            }
            if (!empty($ProfilPicture)) {
                $stmt->bindParam(':ProfilPicture', $ProfilPicture, PDO::PARAM_LOB);
            }

            if ($stmt->execute()) {
                header("Location: /profile-$IdUser");
                exit();
            } else {
                echo "Échec de la mise à jour.";
            }
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function getLinkUserData($id)
    {
        $stmt = $this->dsn->prepare("SELECT * FROM LinkUser WHERE IdUser = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $getUserLinkData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $getUserLinkData;
    }

    public function getUserPosts($id)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, User.FirstName, User.LastName, User.ProfilPicture 
            FROM Post 
            JOIN User ON Post.IdUser = User.IdUser
            WHERE User.IdUser = :id"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
}
