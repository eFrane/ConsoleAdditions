<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use EFrane\ConsoleAdditions\Exception\BatchException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class StringCommandAction extends CommandAction
{
    protected string $commandString;

    /**
     * StringCommandAction constructor.
     *
     * @param string           $commandString
     * @param array<int,mixed> $args
     */
    public function __construct(string $commandString, ...$args)
    {
        // transparent vsprintf
        if (count($args) > 0) {
            /** @var array<bool|float|int|string|null> $args */
            $commandString = vsprintf($commandString, $args);
        }

        $this->commandString = $commandString;
    }

    public function execute(OutputInterface $output): int
    {
        $this->createCommandFromString();

        return parent::execute($output);
    }

    /**
     * @return void
     * @throws BatchException
     */
    public function createCommandFromString()
    {
        $this->abortIfNoApplication();

        $commandName = explode(' ', $this->commandString, 2)[0];

        // @phpstan-ignore-next-line - $application is guaranteed to be set by abortIfNoApplication()
        $this->command = $this->application->get($commandName);
        $this->input = new StringInput($this->commandString);
    }

    /**
     * @return string
     * @throws BatchException
     */
    public function __toString(): string
    {
        $this->abortIfNoApplication();

        return trim(
            sprintf(
                '%s %s',
                // @phpstan-ignore-next-line - $application is guaranteed to be set by abortIfNoApplication()
                $this->application->getName(),
                $this->commandString
            )
        );
    }
}
