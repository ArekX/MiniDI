<?php
/**
 * StackUnderflowException.php
 *
 * Author: Aleksandar Panic <aleksandar.panic@2amigos.us>
 * Timestamp: 05-Feb-17 14:41
 */

namespace ArekX\MiniDI\Exception;

use Exception;

/**
 * Class StackUnderflowException
 * @package ArekX\MiniDI\Exception
 *
 * Exception which is thrown when trying to pop from an empty dependency stack.
 */
class StackUnderflowException extends InjectorException
{
    /**
     * StackUnderflowException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct("Injection stack is empty!");
    }
}