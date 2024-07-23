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

        if (isset($_POST['downloadPDFAndImages'])) {
            $this->downloadPDFAndImages($_POST['IdRequest']);
            return;
        }

        if (isset($_POST['requestValid'])) {
            $IdRequest = $_POST['IdRequest'];
            $this->dashboardModel->requestValid($IdRequest);
        }
    
        if (isset($_POST['requestInvalid'])) {
            $IdRequest = $_POST['IdRequest'];
            $rejectReason = $_POST['rejectReason'];
            $this->dashboardModel->requestInvalid($IdRequest, $rejectReason);
        }

        $requestPassProData = $this->dashboardModel->getAllRequestPassPro();

        echo $this->twig->render('dashboard/dashboard.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'requestPassProData' => $requestPassProData
        ]);
    }

    public function downloadPDFAndImages($IdRequest)
    {
        $requestPassProData = $this->dashboardModel->getRequestPassProById($IdRequest);
        if (!$requestPassProData) {
            http_response_code(404);
            exit("Request not found");
        }

        // Extraire les informations de prénom et nom
        $firstName = $requestPassProData['FirstName'];
        $lastName = $requestPassProData['LastName'];
        // Nettoyage des caractères non alphanumériques pour le nom du fichier
        $safeFirstName = preg_replace('/[^a-zA-Z0-9_]/', '_', $firstName);
        $safeLastName = preg_replace('/[^a-zA-Z0-9_]/', '_', $lastName);
        $zipFilename = "$safeFirstName._$safeLastName.zip";

        // Création d'un fichier ZIP en mémoire
        $zip = new ZipArchive();

        if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) {
            exit("Impossible d'ouvrir l'archive ZIP.");
        }

        // Ajouter les images au ZIP
        if (!empty($requestPassProData['IdentityCardRecto'])) {
            $zip->addFromString('IdentityCardRecto.png', base64_decode($requestPassProData['IdentityCardRecto']));
        }

        if (!empty($requestPassProData['IdentityCardVerso'])) {
            $zip->addFromString('IdentityCardVerso.png', base64_decode($requestPassProData['IdentityCardVerso']));
        }

        if (!empty($requestPassProData['UserPicture'])) {
            $zip->addFromString('UserPicture.png', base64_decode($requestPassProData['UserPicture']));
        }

        // Créer le PDF en mémoire
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Demande pour Passe Pro de ' . $firstName . ', ' . $lastName);
        $pdf->SetSubject('Détails de la demande de Passe Pro');
        $pdf->SetKeywords('Passe Pro, demande, PDF');
        $pdf->AddPage();

        $html = '<h1>Détails de la demande de Passe Pro</h1>';
        $html .= '<p><strong>Nom :</strong> ' . $lastName . '</p>';
        $html .= '<p><strong>Prénom :</strong> ' . $firstName . '</p>';
        $html .= '<p><strong>Âge :</strong> ' . $requestPassProData['UserAge'] . '</p>';
        $html .= '<p><strong>Métier :</strong> ' . $requestPassProData['UserJob'] . '</p>';
        $html .= '<p><strong>Email :</strong> ' . $requestPassProData['Email'] . '</p>';
        $html .= '<p><strong>Adresse :</strong> ' . $requestPassProData['UserAdress'] . '</p>';
        $html .= '<p><strong>Description :</strong> ' . $requestPassProData['Description'] . '</p>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Ajouter le PDF au ZIP
        $pdfContent = $pdf->Output('', 'S'); // Capture PDF output as a string
        $zip->addFromString('Request_Info.pdf', $pdfContent);

        // Fermer le ZIP
        $zip->close();

        // Envoyer le fichier ZIP au client
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
        header('Content-Length: ' . filesize($zipFilename));

        // Lire le fichier ZIP et envoyer le contenu
        readfile($zipFilename);

        // Supprimer le fichier ZIP temporaire
        unlink($zipFilename);

        exit();
    }
}
