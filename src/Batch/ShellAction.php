<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


/**
 * Class ShellAction
 *
 * Convenience action for shell commands.
 *
 * Disables the process timeout by default.
 */
class ShellAction extends ProcessAction
{
    /**
     * ShellAction constructor.
     *
     * @param array<int,string> $command
     * @param string            $cwd
     * @param array<int,string> $env
     * @param mixed             $input
     * @param int               $timeout
     */
    public function __construct(
        array $command,
        string $cwd = null,
        array $env = null,
        $input = null,
        int $timeout = 0
    ) {
        $this->abortIfProcessNotAvailable();

        $process = new \Symfony\Component\Process\Process($command, $cwd, $env, $input, $timeout);

        parent::__construct($process);
    }
}
