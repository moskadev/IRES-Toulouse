<?php

namespace irestoulouse\generators;

use irestoulouse\elements\data\UserData;

abstract class FileGenerator {

    protected string $fileName;
    protected string $container = "";

    public function __construct(string $fileName) {
        $this->fileName = $fileName . "_" . date('d-m-Y');
    }

    public abstract function createRow(array $data);

    public abstract function createBlankLines(int $quantity = 1);

    /**
     * @param array $users
     */
    public function generate(array $users) {
        $this->createRow(array_map(function ($data) {
            return $data->getName();
        }, UserData::all()));
        foreach ($users as $user){
            $this->createRow(array_map(function ($data) use ($user) {
                return $data->getValue($user);
            }, UserData::all()));
        }
    }
}