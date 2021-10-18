<?php


namespace Silence\Service\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

interface ExceptionsRegisterInterface
{
    /**
     * @param ExceptionHandler $handler
     * @param \Closure $logClosure
     */
    public static function register(ExceptionHandler $handler, \Closure $logClosure): void;
}