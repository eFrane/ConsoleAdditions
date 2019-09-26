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
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(OutputInterface $output): int
    {
        return $this->command->run($this->input, $output);
    }

    /**
     * @param Application $application
     * @return CommandAction
     */
    public function setApplication(Application $application): CommandAction
    {
        $this->application = $application;

        return $this;
    }

    public function abortIfNoApplication()
    {
        if (!is_a($this->application, Application::class)) {
            throw BatchException::applicationMustNotBeNull();
        }
    }
}
