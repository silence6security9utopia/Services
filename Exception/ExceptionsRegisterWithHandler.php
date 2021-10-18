<?php


namespace Silence\Service\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class ExceptionsRegisterWithHandler implements ExceptionsRegisterInterface
{
    /**
     * @param ExceptionHandler $handler
     * @param \Closure $logClosure
     */
    public static function register(ExceptionHandler $handler, \Closure $logClosure): void
    {
        /**
         *
         */
        $handler->reportable(function (ServerException $e) use ($logClosure) {
            $logClosure($e);
        })->stop();

        /**
         *
         */
        $handler->reportable(function (ClientException $e) {
            //
        })->stop();

        /**
         *
         */
        $handler->renderable(function (ServerException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        });

        /**
         *
         */
        $handler->renderable(function (ClientException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        });

    }
}