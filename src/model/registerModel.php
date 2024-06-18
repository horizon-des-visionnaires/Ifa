<?php

namespace register;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class registerModel
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

    public function insertRegisterData($firstName, $lastName, $email, $hased_password)
    {
        try {
            $checkEmailAndPhoneNumber = "SELECT COUNT(*) FROM User WHERE Email = :email";
            $stmt = $this->dsn->prepare($checkEmailAndPhoneNumber);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return "Cet email est déjà utilisé.";
            } else {
                $insertRegister = "INSERT INTO User (FirstName, LastName, Email, UserPassword) VALUES (:FirstName, :LastName, :Email, :UserPassword)";
                $stmt2 = $this->dsn->prepare($insertRegister);
                $stmt2->bindParam(':FirstName', $firstName);
                $stmt2->bindParam(':LastName', $lastName);
                $stmt2->bindParam(':Email', $email);
                $stmt2->bindParam(':UserPassword', $hased_password);

                if ($stmt2->execute()) {
                    header("Location: /login");
                    exit;
                } else {
                    return 'Erreur lors de l\'inscription';
                }
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }
}
