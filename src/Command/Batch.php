<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Command;


use EFrane\ConsoleAdditions\Batch\Action;
use EFrane\ConsoleAdditions\Batch\InstanceCommandAction;
use EFrane\ConsoleAdditions\Batch\ShellAction;
use EFrane\ConsoleAdditions\Batch\StringCommandAction;
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
    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @var array|Action[]
     */
    protected $actions = [];

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Exception
     */
    protected $lastException;

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

    /**
     * @return Exception
     */
    public function getLastException(): Exception
    {
        return $this->lastException;
    }

    /**
     * @param Exception $e
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
     */
    public function setActions(array $actions)
    {
        foreach ($actions as $action) {
            if (!is_a($action, Action::class)) {
                BatchException::invalidActionSet();
            }
        }

        $this->actions = $actions;
    }

    /**
     * @param string $commandWithSignature
     * @param array  $args
     * @return $this;
     */
    public function add(string $commandWithSignature, ...$args): self
    {
        return $this->addAction(new StringCommandAction($this->application, $commandWithSignature, ...$args));
    }

    /**
     * @param Action $action
     * @return Batch
     */
    public function addAction(Action $action): self
    {
        array_push($this->actions, $action);

        return $this;
    }

    /**
     * @param array|string         $command
     * @param string               $cwd
     * @param array                $env
     * @param resource|string|null $input
     * @param int                  $timeout
     * @return $this
     */
    public function addShell($command, string $cwd = null, array $env = null, $input = null, int $timeout = 0): self
    {
        $command = $this->prepareShellCommand($command);

        return $this->addAction(new ShellAction($command, $cwd, $env, $input, $timeout));
    }

    /**
     * @param $shellCommand
     * @return array|string
     */
    protected function prepareShellCommand($shellCommand)
    {
        if (!in_array(gettype($shellCommand), ['array', 'string'])) {
            throw BatchException::invalidShellCommandType($shellCommand);
        }

        if (is_string($shellCommand)) {
            /**
             * This is StringInput::tokenize() which unfortunately is a private method.
             * I do not like private methods.
             *
             * Maybe there will be a mangical time when this can be replaced with
             *
             * $shellCommand = (new StringInput($shellCommand))->getTokens();
             */
            $tokens = [];

            $length = strlen($shellCommand);
            $cursor = 0;

            while ($cursor < $length) {
                if (preg_match('/\s+/A', $shellCommand, $match, null, $cursor)) {
                } elseif (preg_match(
                    '/([^="\'\s]+?)(=?)('.StringInput::REGEX_QUOTED_STRING.'+)/A',
                    $shellCommand,
                    $match,
                    null,
                    $cursor
                )) {
                    $tokens[] = $match[1].$match[2].stripcslashes(
                            str_replace(['"\'', '\'"', '\'\'', '""'], '', substr($match[3], 1, strlen($match[3]) - 2))
                        );
                } elseif (preg_match('/'.StringInput::REGEX_QUOTED_STRING.'/A', $shellCommand, $match, null, $cursor)) {
                    $tokens[] = stripcslashes(substr($match[0], 1, strlen($match[0]) - 2));
                } elseif (preg_match('/'.StringInput::REGEX_STRING.'/A', $shellCommand, $match, null, $cursor)) {
                    $tokens[] = stripcslashes($match[1]);
                } else {
                    // should never happen
                    throw new InvalidArgumentException(
                        sprintf('Unable to parse input near "... %s ..."', substr($shellCommand, $cursor, 10))
                    );
                }

                $cursor += strlen($match[0]);
            }
            /**
             * End of StringInput::tokenize()
             */

            $shellCommand = $tokens;
        }

        return $shellCommand;
    }

    /**
     * @param array|string $shellCommand
     * @param callable     $configurationCallback (Symfony\Component\Process\Process $process)
     * @return $this
     */
    public function addShellCb($shellCommand, callable $configurationCallback): self
    {
        $shellCommand = $this->prepareShellCommand($shellCommand);

        $action = new ShellAction($shellCommand);

        call_user_func($configurationCallback, $action->getProcess());

        return $this->addAction($action);
    }

    /**
     * @param Command        $command
     * @param InputInterface $input
     * @return $this
     */
    public function addCommandInstance(Command $command, InputInterface $input): self
    {
        return $this->addAction(new InstanceCommandAction($this->application, $command, $input));
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

        $returnValue = 0;

        foreach ($this->actions as $action) {
            $returnValue &= $this->runOne($action);
        }

        return $returnValue;
    }

    /**
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
