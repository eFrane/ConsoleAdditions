<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Command;


use EFrane\ConsoleAdditions\Exception\BatchException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Batch
 * @package EFrane\ConsoleAdditions\Command
 */
class Batch
{
    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var Application
     */
    protected $application = null;

    public function __construct(Application $application, OutputInterface $output)
    {
        $this->setOutput($output);
        $this->application = $application;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param array $commands
     */
    public function setCommands(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * @param string $commandWithSignature
     */
    public function add($commandWithSignature)
    {
        if (!is_string($commandWithSignature)) {
            throw BatchException::signatureExpected($commandWithSignature);
        }

        array_push($this->commands, $commandWithSignature);
    }

    public function addObject(Command $command, InputInterface $input)
    {
        array_push($this->commands, compact('command', 'input'));
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        if ($this->output->isVerbose()) {
            $commandCount = count($this->commands);
            $this->output->writeln("Running {$commandCount} commands...");
        }

        $returnValue = 0;

        foreach ($this->commands as $command) {
            $returnValue &= $this->runOne($command);
        }

        return $returnValue;
    }

    /**
     * @param string              $command
     * @param InputInterface|null $input
     * @return int
     * @throws \Exception
     */
    public function runOne($command, InputInterface $input = null)
    {
        if (is_array($command)) {
            extract($command);
        }

        if (is_string($command)) {
            $command = $this->createCommandFromString($command, $input);
        }

        if (is_null($input)) {
            throw BatchException::inputMustNotBeNull();
        }

        return $command->run($input, $this->output);
    }

    protected function createCommandFromString($commandWithSignature, InputInterface &$input = null)
    {
        $commandName = explode(' ', $commandWithSignature, 2)[0];

        $command = $this->application->get($commandName);

        $input = new StringInput($commandWithSignature);

        return $command;
    }
}