<?php

namespace generators;

use irestoulouse\controllers\UserInputData;
use irestoulouse\elements\input\UserData;
use irestoulouse\generators\FileGenerator;

class CsvGenerator extends FileGenerator {

    private $output = false;

    public function createRow(array $data) {
        if($this->output !== false){
            fputcsv($this->output, $data);
        }
    }

    public function createBlankLines(int $quantity = 1) {
        while ($quantity > 0 && $this->output !== false){
            fputcsv($this->output, []);
            $quantity--;
        }
    }

    public function generate(array $users) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename={$this->fileName}.csv");
        header("Content-Transfer-Encoding: binary");

        /**
         * create a file pointer connected to the output stream
         * @var [type]
         */
        $this->output = fopen('php://output', 'w');


        $this->createRow(array_map(function ($data) {
            return $data->getName();
        }, UserData::all()));
        foreach ($users as $user){
            $this->createRow(array_map(function ($data) use ($user) {
                return $data->getValue($user);
            }, UserData::all()));
        }

        fclose($this->output);
        exit();
    }
}