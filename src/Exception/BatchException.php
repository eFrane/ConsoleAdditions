<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Exception;


use RuntimeException;

class BatchException extends RuntimeException
{
    /**
     * @param array<string,mixed> $commandArray
     * @return BatchException
     */
    public static function commandArrayFormatMismatch(array $commandArray): self
    {
        $arrayKeyList = implode(', ', array_keys($commandArray));

        return new self("Expected array with keys 'command' and 'input', instead got these keys: {$arrayKeyList}");
    }

    public static function missingSymfonyProcess(): self
    {
        return new self(
            'Missing Process class, please add `symfony/process` to your composer dependencies to use this function.'
        );
    }

    public static function invalidActionSet(): self
    {
        return new self(
            'Invalid action set provided, all actions in the array must be an instance of EFrane\ConsoleAddtions\Batch\Action'
        );
    }

    /**
     * @param mixed $command
     * @return BatchException
     */
    public static function invalidShellCommandType($command): self
    {
        return new self('Invalid shell command type, expected string or array, got: '.gettype($command));
    }

    public static function applicationMustNotBeNull(): self
    {
        return new self('Application must not be null');
    }
}
