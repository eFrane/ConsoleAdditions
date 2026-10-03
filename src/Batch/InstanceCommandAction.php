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
    public function __construct(Command $command, ?InputInterface $input = null)
    {
        $this->command = $command;

        if (is_null($input)) {
            $input = new ArrayInput([]);
        }

        $this->input = $input;
    }

    public function __toString(): string
    {
        $this->abortIfNoApplication();

        $inputString = '';
        if (is_callable([$this->input, '__toString'])) {
            $inputString = $this->input->__toString();
        }

        return trim(
            sprintf(
                '%s %s %s',
                // @phpstan-ignore-next-line - $application is guaranteed to be set by abortIfNoApplication()
                $this->application->getName(),
                // @phpstan-ignore-next-line - $command is guaranteed to be set by constructor
                $this->command->getName(),
                $inputString
            )
        );
    }
}
