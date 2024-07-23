<?php

namespace dashboard;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use TCPDF;
use ZipArchive;

require 'vendor/autoload.php';
require 'vendor/tecnickcom/tcpdf/tcpdf.php';

require_once __DIR__ . '/../model/dashboardModel.php';

class dashboardController
{
    protected $twig;
    private $loader;
    private $dashboardModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->dashboardModel = new dashboardModel();
    }

    public function dashboard()
    {
        session_start();

        $isConnected = false;
        $userId = null;
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        }

        $IsAdmin = false;
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }

        if (!$IsAdmin) {
            header("Location: /");
            exit();
        }

        if (isset($_POST['downloadImages'])) {
            $this->downloadImages($_POST['IdRequest']);
            return;
        }

        if (isset($_POST['downloadInfoPDF'])) {
            $this->downloadRequestInfoAsPDF($_POST['IdRequest']);
            return;
        }

        if (isset($_POST['requestValid'])) {
            $IdRequest = $_POST['IdRequest'];
            $this->dashboardModel->requestValid($IdRequest);
        }

        $requestPassProData = $this->dashboardModel->getAllRequestPassPro();

        echo $this->twig->render('dashboard/dashboard.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'requestPassProData' => $requestPassProData
        ]);
    }

    public function downloadRequestInfoAsPDF($IdRequest)
    {
        $requestPassProData = $this->dashboardModel->getRequestPassProById($IdRequest);
        if (!$requestPassProData) {
            http_response_code(404);
            echo "Request not found";
            exit();
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Demande pour Passe Pro de ' . $requestPassProData['FirstName'] . ', ' . $requestPassProData['LastName']);
        $pdf->SetSubject('Détails de la demande de Passe Pro');
        $pdf->SetKeywords('Passe Pro, demande, PDF');

        $pdf->AddPage();

        $html = '<h1>Détails de la demande de Passe Pro</h1>';
        $html .= '<p><strong>Nom :</strong> ' . $requestPassProData['LastName'] . '</p>';
        $html .= '<p><strong>Prénom :</strong> ' . $requestPassProData['FirstName'] . '</p>';
        $html .= '<p><strong>Âge :</strong> ' . $requestPassProData['UserAge'] . '</p>';
        $html .= '<p><strong>Métier :</strong> ' . $requestPassProData['UserJob'] . '</p>';
        $html .= '<p><strong>Email :</strong> ' . $requestPassProData['Email'] . '</p>';
        $html .= '<p><strong>Description :</strong> ' . $requestPassProData['Description'] . '</p>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $lastName = $requestPassProData['LastName'];
        $firstName = $requestPassProData['FirstName'];
        $filename = 'Demande_pour_passez_pro_de_' . $firstName . '_' . $lastName . '.pdf';

        $pdf->Output($filename, 'D');
        exit();
    }

    public function downloadImages($IdRequest)
    {
        $requestPassProData = $this->dashboardModel->getRequestPassProById($IdRequest);
        if (!$requestPassProData) {
            http_response_code(404);
            echo "Request not found";
            exit();
        }

        $zip = new ZipArchive();
        $filename = "Images_$IdRequest.zip";

        // Création de l'archive ZIP en mémoire
        if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
            exit("Impossible d'ouvrir l'archive ZIP.");
        }

        // Ajouter les images à l'archive ZIP
        if (!empty($requestPassProData['IdentityCardRecto'])) {
            $zip->addFromString('IdentityCardRecto.png', base64_decode($requestPassProData['IdentityCardRecto']));
        }

        if (!empty($requestPassProData['IdentityCardVerso'])) {
            $zip->addFromString('IdentityCardVerso.png', base64_decode($requestPassProData['IdentityCardVerso']));
        }

        if (!empty($requestPassProData['UserPicture'])) {
            $zip->addFromString('UserPicture.png', base64_decode($requestPassProData['UserPicture']));
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);

        unlink($filename);

        exit();
    }
}
