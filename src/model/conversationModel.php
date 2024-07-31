<?php

namespace conversation;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class conversationModel
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

    public function getConvById($userId)
    {
        $stmt = $this->dsn->prepare("SELECT FirstName, LastName, IsPro, ProfilPicture, IsAdmin FROM User WHERE IdUser = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
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

    public function getUsersByConversation($userId)
    {
        $stmt = $this->dsn->prepare("
            SELECT u.FirstName, u.LastName, u.IsPro, u.ProfilPicture
            FROM Conversations c
            JOIN User u ON c.IdUser_1 = u.IdUser
            WHERE c.IdUser_2 = :userId
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usersData as &$userData) {
            if ($userData['ProfilPicture'] !== null) {
                $userData['ProfilPicture'] = base64_encode($userData['ProfilPicture']);
            } else {
                $userData['ProfilPicture'] = '';
            }
        }

        return $usersData;
    }
}
