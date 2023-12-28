<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set cell values
$sheet->setCellValue('A1', 'Hello');
$sheet->setCellValue('B1', 'World !');

// Save to file
$writer = new Xlsx($spreadsheet);
$writer->save('sf2/sf2.xlsx');
