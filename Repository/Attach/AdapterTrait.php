<?php


namespace Silence\Service\Repository\Attach;

trait AdapterTrait
{
    /**
     * @var array
     */
    protected static $relations = [];

    /**
     * @param array $data
     * @param \Closure $rootModel
     * @param int $process
     * @param callable $rejectCallback
     */
    protected function attach(array $data, \Closure $rootModel, int $process, callable $rejectCallback)
    {
        AttachHandler::processAttach($data, $rootModel, static::$relations, $process, $rejectCallback);
    }
}