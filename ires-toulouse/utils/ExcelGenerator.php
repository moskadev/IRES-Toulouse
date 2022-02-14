<?php

namespace irestoulouse\utils;

class ExcelGenerator {

    private string $fileName;
    private string $container = "";

    public function __construct(string $fileName){
        $this->fileName = $fileName;
    }

    public function createRow(array $data){
        $this->container .= implode("\t", $data) . "\n";
    }

    public function createBlankLines(int $quantity = 1){
        $this->container .= str_repeat("\n", $quantity);
    }

    public function generate(){
        header('Content-Description: File Transfer');
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$this->fileName}.xls");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        print $this->container;
        exit;
    }
}