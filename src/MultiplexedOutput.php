<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions;

use EFrane\ConsoleAdditions\Exception\MultiplexedOutputException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MultiplexedOutput implements OutputInterface
{
    protected $verbosity = self::VERBOSITY_NORMAL;

    /**
     * @var OutputFormatterInterface
     */
    protected $formatter = null;

    /**
     * @var OutputInterface[]
     */
    protected $interfaces = [];

    /**
     * MultiplexedOutput constructor.
     * @param OutputInterface[]             $interfaces
     * @param int                           $verbosity
     * @param bool|null                     $decorated
     * @param OutputFormatterInterface|null $formatter
     */
    public function __construct(
        array $interfaces,
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        $this->verbosity = $verbosity;
        $this->formatter = $formatter;

        if (is_null($this->formatter)) {
            $this->formatter = new OutputFormatter();
        }

        foreach ($interfaces as $interface) {
            if (!is_a($interface, OutputInterface::class)) {
                throw MultiplexedOutputException::unsupportedInterfaceClass($interface);
            }

            $interface->setVerbosity($verbosity);
            $interface->setFormatter($this->formatter);
            $interface->setDecorated($decorated);

            array_push($this->interfaces, $interface);
        }
    }

    /**
     * @return OutputInterface[] the multiplexed interfaces
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @inheritdoc
     */
    public function write($messages, $newline = false, $options = 0)
    {
        foreach ($this->interfaces as $interface) {
            $interface->write($messages, $newline, $options);
        }
    }

    /**
     * @inheritdoc
     */
    public function writeln($messages, $options = 0)
    {
        foreach ($this->interfaces as $interface) {
            $interface->writeln($messages, $options);
        }
    }

    /**
     * @inheritdoc
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * @inheritdoc
     */
    public function setVerbosity($level)
    {
        $this->verbosity = $level;

        foreach ($this->interfaces as $interface) {
            $interface->setVerbosity($level);
        }
    }

    /**
     * @inheritdoc
     */
    public function isQuiet()
    {
        return $this->verbosity === OutputInterface::VERBOSITY_QUIET;
    }

    /**
     * @inheritdoc
     */
    public function isVerbose()
    {
        return $this->verbosity === OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * @inheritdoc
     */
    public function isVeryVerbose()
    {
        return $this->verbosity === OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * @inheritdoc
     */
    public function isDebug()
    {
        return $this->verbosity === OutputInterface::VERBOSITY_DEBUG;
    }

    /**
     * @inheritdoc
     */
    public function setDecorated($decorated)
    {
        $this->formatter->setDecorated($decorated);
    }

    /**
     * @inheritdoc
     */
    public function isDecorated()
    {
        return $this->formatter->isDecorated();
    }

    /**
     * @inheritdoc
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @inheritdoc
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        foreach ($this->interfaces as $interface) {
            $interface->setFormatter($formatter);
        }
    }
}