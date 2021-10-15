<?php


namespace Silence\Service\Structure;


use Throwable;

class StructureException extends \Exception
{
    /**
     * StructureException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code, null);
    }

}