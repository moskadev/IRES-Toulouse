<?php

namespace generators;

use irestoulouse\generators\FileGenerator;

class CsvGenerator extends FileGenerator {

    const SEPARATOR = ";";

    private $output;

    public function __construct(string $fileName) {
        parent::__construct($fileName);

        $this->container = "";
        $this->output = false;
    }

    public function createRow(array $data, bool $title = false) {
        if($this->output !== false){
            fputcsv($this->output, $data, self::SEPARATOR);
        }
    }

    public function createBlankLines(int $quantity = 1) {
        while ($quantity > 0 && $this->output !== false){
            fputcsv($this->output, [], self::SEPARATOR);
            $quantity--;
        }
    }

    public function generate(array $users) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename={$this->fileName}.csv");
        header("Content-Transfer-Encoding: binary");
        header('Content-Encoding: UTF-8');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        /**
         * create a file pointer connected to the output stream
         * @var [type]
         */
        $this->output = fopen('php://output', 'w');

        parent::generate($users);

        fclose($this->output);
        exit();
    }
}