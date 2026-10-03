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
    protected ?Application $application = null;

    protected ?Command $command = null;

    protected ?InputInterface $input = null;

    /**
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(OutputInterface $output): int
    {
        // @phpstan-ignore-next-line - $command and $input are set by child classes before calling parent::execute()
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

    /**
     * @return void
     * @throws BatchException
     */
    public function abortIfNoApplication()
    {
        if (!$this->application instanceof Application) {
            throw BatchException::applicationMustNotBeNull();
        }
    }
}
