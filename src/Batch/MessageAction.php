<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions\Batch;


use Symfony\Component\Console\Output\OutputInterface;

class MessageAction implements Action
{
    protected string $message;
    protected bool $newLine;
    protected int $verbosity;

    /**
     * MessageAction constructor.
     * @param string $message
     * @param bool   $newLine
     * @param int    $verbosity
     */
    public function __construct(
        string $message,
        bool $newLine = true,
        int $verbosity = OutputInterface::VERBOSITY_NORMAL
    ) {
        $this->message = $message;
        $this->newLine = $newLine;
        $this->verbosity = $verbosity;
    }

    public function execute(OutputInterface $output): int
    {
        $output->write($this->message, $this->newLine, $this->verbosity);

        return 0;
    }

    public function __toString(): string
    {
        return sprintf($this->message.($this->newLine ? "\n" : ''));
    }
}
