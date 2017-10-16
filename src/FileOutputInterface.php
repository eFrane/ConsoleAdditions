<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions;


interface FileOutputInterface
{
    /**
     * Append output to the end of the file
     */
    const WRITE_MODE_APPEND = 1024;

    /**
     * Empty the file before first output is written
     */
    const WRITE_MODE_RESET = 2048;

    /**
     * @param $filename
     * @return mixed
     */
    public function loadFileStream($filename);
}