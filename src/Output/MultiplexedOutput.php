<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Output;

use EFrane\ConsoleAdditions\Exception\MultiplexedOutputException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MultiplexedOutput
 *
 * Send output to multiple destinations. `MultiplexedOutput` will
 * forward all `OutputInterface` methods that change state to
 * all registered interfaces. Thus it makes it possible to send
 * output to as many places as required.
 *
 * This can be useful to for instance log output of complex
 * console commands by combining a `ConsoleOutput` with a `FileOutput`.
 *
 * **Any** interface added to a multiplexed output will inherit the
 * following properties from the multiplexer:
 *
 * - Verbosity
 * - Output Formatter
 * - Decoration State
 *
 * This also applies when changing any of these after instantiation.
 *
 * @package EFrane\ConsoleAdditions
 */
class MultiplexedOutput implements OutputInterface
{
    /**
     * @var int verbosity
     */
    protected int $verbosity = self::VERBOSITY_NORMAL;

    /**
     * @var OutputFormatterInterface
     */
    protected $formatter = null;

    /**
     * @var OutputInterface[]
     */
    protected array $interfaces = [];

    /**
     * MultiplexedOutput constructor.
     * @param OutputInterface[]             $interfaces
     * @param int                           $verbosity
     * @param bool                          $decorated
     * @param OutputFormatterInterface|null $formatter
     */
    public function __construct(
        array $interfaces,
        int $verbosity = self::VERBOSITY_NORMAL,
        bool $decorated = false,
        OutputFormatterInterface $formatter = null
    ) {
        $this->verbosity = $verbosity;

        if (is_null($formatter)) {
            $formatter = new OutputFormatter();
        }

        $this->formatter = $formatter;

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
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * Pass write() call to all registered interfaces
     *
     * @param array<int,string> $messages
     * @param bool $newline
     * @param int $options
     *
     * @return void
     */
    public function write($messages, bool $newline = false, int $options = 0): void
    {
        foreach ($this->interfaces as $interface) {
            $interface->write($messages, $newline, $options);
        }
    }

    /**
     * Pass writeln() call to all registered interfaces
     *
     * @param array<int,string> $messages
     * @param int $options
     * @return void
     */
    public function writeln($messages, int $options = 0)
    {
        foreach ($this->interfaces as $interface) {
            $interface->writeln($messages, $options);
        }
    }

    /**
     * @inheritdoc
     */
    public function getVerbosity(): int
    {
        return $this->verbosity;
    }

    /**
     * Set verbosity for all registered interfaces
     *
     * @param int $level OutputInterface Verbosity Level
     * @return void
     */
    public function setVerbosity(int $level)
    {
        $this->verbosity = $level;

        foreach ($this->interfaces as $interface) {
            $interface->setVerbosity($level);
        }
    }

    /**
     * @inheritdoc
     */
    public function isQuiet(): bool
    {
        return $this->verbosity === OutputInterface::VERBOSITY_QUIET;
    }

    /**
     * @inheritdoc
     */
    public function isVerbose(): bool
    {
        return $this->verbosity >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * @inheritdoc
     */
    public function isVeryVerbose(): bool
    {
        return $this->verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * @inheritdoc
     */
    public function isDebug(): bool
    {
        return $this->verbosity >= OutputInterface::VERBOSITY_DEBUG;
    }

    /**
     * Set decorated flag for all registered interfaces
     *
     * @param bool $decorated
     * @return void
     */
    public function setDecorated(bool $decorated)
    {
        $this->formatter->setDecorated($decorated);

        foreach ($this->interfaces as $interface) {
            $interface->getFormatter()->setDecorated($decorated);
        }
    }

    /**
     * @inheritdoc
     */
    public function isDecorated(): bool
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
     * Set formatter for all registered interfaces
     *
     * @param OutputFormatterInterface $formatter
     * @return void
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        foreach ($this->interfaces as $interface) {
            $interface->setFormatter($formatter);
        }
    }
}
