<?php


namespace App\Controller;


use App\Repository\UserRepository;
use App\Services\DataExport;
use Symfony\Component\Filesystem\Filesystem;
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
        $path = __DIR__."../../public/exports/";
        $file = $path.$fileName;
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($path)) {
            file_put_contents($file, '');
            $fileSystem->tempnam($path, $fileName);
        }
        $writer = $dataExport->writeData($file, $userRepository->findAll());
        file_put_contents($file, $writer);
        $fileStream = fopen($file, 'r+');
        dd($writer, $fileName, $file, $fileStream);
        // just shows text, need to send csv file
        return new StreamedResponse(function() use ($path, $fileName) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = fopen('/tmp/'.$fileName, 'r');
            stream_copy_to_stream($fileStream, $outputStream);
        });
    }
}