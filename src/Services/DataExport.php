<?php


namespace App\Services;


use App\Entity\User;
use EasyCSV\Writer;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\Serializer\SerializerInterface;

class DataExport
{
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function exportCSV(array $data)
    {
        return $this->serializer->serialize($data, 'csv', ['groups' => ['csv_export']]);
    }

    public function getNewSpreadSheet(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data as $user) {
            $sheet->setCellValueExplicitByColumnAndRow(1, 1, $user->getEmail(), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(1, 2, $user->getFirstName(), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(1, 3, $user->getLastName(), DataType::TYPE_STRING);
        }
        return $spreadsheet;
    }

    public function writeData(string $path, array $users)
    {

        $writer = new Writer($path);
        // create and save csv file
        $writer->setDelimiter('|');
        //$writer->writeRow(['email', 'firstname', 'lastname']);
        foreach ($users as $user) {
            $writer->writeRow([$user->getEmail(),$user->getFirstName(),$user->getLastName()]);
        }
        return $writer;
    }

    public function saveNewCsv(Spreadsheet $spreadsheet, string $fileName)
    {
        $csv = new Csv($spreadsheet);
        try {
            $csv->save($fileName);
        } catch (Exception $e) {
        }
        return $csv;
    }

    public function convertObjectToArray(User $user)
    {
        try {
            $reflexion = new \ReflectionClass($user);
        } catch (\ReflectionException $e) {
        }
    }

    public function outputCSV(array $data, $useKeysForHeaderRow = true) {
        if ($useKeysForHeaderRow) {
            array_unshift($data, array_keys(reset($data)));
        }

        $outputBuffer = fopen("php://output", 'w');
        foreach($data as $d) {
            $v = get_object_vars($d);
            fputcsv($outputBuffer, $v);
        }
        fclose($outputBuffer);
        return $outputBuffer;
    }
}