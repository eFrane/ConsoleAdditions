<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class StringCommandAction extends CommandAction
{
    /**
     * @var string
     */
    protected $commandString;

    /**
     * StringCommandAction constructor.
     *
     * @param Application $application
     * @param string      $commandString
     * @param array       $args
     */
    public function __construct(Application $application, string $commandString, ...$args)
    {
        parent::__construct($application);

        // transparent vsprintf
        if (count($args) > 0) {
            $commandString = vsprintf($commandString, $args);
        }

        $this->commandString = $commandString;
    }

    public function execute(OutputInterface $output): int
    {
        $this->createCommandFromString();

        return parent::execute($output);
    }

    public function createCommandFromString()
    {
        $commandName = explode(' ', $this->commandString, 2)[0];

        $this->command = $this->application->get($commandName);
        $this->input = new StringInput($this->commandString);
    }

    public function __toString(): string
    {
        return trim(
            sprintf(
                '%s %s',
                $this->application->getName(),
                $this->commandString
            )
        );
    }
}
