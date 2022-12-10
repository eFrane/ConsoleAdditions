<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Batch;


use EFrane\ConsoleAdditions\Exception\BatchException;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use function strlen;

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
    protected OutputInterface $output;

    /**
     * @var array|Action[]
     */
    protected array $actions = [];

    protected Application $application;

    protected ?Exception $lastException;

    protected ReturnCodeStack $returnCodeStack;

    protected bool $hasRun;

    /**
     * Batch constructor.
     *
     * @param Application     $application
     * @param OutputInterface $output
     */
    public function __construct(Application $application, OutputInterface $output)
    {
        $this->setOutput($output);
        $this->application = $application;
        $this->returnCodeStack = new ReturnCodeStack();
    }

    /**
     * @param Application     $application
     * @param OutputInterface $output
     * @return Batch
     */
    public static function create(Application $application, OutputInterface $output): self
    {
        return new self($application, $output);
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     * @return Batch
     */
    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->lastException instanceof Exception;
    }

    public function getLastException(): ?Exception
    {
        return $this->lastException;
    }

    /**
     * @param Exception $e
     * @return void
     */
    protected function setLastException(Exception $e)
    {
        $this->lastException = $e;
    }

    /**
     * @return array|Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array|Action[] $actions
     * @return void
     */
    public function setActions(array $actions)
    {
        foreach ($actions as $action) {
            if (!is_a($action, Action::class)) {
                BatchException::invalidActionSet();
            }

            $this->addAction($action);
        }
    }

    /**
     * @param Action $action
     * @return Batch
     */
    public function addAction(Action $action): self
    {
        if ($action instanceof CommandAction) {
            $action->setApplication($this->application);
        }

        array_push($this->actions, $action);

        return $this;
    }

    /**
     * @param string             $commandWithSignature
     * @param array<int, string> $args
     * @return Batch
     */
    public function add(string $commandWithSignature, ...$args): self
    {
        return $this->addAction(new StringCommandAction($commandWithSignature, ...$args));
    }

    public function addMessage(
        string $message,
        bool $newLine = true,
        int $verbosity = OutputInterface::VERBOSITY_NORMAL
    ): self {
        return $this->addAction(new MessageAction($message, $newLine, $verbosity));
    }

    /**
     * @param array<int, string> $command
     * @param string                    $cwd
     * @param array<int,string>         $env
     * @param resource|string|null      $input
     * @param int                       $timeout
     * @return Batch
     */
    public function addShell(array $command, string $cwd = null, array $env = null, $input = null, int $timeout = 0): self
    {
        return $this->addAction(new ShellAction($command, $cwd, $env, $input, $timeout));
    }

    /**
     * @param array<int,string>|string[] $shellCommand
     * @param callable              $configurationCallback (Symfony\Component\Process\Process $process)
     * @return self
     */
    public function addShellCb(array $shellCommand, callable $configurationCallback): self
    {
        $action = new ShellAction($shellCommand);

        call_user_func($configurationCallback, $action->getProcess());

        return $this->addAction($action);
    }

    /**
     * @param Command        $command
     * @param InputInterface $input
     * @return self
     */
    public function addCommandInstance(Command $command, InputInterface $input): self
    {
        return $this->addAction(new InstanceCommandAction($command, $input));
    }

    /**
     * @return int
     */
    public function runSilent(): int
    {
        try {
            return $this->run();
        } catch (Exception $e) {
            $this->setLastException($e);

            return -1;
        }
    }

    /**
     * @return int
     * @throws Exception
     */
    public function run(): int
    {
        $actionCount = count($this->actions);
        $this->output->writeln("Running {$actionCount} actions...", OutputInterface::VERBOSITY_VERBOSE);

        // marking hasRun true before any actual run ensures exceptions will be output correctly
        $this->hasRun = true;

        foreach ($this->actions as $action) {
            $this->returnCodeStack->push($this->runOne($action));
        }

        return $this->returnCodeStack->getLastReturnCode();
    }

    /**
     * Check if all actions from a `run()` or `runSilent()`
     * did return successful.
     *
     * @return bool
     */
    public function allActionsSucceeded(): bool
    {
        return $this->returnCodeStack->allSuccessful();
    }

    /**
     * Check if any action from a `run()` or `runSilent()`
     * did return with an error (non-zero) code.
     *
     * @return bool
     */
    public function atLeastOneActionFailed(): bool
    {
        return $this->returnCodeStack->anyErrored();
    }

    /**
     * Get all collected return codes in order of execution
     *
     * @return int[]
     */
    public function getAllReturnCodes(): array
    {
        return $this->returnCodeStack->all();
    }

    public function resetApplication(): self
    {
        $this->application->reset();

        return $this;
    }

    /**
     * Run a single action
     *
     * **NOTE:** This method ignores the return code stack.
     *
     * @param Action $action
     * @return int
     */
    public function runOne(Action $action): int
    {
        $this->output->writeln("Next action: {$action}", OutputInterface::VERBOSITY_VERBOSE);

        return $action->execute($this->output);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(
            "\n",
            array_map(
                function (Action $action) {
                    return (string)$action;
                },
                $this->actions
            )
        );
    }
}
