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

    public function getPost($searchQuery = '')
    {
        $query = "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views,
              User.IdUser, User.FirstName, User.LastName, User.ProfilPicture, User.IsPro 
              FROM Post 
              JOIN User ON Post.IdUser = User.IdUser";

        if ($searchQuery) {
            $query .= " WHERE Post.TitlePost LIKE :searchQuery 
                    OR User.FirstName LIKE :searchQuery 
                    OR User.LastName LIKE :searchQuery";
        }

        $query .= " ORDER BY Post.DatePost DESC";

        $stmt = $this->dsn->prepare($query);

        if ($searchQuery) {
            $searchQuery = "%{$searchQuery}%";
            $stmt->bindParam(':searchQuery', $searchQuery);
        }

        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            if (!isset($post['IdUser'])) {
                echo "Error: IdUser not found in post data. ";
                continue;
            }

            if ($post['ProfilPicture'] !== null) {
                $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
            } else {
                $post['ProfilPicture'] = '';
            }
            $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);

            if (!isset($post['IdPost'])) {
                echo "Error: IdPost not found in post data. ";
                continue;
            }

            $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
            $stmtPictures->bindParam(':IdPost', $post['IdPost']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($pictures as &$picture) {
                $picture = base64_encode($picture);
            }
            $post['PicturesPost'] = $pictures;

            if (isset($post['IdUser'])) {
                $post['IsLike'] = $this->getIsLike($post['IdUser'], $post['IdPost']);
                $post['IsFavorites'] = $this->getIsFavorites($post['IdUser'], $post['IdPost']);
            } else {
                $post['IsLike'] = false;
                $post['IsFavorites'] = false;
            }

            if (isset($post['IdPost'])) {
                $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
                $stmtLikes->bindParam(':IdPost', $post['IdPost']);
                $stmtLikes->execute();
                $totalLikes = $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
                $post['TotalLikes'] = $totalLikes;
            } else {
                $post['TotalLikes'] = 0;
            }
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

    public function getIsLike($IdUser, $IdPost)
    {
        try {
            $stmt = $this->dsn->prepare(
                "SELECT IsLike 
            FROM LikeFavorites 
            WHERE IdUser = :IdUser AND IdPost = :IdPost"
            );
            $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
            $stmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result !== false) {
                return (bool)$result['IsLike'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getIsFavorites($IdUser, $IdPost)
    {
        try {
            $stmt = $this->dsn->prepare(
                "SELECT IsFavorites 
            FROM LikeFavorites 
            WHERE IdUser = :IdUser AND IdPost = :IdPost"
            );
            $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
            $stmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result !== false) {
                return (bool)$result['IsFavorites'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function LikeData($IdUser, $IdPost)
    {
        try {
            $checkIsLike = "SELECT COUNT(*) FROM LikeFavorites WHERE IdUser = :IdUser AND IdPost = :IdPost";
            $execCheckIsLike = $this->dsn->prepare($checkIsLike);
            $execCheckIsLike->bindParam(':IdUser', $IdUser);
            $execCheckIsLike->bindParam(':IdPost', $IdPost);
            $execCheckIsLike->execute();

            $isLiked = $execCheckIsLike->fetchColumn() > 0;

            if ($isLiked) {
                $updateLike = "UPDATE LikeFavorites SET IsLike = NOT IsLike WHERE IdUser = :IdUser AND IdPost = :IdPost";
            } else {
                $updateLike = "INSERT INTO LikeFavorites (IdUser, IdPost, IsLike) VALUES (:IdUser, :IdPost, 1)";
            }

            $execUpdateLike = $this->dsn->prepare($updateLike);
            $execUpdateLike->bindParam(':IdUser', $IdUser);
            $execUpdateLike->bindParam(':IdPost', $IdPost);

            if ($execUpdateLike->execute()) {
                header("Location: /allPost");
                exit();
            } else {
                echo "Erreur lors de l'ajout ou de la suppression du like.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function FavoriteData($IdUser, $IdPost)
    {
        try {
            $checkFavorite = "SELECT IsFavorites FROM LikeFavorites WHERE IdUser = :IdUser AND IdPost = :IdPost";
            $stmt = $this->dsn->prepare($checkFavorite);
            $stmt->bindParam(':IdUser', $IdUser);
            $stmt->bindParam(':IdPost', $IdPost);
            $stmt->execute();

            $existingFavorite = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingFavorite) {
                $updateFavorite = "UPDATE LikeFavorites SET IsFavorites = NOT IsFavorites WHERE IdUser = :IdUser AND IdPost = :IdPost";
            } else {
                $updateFavorite = "INSERT INTO LikeFavorites (IdUser, IdPost, IsFavorites) VALUES (:IdUser, :IdPost, 1)";
            }

            $execUpdateFavorite = $this->dsn->prepare($updateFavorite);
            $execUpdateFavorite->bindParam(':IdUser', $IdUser);
            $execUpdateFavorite->bindParam(':IdPost', $IdPost);

            if ($execUpdateFavorite->execute()) {
                header("Location: /allPost");
                exit();
            } else {
                echo "Erreur lors de l'ajout ou de la suppression du favori.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}
