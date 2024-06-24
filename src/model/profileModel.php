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
}
