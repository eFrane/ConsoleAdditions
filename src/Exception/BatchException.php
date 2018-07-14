<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Exception;


class BatchException extends \RuntimeException
{
    public static function inputMustNotBeNull()
    {
        return new self('Input must not be null');
    }

    public static function signatureExpected($commandWithSignature)
    {
        return new self('Expected command name with signature, got a value of type ' . gettype($commandWithSignature));
    }

    public static function commandArrayFormatMismatch(array $commandArray) {
        $arrayKeyList = implode(', ', array_keys($commandArray));

        return new self("Expected array with keys 'command' and 'input', instead got these keys: {$arrayKeyList}");
    }

    public static function missingSymfonyProcess()
    {
        return new self('Missing Process class, please add `symfony/process` to your composer dependencies to use this function.');
    }
}
