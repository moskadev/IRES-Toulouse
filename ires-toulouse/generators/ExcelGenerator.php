<?php

namespace irestoulouse\generators;

use irestoulouse\elements\input\UserData;

class ExcelGenerator extends FileGenerator {

    public function createRow(array $data) {
        $this->container .= implode("\t", $data) . "\n";
    }

    public function createBlankLines(int $quantity = 1) {
        $this->container .= str_repeat("\n", $quantity);
    }

    /**
     * @param array $users
     */
    public function generate(array $users) {
        ob_start();

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Description: File Transfer");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename={$this->fileName}.xls");
        header("Expires: 0");
        header("Pragma: public");

        parent::generate($users);

        echo $this->container;
        ob_end_flush();

        exit();
    }
}