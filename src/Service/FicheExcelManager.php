<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class FicheExcelManager
{
    public function exportExcel(array $results, int $trimestre): Response {
         $matieres = $results[4];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default empty sheet

        foreach ($matieres as $key => $value) {
            // Create new sheet for each subject
            $sheet = $spreadsheet->createSheet();
            if($value->getNom() == "Allemand/Espagnol"){
                $sheet->setTitle("Allemand ou Esagnol");
            }
            else{
                $sheet->setTitle($value->getNom());
            }

            // Write header row
            $sheet->fromArray(['Noms', 'Prénoms', 'Moy.interro', 'Devoir 1', 'Devoir 2'], null, 'A1');

            // Write data rows
            $row = 2;
            foreach ($results[0] as $eleveInfos) {
                // dd($eleveInfos);
                $sheet->fromArray([
                    $eleveInfos['eleve']->getNom(),
                    $eleveInfos['eleve']->getPrenom(),
                    $eleveInfos['matieres'][$value->getId()]['notes']['MI'],
                    $eleveInfos['matieres'][$value->getId()]['notes']['D1'],
                    $eleveInfos['matieres'][$value->getId()]['notes']['D2'],
                ], null, "A$row");
                $row++;
            }
        }

        // Save the Excel file
        $excelFilename = sys_get_temp_dir() . '/' . $results[1]->getNom() . '_Trimestre-' . $trimestre .'_'. $results[1]->getAnneeScolaire()->getAnnee(). '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelFilename);

        // Send file as response
        $response = new BinaryFileResponse($excelFilename);
        $response->setContentDisposition('attachment', basename($excelFilename));

        // Delete file after sending
        $response->deleteFileAfterSend(true);

        return $response;
    }
}