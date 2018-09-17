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
 * Batch
 *
 * This class offers batching commands of a Symfony Console Application. This can be
 * useful when writing things like deployment or update scripts as console commands
 * which call many other commands in a set order e.g. cache updating, database
 * migrations, etc.
 *
 * Usage in a `Command::execute`:
 *
 * <code>
 * Batch::create($this->getApplication(), $output)
 *     ->add('my:command --with-option')
 *     ->add('my:other:command for-this-input')
 *     ->run();
 * </code>
 *
 * Exceptions occurring in commands are cascaded upwards. It is also possible to
 * just use this as a command string parser by creating an instance and calling
 * `$command = $batch->createCommandFromString('my:command --with -a --weird signature', $input);`
 *
 * @package EFrane\ConsoleAdditions\Command
 */
class Batch
{
    const ALLOWED_COMMAND_ARRAY_KEYS = ['command', 'input', 'process'];

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

    public static function create(Application $application, OutputInterface $output)
    {
        return new self($application, $output);
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
     * @return $this;
     */
    public function add($commandWithSignature, ...$args)
    {
        if (!is_string($commandWithSignature)) {
            throw BatchException::signatureExpected($commandWithSignature);
        }

        // transparent vsprintf
        if (count($args) > 0) {
            $commandWithSignature = vsprintf($commandWithSignature, $args);
        }

        array_push($this->commands, $commandWithSignature);

        return $this;
    }

    /**
     * @param string               $command
     * @param string               $cwd
     * @param array                $env
     * @param resource|string|null $input
     * @param float                $timeout
     * @return $this
     * @throws BatchException
     */
    public function addShell($command, $cwd = null, $env = null, $input = null, $timeout = 60.0)
    {
        $this->checkShell();

        $process = new \Symfony\Component\Process\Process($command, $cwd, $env, $input, $timeout);
        array_push($this->commands, compact('process'));

        return $this;
    }

    /**
     * Checks whether symfony/process is available
     * @throws BatchException
     */
    public function checkShell()
    {
        if (!class_exists('Symfony\Component\Process\Process')) {
            throw BatchException::missingSymfonyProcess();
        }
    }

    /**
     * @param string   $cmd
     * @param callable $configurationCallback (Symfony\Component\Process\Process $process)
     * @return $this
     * @throws BatchException
     */
    public function addShellCb($cmd, callable $configurationCallback)
    {
        $this->checkShell();

        $process = new \Symfony\Component\Process\Process($cmd);
        $process = call_user_func($configurationCallback, $process);

        array_push($this->commands, compact('process'));

        return $this;
    }

    /**
     * @param Command        $command
     * @param InputInterface $input
     * @return $this
     */
    public function addObject(Command $command, InputInterface $input)
    {
        array_push($this->commands, compact('command', 'input'));

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
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
     * @param string|array        $command
     * @param InputInterface|null $input
     * @return int
     * @throws \Exception|BatchException
     */
    public function runOne($command, InputInterface $input = null)
    {
        if (is_array($command)
        ) {
            //  the amount of values in command equals the amount of keys which are in the amount of allowed keys
            if (count($command) === array_intersect(array_keys($command), self::ALLOWED_COMMAND_ARRAY_KEYS)) {
                throw BatchException::commandArrayFormatMismatch($command);
            }

            extract($command);
        }

        if (is_string($command)) {
            $command = $this->createCommandFromString($command, $input);
        }

        if (is_null($input) && !isset($process)) {
            throw BatchException::inputMustNotBeNull();
        }

        if (isset($process)) {
            return $this->runProcess($process);
        }

        return $command->run($input, $this->output);
    }

    public function createCommandFromString($commandWithSignature, InputInterface &$input = null)
    {
        $commandName = explode(' ', $commandWithSignature, 2)[0];

        $command = $this->application->get($commandName);

        $input = new StringInput($commandWithSignature);

        return $command;
    }

    /**
     * @param Process $process
     * @return int
     */
    public function runProcess(\Symfony\Component\Process\Process $process)
    {
        $process->mustRun();
        $process->enableOutput();

        $process->run(function ($type, $out) {
            // TODO: allow access to process stderr
            if ('out' === $type) {
                $this->output->write($out);
            }
        });

        return $process->getExitCode();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode("\n", array_map(function ($command) {
            $commandAsString = '';

            if (is_string($command)) {
                $commandAsString = sprintf(
                    '%s %s',
                    $this->application->getName(),
                    $command
                );
            }

            if (is_array($command)) {
                extract($command);

                if (isset($process)) {
                    /** @var \Symfony\Component\Process\Process $process */

                    $commandAsString = $process->getCommandLine();
                }

                if (isset($command, $input)) {
                    /** @var Command $command */
                    /** @var InputInterface $input */

                    $commandAsString = sprintf(
                        '%s %s %s',
                        $this->application->getName(),
                        $command->getName(),
                        $input
                    );
                }
            }

            return trim($commandAsString);
        }, $this->commands));
    }
}
