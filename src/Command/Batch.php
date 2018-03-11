<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Command;


use Symfony\Component\Console\Application;
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
        array_push($this->commands, $commandWithSignature);
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $returnValue = 0;

        foreach ($this->commands as $command) {
            $returnValue &= $this->runOne($command);
        }
    }

    /**
     * @param $commandWithSignature
     * @return int
     * @throws \Exception
     */
    public function runOne($commandWithSignature)
    {
        $commandName = explode(' ', $commandWithSignature, 2)[0];

        $command = $this->application->get($commandName);

        $input = new StringInput($commandWithSignature);

        return $command->run($input, $this->output);
    }
}