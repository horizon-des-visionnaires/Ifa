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
}
