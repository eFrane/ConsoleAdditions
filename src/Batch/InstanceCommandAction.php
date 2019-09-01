<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class InstanceCommandAction extends CommandAction
{
    public function __construct(Command $command, InputInterface $input)
    {
        $this->command = $command;
        $this->input = $input;
    }

    public function __toString(): string
    {
        $this->abortIfNoApplication();

        return trim(
            sprintf(
                '%s %s %s',
                $this->application->getName(),
                $this->command->getName(),
                $this->input
            )
        );
    }
}
