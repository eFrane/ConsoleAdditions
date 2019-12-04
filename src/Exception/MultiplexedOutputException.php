<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Exception;


class MultiplexedOutputException extends \RuntimeException
{
    /**
     * @param object $object
     * @return MultiplexedOutputException
     */
    public static function unsupportedInterfaceClass($object): self
    {
        $className = get_class($object);
        return new self("{$className} must implement \\Symfony\\Components\\Console\\Output\\OutputInterface.");
    }
}
