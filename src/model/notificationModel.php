<?php

namespace notification;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class notificationModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    // fonction pour récupérer toutes les notification en fonctions de l'id d'un user
    public function getUserNotifications($userId)
    {
        try {
            $stmt = $this->dsn->prepare("
            SELECT IdNotification, MessageNotif, IsRead, CreatedAt 
            FROM Notifications 
            WHERE IdUser = :IdUser 
            ORDER BY CreatedAt DESC
        ");
            $stmt->bindParam(':IdUser', $userId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // fonction pour marqué les notifications comme lues
    public function markNotificationAsRead($notificationId)
    {
        try {
            $stmt = $this->dsn->prepare("
            UPDATE Notifications 
            SET IsRead = 1, ReadAt = NOW() 
            WHERE IdNotification = :IdNotification
        ");
            $stmt->bindParam(':IdNotification', $notificationId);
            $stmt->execute();

            header("Location: /notification");
            exit();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // fonction pour compter le nombre de notifications non lues
    public function getUnreadNotificationCount($userId)
    {
        try {
            $stmt = $this->dsn->prepare("
            SELECT COUNT(*) as count
            FROM Notifications 
            WHERE IdUser = :IdUser 
            AND IsRead = 0
        ");
            $stmt->bindParam(':IdUser', $userId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return 0;
        }
    }

    // function pour récupérer les données d'un user en fonction de son id
    public function getUserInfo($userId)
    {
        try {
            $query = "
        SELECT FirstName, LastName
        FROM User
        WHERE IdUser = :userId
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    // fonction pour supprimer les notifications marqué comme lue
    public function deleteExpiredNotifications()
    {
        try {
            $stmt = $this->dsn->prepare("
            DELETE FROM Notifications 
            WHERE IsRead = 1 AND ReadAt < NOW() - INTERVAL 1 DAY
        ");
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}
