<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

class InstanceCommandAction extends CommandAction
{
    public function __construct(Command $command, InputInterface $input = null)
    {
        $this->command = $command;

        $this->input = $input;
        if (is_null($input)) {
            $this->input = new ArrayInput([]);
        }
    }

    public function __toString(): string
    {
        $this->abortIfNoApplication();

        $inputString = '';
        if (method_exists($this->input, '__toString')) {
            $inputString = $this->input->__toString();
        }

        return trim(
            sprintf(
                '%s %s %s',
                $this->application->getName(),
                $this->command->getName(),
                $inputString
            )
        );
    }
}
