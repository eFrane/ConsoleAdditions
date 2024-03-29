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

    public static function applicationMustNotBeNull(): self
    {
        return new self('Application must not be null');
    }
}
