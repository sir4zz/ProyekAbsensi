<?php
/**
 * SimpleXLSX - Lightweight PHP Excel Parser
 * Version: 1.0 (Simplified for this project)
 */

class SimpleXLSX {
    private static $error = '';
    private $data = [];
    
    public static function parse($filename) {
        if (!file_exists($filename)) {
            self::$error = 'File tidak ditemukan';
            return false;
        }
        
        $instance = new self();
        
        try {
            // Buka file ZIP
            $zip = new ZipArchive;
            if ($zip->open($filename) !== TRUE) {
                self::$error = 'Gagal membuka file Excel';
                return false;
            }
            
            // Baca sharedStrings.xml (berisi teks)
            $sharedStrings = [];
            $sharedStringsXML = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXML) {
                $xml = simplexml_load_string($sharedStringsXML);
                foreach ($xml->si as $si) {
                    $sharedStrings[] = (string)$si->t;
                }
            }
            
            // Baca sheet1.xml (data worksheet)
            $sheetXML = $zip->getFromName('xl/worksheets/sheet1.xml');
            if (!$sheetXML) {
                self::$error = 'Sheet tidak ditemukan';
                $zip->close();
                return false;
            }
            
            $xml = simplexml_load_string($sheetXML);
            $rows = [];
            
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $value = '';
                    
                    // Cek tipe cell
                    if (isset($cell['t']) && (string)$cell['t'] == 's') {
                        // String dari sharedStrings
                        $index = (int)$cell->v;
                        $value = isset($sharedStrings[$index]) ? $sharedStrings[$index] : '';
                    } else {
                        // Nilai langsung
                        $value = (string)$cell->v;
                    }
                    
                    $rowData[] = $value;
                }
                $rows[] = $rowData;
            }
            
            $zip->close();
            $instance->data = $rows;
            return $instance;
            
        } catch (Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
    
    public function rows() {
        return $this->data;
    }
    
    public static function parseError() {
        return self::$error;
    }
}
?>