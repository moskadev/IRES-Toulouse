<?php

namespace irestoulouse\generators;

use irestoulouse\elements\input\UserData;

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
    public abstract function generate(array $users);
}