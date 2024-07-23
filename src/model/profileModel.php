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

    public function getUserPosts($id)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views, Post.IdUser, User.FirstName, User.LastName, User.ProfilPicture 
            FROM Post 
            JOIN User ON Post.IdUser = User.IdUser
            WHERE User.IdUser = :id
            ORDER BY Post.DatePost DESC"
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

            $post['IsLike'] = $this->getIsLike($post['IdUser'], $post['IdPost']);
            $post['IsFavorites'] = $this->getIsFavorites($post['IdUser'], $post['IdPost']);

            $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
            $stmtLikes->bindParam(':IdPost', $post['IdPost']);
            $stmtLikes->execute();
            $totalLikes = $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
            $post['TotalLikes'] = $totalLikes;
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

    public function deletePost($idPost, $idUser)
    {
        try {
            $this->dsn->beginTransaction();

            $deletePicturePost = "DELETE FROM PicturePost WHERE IdPost = :IdPost";
            $picturePost = $this->dsn->prepare($deletePicturePost);
            $picturePost->bindParam(':IdPost', $idPost);
            $picturePost->execute();

            $deleteComment = "DELETE FROM Comment WHERE IdPost = :IdPost";
            $comment = $this->dsn->prepare($deleteComment);
            $comment->bindParam(':IdPost', $idPost);
            $comment->execute();

            $deleteLike = "DELETE FROM `Like` WHERE IdPost = :IdPost";
            $like = $this->dsn->prepare($deleteLike);
            $like->bindParam(':IdPost', $idPost);
            $like->execute();

            $deletePost = "DELETE FROM Post WHERE IdPost = :IdPost";
            $stmt = $this->dsn->prepare($deletePost);
            $stmt->bindParam(':IdPost', $idPost);
            $stmt->execute();

            $this->dsn->commit();

            header("Location: /profile-$idUser");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function insertRequestPassProData($Job, $Age, $Description, $idUser, $Adress, $identityCardRecto = null, $identityCardVerso = null, $UserPicture = null)
    {
        try {
            $this->dsn->beginTransaction();

            $insertData = "INSERT INTO RequestPassPro (IdUser, UserJob, UserAge, Description, IdentityCardRecto, IdentityCardVerso, UserPicture, UserAdress) VALUE (:IdUser, :UserJob, :UserAge, :Description, :IdentityCardRecto, :IdentityCardVerso, :UserPicture, :UserAdress)";
            $stmt = $this->dsn->prepare($insertData);
            $stmt->bindParam(':IdUser', $idUser);
            $stmt->bindParam(':UserJob', $Job);
            $stmt->bindParam(':UserAge', $Age);
            $stmt->bindParam(':Description', $Description);
            $stmt->bindParam(':IdentityCardRecto', $identityCardRecto, PDO::PARAM_LOB);
            $stmt->bindParam(':IdentityCardVerso', $identityCardVerso, PDO::PARAM_LOB);
            $stmt->bindParam(':UserPicture', $UserPicture, PDO::PARAM_LOB);
            $stmt->bindParam(':UserAdress', $Adress);
            $stmt->execute();

            $this->dsn->commit();

            header("Location: /profile-$idUser");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
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
                header("Location: /profile-$IdUser");
                exit();
            } else {
                echo "Erreur lors de l'ajout ou de la suppression du like.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
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
                header("Location: /profile-$IdUser");
                exit();
            } else {
                echo "Erreur lors de l'ajout ou de la suppression du favori.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
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

    public function getUserFavorites($id)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views, Post.IdUser, User.FirstName, User.LastName, User.ProfilPicture 
        FROM Post 
        JOIN User ON Post.IdUser = User.IdUser
        JOIN LikeFavorites ON Post.IdPost = LikeFavorites.IdPost
        WHERE LikeFavorites.IdUser = :id AND LikeFavorites.IsFavorites = 1
        ORDER BY Post.DatePost DESC"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $getFavPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getFavPostData as &$postFav) {
            if ($postFav['ProfilPicture'] !== null) {
                $postFav['ProfilPicture'] = base64_encode($postFav['ProfilPicture']);
            } else {
                $postFav['ProfilPicture'] = '';
            }
            $postFav['RelativeDatePost'] = $this->getRelativeTime($postFav['DatePost']);

            $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
            $stmtPictures->bindParam(':IdPost', $postFav['IdPost']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($pictures as &$picture) {
                $picture = base64_encode($picture);
            }
            $postFav['PicturesPost'] = $pictures;

            $postFav['IsLike'] = $this->getIsLike($postFav['IdUser'], $postFav['IdPost']);
            $postFav['IsFavorites'] = true;

            $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
            $stmtLikes->bindParam(':IdPost', $postFav['IdPost']);
            $stmtLikes->execute();
            $totalLikes = $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
            $postFav['TotalLikes'] = $totalLikes;
        }

        return $getFavPostData;
    }
}
