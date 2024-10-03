<?php

use \Phalcon\Di\Injectable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel extends Injectable {

    public function genera($datos) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if (!empty($datos)&&$datos[0]) {
            $char = 'A';
            foreach ($datos[0] as $key => $value) {
                $sheet->setCellValue($char . '1', $key);

                $char++;
            }
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'top' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startColor' => array(
                        'argb' => 'FFA0A0A0',
                    ),
                    'endColor' => array(
                        'argb' => 'FFFFFFFF',
                    ),
                ),
            );

            $sheet->getStyle('A1:' . --$char . '1')->applyFromArray($styleArray);
            $prefix='';
            $char = 'A';
            $index = 2;
            foreach ($datos as $dato) {

                foreach ($dato as $k => $v) {
                    $sheet->setCellValue($prefix.$char . $index, $v);
                    if($char=='Z'){
                        $char='A';
                        if($prefix=='')
                            $prefix='A';
                        else
                            $prefix++;
                    }
                    
                    $char++;
                }
                $char = 'A';
                $prefix='';
                $index++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function generaImpuestos($datos,$coeficiente) {
        $Source_File = '../public/layout_impuestos.xlsx';
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($Source_File);
        $totales=5;

        foreach ($datos as $k => $v) {
            $spreadsheet->setActiveSheetIndexByName($k);
            $sheet = $spreadsheet->getActiveSheet();
            
            $index=2;
            foreach($v as $valores) {
                $char = 'A';
                foreach($valores as $d) {
                $sheet->setCellValue($char.$index, $d);
               
                $char++;
                }
                $index++;
            }
            
            $spreadsheet->setActiveSheetIndexByName("Balance");
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue("B".$totales, "=SUMA($k!C2:$k!C$index)");
            $sheet->setCellValue("E".($totales), "=SUMA($k!D2:$k!D$index)");
            $sheet->setCellValue("H".($totales), "=SUMA($k!E2:$k!E$index)");
            $totales+=5;
        }
        
        
        
        $sheet->setCellValue("E20", $coeficiente);
        

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

}
