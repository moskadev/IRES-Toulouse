<?php

namespace generators;

use irestoulouse\generators\FileGenerator;

/**
 * CSV file's generator where a list of users is
 * downloaded by the user
 *
 * @version 2.0
 */
class CsvGenerator extends FileGenerator {

    const SEPARATOR = ";";

    /** @var resource|false */
    private $output;

    /**
     * Initializing the CSV output
     *
     * @param string $fileName CSV's filename
     */
    public function __construct(string $fileName) {
        parent::__construct($fileName);

        $this->container = "";
        $this->output = false;
    }

    /**
     * Creating a new row in the CSV table
     *
     * @param array $data all the cells
     * @param bool $title if those cells are a "title"
     */
    public function createRow(array $data, bool $title = false) : void {
        if ($this->output !== false) {
            fputcsv($this->output, $data, self::SEPARATOR);
        }
    }

    /**
     * Creation a quantity of n blank lines in the CSV table
     *
     * @param int $quantity quantity of blank lines
     */
    public function createBlankLines(int $quantity = 1) : void {
        while ($quantity > 0 && $this->output !== false) {
            fputcsv($this->output, [], self::SEPARATOR);
            $quantity --;
        }
    }

    /**
     * Generate and download the CSV file
     *
     * @param array $users list of users' data to be downloaded
     */
    public function generate(array $users) : void {
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