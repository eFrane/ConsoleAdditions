<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
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
}