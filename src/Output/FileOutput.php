<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Output;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * FileOutput
 *
 * Outputting to a file is based on Symfony's streaming output.
 * A typical use case would be to keep a log of what's being outputted
 * for later analysis. This can be achieved easily in conjunction with
 * the `MultiplexedOutput` like so:
 *
 * ```
 * // assuming $output is your current ConsoleOutput
 * $output = new MultiplexedOutput([
 *      $output,
 *      new NativeFileOutput('logfile_of_whats_happening.txt')
 * ]);
 * ```
 *
 * For further documentation on write modes and implementation
 * of concrete file outputs see `FileOutputInterface`.
 *
 * @package EFrane\ConsoleAdditions
 */
abstract class FileOutput extends StreamOutput implements FileOutputInterface
{
    /**
     * @var string filename to write to
     */
    protected $filename = '';

    /**
     * Overriding the StreamOutput stream to be able to set it
     *
     * @var resource
     * @inheritdoc
     */
    protected $stream;

    /**
     * @var int write mode
     * @see FileOutputInterface
     */
    protected $writeMode = self::WRITE_MODE_APPEND;

    /**
     * @var int number of milliseconds writes are debounced
     */
    protected $debounceMilliseconds = 0;

    /**
     * @var array<int,array<string,mixed>> messages that have been kept back during debouncing
     */
    protected $debounceMessageCache = [];

    /**
     * @var float unix micro time of last write
     */
    protected $debounceLastWrite = 0.0;

    /**
     * @var null|\Closure callback to pass actual stream write to if set
     */
    protected $writeCallback = null;

    /**
     * FileOutput constructor.
     */
    public function __construct(
        string $filename,
        int $writeMode = self::WRITE_MODE_APPEND,
        int $verbosity = self::VERBOSITY_NORMAL,
        bool $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        $this->writeMode = $writeMode;
        $this->filename = $filename;

        $this->debounceLastWrite = microtime(true);

        $stream = $this->loadFileStream($filename);

        parent::__construct($stream, $verbosity, $decorated, $formatter);
    }

    public function __destruct()
    {
        // make sure that any remaining debounced writes are processed
        $this->debounceMilliseconds = 0;
        if (count($this->debounceMessageCache)) {
            $this->doDebouncedWrite();
        }

        if (is_resource($this->stream) && 'stream' === get_resource_type($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * Perform the stream write respecting the debounce settings
     *
     * @return void
     */
    public function doWrite(string $message, bool $newline)
    {
        if ($this->shouldDoWriteImmediate()) {
            if (is_null($this->writeCallback)) {
                parent::doWrite($message, $newline);
            }

            if (is_callable($this->writeCallback)) {
                call_user_func_array($this->writeCallback, [$message, $newline]);
            }

            return;
        }

        $this->storeMessageForDebouncedWrite($message, $newline);

        if ($this->shouldDoWriteDebounced()) {
            $this->doDebouncedWrite();
        }
    }

    /**
     * @return bool
     */
    protected function shouldDoWriteImmediate()
    {
        return 0 === $this->debounceMilliseconds;
    }

    /**
     * @param string $message
     * @param bool $newline
     * @return void
     */
    protected function storeMessageForDebouncedWrite(string $message, bool $newline)
    {
        array_push($this->debounceMessageCache, compact('message', 'newline'));
    }

    /**
     * @return bool
     */
    protected function shouldDoWriteDebounced()
    {
        $possibleWriteTime = $this->debounceLastWrite + ($this->debounceMilliseconds / 1000);

        return $possibleWriteTime <= microtime(true);
    }

    /**
     * Since the whole point of debouncing writes is to be conservative
     * about disk writes, this cannot simply call the upstream
     * `doWrite` implementation as that flushes after each write. It
     * is much more efficient to write all messages to the stream first
     * and only flush it when done.
     *
     * @return void
     */
    protected function doDebouncedWrite()
    {
        while (0 < count($this->debounceMessageCache)) {
            list($message, $newline) = array_values(array_shift($this->debounceMessageCache));

            // this is basically StreamOutput::doWrite but oh well
            if (is_null($this->writeCallback)
                && false === @fwrite($this->getStream(), $message)
                || ($newline && (false === @fwrite($this->getStream(), PHP_EOL)))) {
                // should never happen
                throw new \RuntimeException('Unable to write output.');
            }

            if (is_callable($this->writeCallback)) {
                call_user_func_array($this->writeCallback, [$message, $newline]);
            }
        }

        if (!is_callable($this->writeCallback)) {
            fflush($this->getStream());
        }

        $this->debounceLastWrite = microtime(true);
    }

    /**
     * How many milliseconds are file writes debounced?
     *
     * @return int
     */
    public function getDebounceMilliseconds()
    {
        return $this->debounceMilliseconds;
    }

    /**
     * Set the amount of milliseconds file writes are debounced
     *
     * @param int $debounceMilliseconds
     * @return void
     */
    public function setDebounceMilliseconds($debounceMilliseconds)
    {
        $this->debounceMilliseconds = $debounceMilliseconds;
    }

    /**
     * @param \Closure|null $callback
     * @return void
     */
    public function setWriteCallback(\Closure $callback = null)
    {
        $this->writeCallback = $callback;
    }
}
