<?php

namespace adviceMeeting;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class adviceMeetingModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    public function getBuyAdviceData($IdBuyAdvice)
    {
        try {
            $query = "
            SELECT 
                A.IdAdvice,
                A.AdviceType,
                A.AdviceDescription,
                C.CategoryName,
                BA.IdBuyAdvice,
                BA.Date AS BuyAdviceDate,
                BA.StartTime AS BuyAdviceStartTime,
                BA.EndTime AS BuyAdviceEndTime,
                BA.IsAdviceValid,
                U1.IdUser AS SellerId,
                U1.FirstName AS SellerFirstName,
                U1.LastName AS SellerLastName,
                U2.IdUser AS BuyerId,
                U2.FirstName AS BuyerFirstName,
                U2.LastName AS BuyerLastName,
                U1.ProfilPicture AS SellerProfilPicture,
                U2.ProfilPicture AS BuyerProfilPicture
            FROM BuyAdvice BA
            INNER JOIN Advice A ON BA.IdAdvice = A.IdAdvice
            INNER JOIN Category C ON A.IdCategory = C.IdCategory
            INNER JOIN User U1 ON A.IdUser = U1.IdUser -- Seller
            INNER JOIN User U2 ON BA.IdBuyer = U2.IdUser -- Buyer
            WHERE BA.IdBuyAdvice = :idBuyAdvice
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':idBuyAdvice', $IdBuyAdvice, PDO::PARAM_INT);
            $stmt->execute();

            $getAdviceData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($getAdviceData === false) {
                echo "No data found for ID: " . htmlspecialchars($IdBuyAdvice);
                return null;
            }

            if ($getAdviceData) {
                $getAdviceData['SellerProfilPicture'] = $getAdviceData['SellerProfilPicture'] ? base64_encode($getAdviceData['SellerProfilPicture']) : '';
                $getAdviceData['BuyerProfilPicture'] = $getAdviceData['BuyerProfilPicture'] ? base64_encode($getAdviceData['BuyerProfilPicture']) : '';
            }

            return $getAdviceData;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function getAdviceImages($IdAdvice)
    {
        try {
            $query = "
        SELECT PictureAdvice
        FROM PictureAdvice
        WHERE IdAdvice = :idAdvice
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':idAdvice', $IdAdvice, PDO::PARAM_INT);
            $stmt->execute();

            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Encode images to base64
            foreach ($images as &$image) {
                $image['PictureAdvice'] = base64_encode($image['PictureAdvice']);
            }

            return $images;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    public function updateAdviceValidity($idBuyAdvice, $satisfaction)
    {
        try {
            $query = "
        UPDATE BuyAdvice
        SET IsAdviceValid = :satisfaction
        WHERE IdBuyAdvice = :idBuyAdvice
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':satisfaction', $satisfaction, PDO::PARAM_INT);
            $stmt->bindParam(':idBuyAdvice', $idBuyAdvice, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function insertNotations($IdUserIsPro, $IdUser, $Note, $CommentNote)
    {
        try {
            $checkQuery = "SELECT COUNT(*) AS count FROM Notations WHERE IdUser = :IdUser AND IdUserIsPro = :IdUserIsPro AND DATE(DateNotation) = CURDATE()";
            $checkStmt = $this->dsn->prepare($checkQuery);
            $checkStmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
            $checkStmt->bindParam(':IdUserIsPro', $IdUserIsPro, PDO::PARAM_INT);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return false;
            } else {
                $insertNotations = "INSERT INTO Notations (Note, CommentNote, IdUser, IdUserIsPro) VALUES (:Note, :CommentNote, :IdUser, :IdUserIsPro)";
                $Notations = $this->dsn->prepare($insertNotations);
                $Notations->bindParam(':Note', $Note, PDO::PARAM_INT);
                $Notations->bindParam(':CommentNote', $CommentNote);
                $Notations->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
                $Notations->bindParam(':IdUserIsPro', $IdUserIsPro, PDO::PARAM_INT);
                $Notations->execute();
                return true;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
