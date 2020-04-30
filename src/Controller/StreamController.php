<?php


namespace App\Controller;


use App\Repository\UserRepository;
use App\Services\DataExport;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StreamController
 * @package App\Controller
 * @Route(path="/stream")
 */
class StreamController
{
    /**
     * @Route(path="/export", name="stream_export")
     * @param DataExport $dataExport
     */
    public function export(DataExport $dataExport, UserRepository $userRepository)
    {
        $fileName = "utilisateurs_".date('Ymd-Hi').'.csv';

        $tempPath = sys_get_temp_dir().'/exports/';
        $uniqTime = time();
        $publicPath = __DIR__."../../../public/exports/{$uniqTime}/";
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($tempPath)) {
            try {
                $fileSystem->mkdir($tempPath);
            } catch (IOExceptionInterface $exception) {
                echo "Impossible de créer le dossier à ".$exception->getPath();
            }
        }
        $fileSystem->touch($tempPath.$fileName);
        $tempFile = new File($tempPath.$fileName);

        $writer = $dataExport->writeData($tempFile, $userRepository->findAll());
        $fileStream = fopen($tempFile, 'r+');

        $response = new StreamedResponse(function() use ($tempFile) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = fopen($tempFile, 'r');
            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Charset', 'utf-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->setCharset('utf-8');
        return $response;
    }
}