<?php

namespace irestoulouse\utils;

use irestoulouse\elements\input\UserData;

class ExcelGenerator {

    private string $fileName;
    private string $container = "";

    public function __construct(string $fileName) {
        $this->fileName = $fileName;
    }

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

        $this->createRow(array_map(function ($data) {
            return $data->getName();
        }, UserData::all(false)));
        foreach ($users as $user){
            $this->createRow(array_map(function ($data) use ($user) {
                return $data->getValue($user);
            }, UserData::all(false)));
        }

        echo $this->container;
        ob_end_flush();

        exit();
    }
}