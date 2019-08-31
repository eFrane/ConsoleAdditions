<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use EFrane\ConsoleAdditions\Exception\BatchException;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CommandAction implements Action
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var InputInterface
     */
    protected $input = null;

    /**
     * CommandAction constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(OutputInterface $output): int
    {
        if (is_null($this->input)) {
            throw BatchException::inputMustNotBeNull();
        }

        return $this->command->run($this->input, $output);
    }
}
