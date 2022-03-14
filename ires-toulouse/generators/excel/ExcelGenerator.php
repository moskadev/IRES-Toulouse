<?php

namespace irestoulouse\generators\excel;

use irestoulouse\generators\FileGenerator;

/**
 * Excel file's generator where a list of users is
 * downloaded by the user
 *
 * @version 2.0
 */
class ExcelGenerator extends FileGenerator {

    public const SHEET_NAME = "Utilisateurs";

    private XLSXWriter $writer;

    /**
     * Initializing the Excel writer
     *
     * @param string $fileName Excel's filename
     */
    public function __construct(string $fileName) {
        parent::__construct($fileName);
        /**
         * FROM : https://github.com/mk-j/PHP_XLSXWriter
         */
        $this->writer = new XLSXWriter();
    }

    /**
     * Creating a new row in the Excel table
     *
     * @param array $data all the cells
     * @param bool $title if those cells are a "title"
     */
    public function createRow(array $data, bool $title = false) : void {
        $styles["border"] = ["left,right,top,bottom"];
        if ($title) {
            $styles["font-style"] = "bold";
            $styles["fill"] = "#D7D7D7";
        }
        $this->writer->writeSheetRow(self::SHEET_NAME, $data, $styles);
    }

    /**
     * Creation a quantity of n blank lines in the Excel table
     *
     * @param int $quantity quantity of blank lines
     */
    public function createBlankLines(int $quantity = 1) : void {
        $styles["border"] = ["left,right,top,bottom"];
        while ($quantity > 0) {
            $this->writer->writeSheetRow(self::SHEET_NAME, [], $styles);
            -- $quantity;
        }
    }

    /**
     * Generate and download the Excel file
     *
     * @param array $users list of users' data to be downloaded
     */
    public function generate(array $users) : void {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename={$this->fileName}.xlsx");
        header("Content-Transfer-Encoding: binary");

        $this->writer->setAuthor("ires-toulouse");
        $this->writer->setCompany("IRES de Toulouse");

        parent::generate($users);
        $this->writer->writeToStdOut();

        exit();
    }
}