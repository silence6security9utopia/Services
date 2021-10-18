<?php


namespace Silence\Service\Exception;

class ServerException extends \Exception
{
    /**
     * ServerException constructor.
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($message, 500);
    }
}