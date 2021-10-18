<?php


namespace Silence\Service\Repository;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Silence\Service\Map\IMap;

interface IRepository
{
    /**
     * All records
     */
    public const ALL = 12;

    /**
     * First record or fail
     */
    public const FIRST_OR_FAIL = 13;

    /**
     * One record
     */
    public const ONE = 14;

    /**
     * @param int $option
     * @param Builder|null $builder
     * @param int $limit
     * @return |\Low\Service\Repository\Repository|null
     */
    public function select(int $option, Builder $builder = null, int $limit = 100);

    /**
     * @param array $data
     * @return boolean
     */
    public function insert(array $data): bool;

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool;

    /**
     * @return bool
     */
    public function delete(): bool;

    /**
     * @return Builder
     */
    public function getBuilder(): Builder;

    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @param array $fields
     * @return IMap|null
     */
    public function map(array $fields = []): ?IMap;
}