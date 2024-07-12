<?php

namespace postDetails;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class postDetailsModel
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

    public function getPost($idPost)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.IdUser, Post.TitlePost, Post.ContentPost, Post.DatePost, User.FirstName, User.LastName, User.ProfilPicture 
            FROM Post 
            JOIN User ON Post.IdUser = User.IdUser
            WHERE Post.IdPost = :idPost"
        );
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
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

    public function getComment($idPost)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Comment.ContentComment, Comment.DateComment, Comment.IdUser, Comment.IdComment, User.FirstName, User.LastName, User.ProfilPicture
        FROM Comment
        JOIN User ON Comment.IdUser = User.IdUser
        WHERE Comment.IdPost = :idPost"
        );
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($comments as &$comment) {
            if ($comment['ProfilPicture'] !== null) {
                $comment['ProfilPicture'] = base64_encode($comment['ProfilPicture']);
            } else {
                $comment['ProfilPicture'] = '';
            }
            $comment['RelativeDateComment'] = $this->getRelativeTime($comment['DateComment']);
        }

        return $comments;
    }

    public function addComment($idPost, $ContentComment, $IdUser)
    {
        try {

            $this->dsn->beginTransaction();

            $stmt = $this->dsn->prepare("INSERT INTO Comment (ContentComment, IdUser, IdPost) VALUES (:ContentComment, :IdUser, :idPost)");
            $stmt->bindParam(':ContentComment', $ContentComment);
            $stmt->bindParam(':IdUser', $IdUser);
            $stmt->bindParam(':idPost', $idPost);
            $stmt->execute();
            $IdComment = $this->dsn->lastInsertId();

            $this->dsn->commit();
            return $IdComment;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    public function deletePost($idPost)
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

            header("Location: /");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function deleteComment($idComment, $idPost)
    {
        try {
            $this->dsn->beginTransaction();

            $deleteComment = "DELETE FROM Comment WHERE IdComment = :IdComment";
            $stmt = $this->dsn->prepare($deleteComment);
            $stmt->bindParam(':IdComment', $idComment);
            $stmt->execute();

            $this->dsn->commit();

            header("Location: /postDetails-$idPost");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }
}
