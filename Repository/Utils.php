<?php


namespace Silence\Service\Repository;


use Illuminate\Database\Eloquent\Model;

final class Utils
{
    /**
     * @param string $reposClass
     * @return \Closure
     */
    public static function getRepoInstanceClosure(string $reposClass): \Closure
    {
        return function (Model $model) use ($reposClass) {
            return new $reposClass($model);
        };
    }
}