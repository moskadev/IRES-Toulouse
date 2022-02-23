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
     * TODO réadapter
     * @param array $users
     */
    public function generate(array $users) {
        ob_start();

        $fh = @fopen( "php://output", "w");
        fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Description: File Transfer");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$this->fileName}");
        header("Expires: 0");
        header("Pragma: public");

        fputcsv( $fh, array_map(function ($data) {
            return $data->getName();
        }, UserData::all(false)));
        foreach ($users as $user){
            fputcsv( $fh, array_map(function ($data) use ($user) {
                return $data->getValue($user);
            }, UserData::all(false)) );
        }
        ob_end_flush();

        exit;
    }
}