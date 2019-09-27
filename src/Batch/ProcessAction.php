<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use EFrane\ConsoleAdditions\Exception\BatchException;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessAction implements Action
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @var string
     */
    protected $stderr;

    /**
     * ProcessAction constructor.
     *
     * @param \Symfony\Component\Process\Process $process
     */
    public function __construct(\Symfony\Component\Process\Process $process)
    {
        $this->abortIfProcessNotAvailable();

        $this->process = $process;
    }

    /**
     * Checks whether symfony/process is available
     * @throws BatchException
     */
    public function abortIfProcessNotAvailable()
    {
        if (!class_exists('Symfony\Component\Process\Process')) {
            throw BatchException::missingSymfonyProcess();
        }
    }

    /**
     * Return process instance to allow customization
     *
     * @return \Symfony\Component\Process\Process
     */
    public function getProcess(): \Symfony\Component\Process\Process
    {
        return $this->process;
    }

    public function execute(OutputInterface $output): int
    {
        $this->process->enableOutput();
        $this->stderr = '';

        $this->process->run(
            function ($type, $out) use ($output) {
                if (\Symfony\Component\Process\Process::OUT === $type) {
                   $output->write($out);
                }

                if (\Symfony\Component\Process\Process::ERR === $type) {
                    $this->stderr .= $out;
                }
            }
        );

        $exitCode = $this->process->getExitCode();

        return is_int($exitCode) ? $exitCode : -1;
    }

    public function __toString(): string
    {
        return $this->process->getCommandLine();
    }
}
