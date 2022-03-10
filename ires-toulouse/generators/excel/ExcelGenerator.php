<?php

namespace irestoulouse\generators\excel;

use irestoulouse\generators\FileGenerator;

class ExcelGenerator extends FileGenerator {

    private XLSXWriter $writer;
    private string $fullFileName;

    public function __construct(string $fileName) {
        parent::__construct($fileName);

        $this->fullFileName = $this->fileName . ".xlsx";
        $this->writer = new XLSXWriter();
    }

    public function createRow(array $data, bool $title = false) {
        $styles["border"] = ["left,right,top,bottom"];
        if($title){
            $styles["font-style"] = "bold";
            $styles["fill"] = "#D7D7D7";
        }
        $this->writer->writeSheetRow("Utilisateurs", $data, $styles);
    }

    public function createBlankLines(int $quantity = 1) {
        $styles["border"] = ["left, right, top, bottom"];
        while ($quantity > 0){
            $this->writer->writeSheetRow("Utilisateurs", [], $styles);
            --$quantity;
        }
    }

    /**
     * @param array $users
     */
    public function generate(array $users) {
        ob_start();

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename={$this->fullFileName}");
        header("Content-Transfer-Encoding: binary");

        $this->writer->setAuthor("ires-toulouse");
        $this->writer->setCompany("IRES de Toulouse");

        parent::generate($users);
        $this->writer->writeToFile($this->fullFileName);

        ob_clean();
        flush();
        readfile($this->fullFileName);
        unlink($this->fullFileName);

        exit();
    }
}