<?php

namespace dashboard;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class dashboardModel
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

    public function getAllRequestPassPro()
    {
        try {
            $stmt = $this->dsn->query("
                    SELECT 
                        rp.*, 
                        u.FirstName, 
                        u.LastName ,
                        u.Email
                    FROM 
                        RequestPassPro rp
                    LEFT JOIN 
                        User u 
                    ON 
                        rp.IdUser = u.IdUser
                    WHERE 
                        rp.IsRequestValid = 0
                ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!is_null($row['IdentityCardRecto'])) {
                    $row['IdentityCardRecto'] = base64_encode($row['IdentityCardRecto']);
                }
                if (!is_null($row['IdentityCardVerso'])) {
                    $row['IdentityCardVerso'] = base64_encode($row['IdentityCardVerso']);
                }
            }

            return $results;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function requestValid($IdRequest)
    {
        try {
            $this->dsn->beginTransaction();

            $updateRequestQuery = "UPDATE RequestPassPro SET IsRequestValid = 1 WHERE IdRequest = :IdRequest";
            $stmt = $this->dsn->prepare($updateRequestQuery);
            $stmt->execute([':IdRequest' => $IdRequest]);

            $getUserIdQuery = "SELECT IdUser FROM RequestPassPro WHERE IdRequest = :IdRequest AND IsRequestValid = 1";
            $stmt = $this->dsn->prepare($getUserIdQuery);
            $stmt->execute([':IdRequest' => $IdRequest]);
            $IdUser = $stmt->fetchColumn();

            if ($IdUser) {
                $updateUserQuery = "UPDATE User SET IsPro = 1 WHERE IdUser = :IdUser";
                $stmt = $this->dsn->prepare($updateUserQuery);
                $stmt->execute([':IdUser' => $IdUser]);
            }

            $this->dsn->commit();

            header("Location: /dashboard");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }
}
