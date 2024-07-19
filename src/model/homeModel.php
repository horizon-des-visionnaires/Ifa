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

    public function get5UserProRandom()
    {
        try {
            $stmt = $this->dsn->query("
                SELECT 
                    IdUser,
                    FirstName, 
                    LastName, 
                    ProfilPicture, 
                    ProfilDescription
                FROM User
                WHERE IsPro = 1
                ORDER BY RAND()
                LIMIT 5
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!is_null($row['ProfilPicture'])) {
                    $row['ProfilPicture'] = base64_encode($row['ProfilPicture']);
                }
            }

            return $results;
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function get5RandomPostsFromTop10()
    {
        try {
            $stmt = $this->dsn->query("
            SELECT 
                Post.IdPost, 
                Post.TitlePost, 
                Post.ContentPost, 
                Post.DatePost, 
                Post.Views, 
                Post.IdUser,
                User.FirstName,
                User.LastName,
                User.ProfilPicture,
                User.IsPro
            FROM Post
            INNER JOIN User ON Post.IdUser = User.IdUser
            ORDER BY Post.Views DESC
            LIMIT 10
        ");
            $top10Posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($top10Posts) > 5) {
                $randomKeys = array_rand($top10Posts, 5);
                $randomPosts = array_intersect_key($top10Posts, array_flip($randomKeys));
            } else {
                $randomPosts = $top10Posts;
            }

            foreach ($randomPosts as &$post) {
                if (!is_null($post['ProfilPicture'])) {
                    $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
                }
                $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);
            }

            return $randomPosts;
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
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
