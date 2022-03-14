<?php

namespace irestoulouse\generators;

use irestoulouse\data\UserCustomDataFactory;

/**
 * File's generator where a list of users is
 * downloaded by the user
 *
 * @version 2.0
 */
abstract class FileGenerator {

    /** @var string */
    protected string $fileName;
    /** @var mixed */
    protected $container;

    /**
     * Initializing the filename
     *
     * @param string $fileName filename
     */
    public function __construct(string $fileName) {
        $this->fileName = $fileName . "_" . date('d-m-Y');
    }

    /**
     * Creation a quantity of n blank lines in the CSV table
     *
     * @param int $quantity quantity of blank lines
     */
    public abstract function createBlankLines(int $quantity = 1);

    /**
     * Generate and download the file
     *
     * @param array $users list of users' data to be downloaded
     */
    public function generate(array $users) : void {
        $this->createRow(array_map(function ($data) {
            return $data->getName();
        }, UserCustomDataFactory::all()), true
        );
        foreach ($users as $user) {
            $this->createRow(array_map(function ($data) use ($user) {
                return $data->getValue($user);
            }, UserCustomDataFactory::all())
            );
        }
    }

    /**
     * Creating a new row in the file table
     *
     * @param array $data all the cells
     * @param bool $title if those cells are a "title"
     */
    public abstract function createRow(array $data, bool $title = false);
}