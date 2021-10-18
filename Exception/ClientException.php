<?php


namespace Silence\Service\Exception;

class ClientException extends \Exception
{
    /**
     * ClientException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "", $code = 400)
    {
        parent::__construct($message, $code);
    }
}