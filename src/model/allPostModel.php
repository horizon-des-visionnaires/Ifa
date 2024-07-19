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

    public function getFilteredPosts($searchQuery = '', $sortBy = '', $order = 'DESC')
    {
        $query = $this->buildQuery($searchQuery, $sortBy, $order);
        $stmt = $this->dsn->prepare($query);

        if ($searchQuery) {
            $searchQuery = "%{$searchQuery}%";
            $stmt->bindParam(':searchQuery', $searchQuery);
        }

        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            $this->processPost($post);
        }

        return $getPostData;
    }

    private function buildQuery($searchQuery, $sortBy, $order)
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

        switch ($sortBy) {
            case 'likes':
                $query .= " ORDER BY (SELECT COUNT(*) FROM LikeFavorites WHERE IdPost = Post.IdPost AND IsLike = 1) $order";
                break;
            case 'views':
                $query .= " ORDER BY Post.Views $order";
                break;
            case 'comments':
                $query .= " ORDER BY (SELECT COUNT(*) FROM Comment WHERE IdPost = Post.IdPost) $order";
                break;
            case 'date':
            default:
                $query .= " ORDER BY Post.DatePost $order";
                break;
        }

        return $query;
    }

    private function processPost(&$post)
    {
        $post['ProfilPicture'] = $this->processProfilePicture($post['ProfilPicture']);
        $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);
        $post['PicturesPost'] = $this->getPictures($post['IdPost']);
        $post['IsLike'] = $this->getIsLike($post['IdUser'], $post['IdPost']);
        $post['IsFavorites'] = $this->getIsFavorites($post['IdUser'], $post['IdPost']);
        $post['TotalLikes'] = $this->getTotalLikes($post['IdPost']);
    }

    private function processProfilePicture($picture)
    {
        return $picture !== null ? base64_encode($picture) : '';
    }

    private function getPictures($idPost)
    {
        $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
        $stmtPictures->bindParam(':IdPost', $idPost);
        $stmtPictures->execute();
        $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

        return array_map('base64_encode', $pictures);
    }

    private function getTotalLikes($idPost)
    {
        $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
        $stmtLikes->bindParam(':IdPost', $idPost);
        $stmtLikes->execute();
        return $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
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

            return $result !== false ? (bool)$result['IsLike'] : false;
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

            return $result !== false ? (bool)$result['IsFavorites'] : false;
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
