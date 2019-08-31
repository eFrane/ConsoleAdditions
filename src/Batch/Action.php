<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use Symfony\Component\Console\Output\OutputInterface;

interface Action
{
    public function execute(OutputInterface $output): int;
    public function __toString(): string;
}
