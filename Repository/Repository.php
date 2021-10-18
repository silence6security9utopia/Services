<?php


namespace Silence\Service\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Silence\Service\Map\IMap;
use Silence\Service\Map\Map;
use Silence\Service\Repository\Attach\AdapterTrait as AttachTrait;
use Silence\Service\Repository\Attach\AttachHandler;
use Silence\Service\Repository\Page\Pages;

abstract class Repository implements IRepository
{
    use AttachTrait;

    /**
     * @var Model
     */
    private Model $model;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected static int $defaultLimit = 100;

    /**
     * Repository constructor.
     * @param Model|null $model
     * @throws RepositoryException
     */
    public function __construct(Model $model = null)
    {
        $this->model = $model ?? $this->instanceModel();
    }

    /**
     * @param int $option
     * @param Builder|null $builder
     * @param int|null $limit
     * @return Repository|\Silence\Service\Repository\Page\Page|null
     * @throws RepositoryException
     */
    public function select(int $option, Builder $builder = null, ?int $limit = null)
    {
        $this->limit = $limit ?? static::$defaultLimit;

        $call = $this->switchCallSelect($option);

        return $call($this->prepareBuilder($builder));
    }

    /**
     * @param array $data
     * @return bool
     * @throws Attach\OptionsException
     * @throws RepositoryException
     */
    public function insert(array $data): bool
    {
        $rootMData = $this->getDataForRootModel($data);

        $this->model->fill($rootMData);

        $this->attach($data, $this->getSaveCallback(), AttachHandler::INSERT, function (\Throwable $e) {
            throw new RepositoryException(sprintf('AttachException precess INSERT: [%s] message: %s', get_class($e), $e->getMessage()));
        });

        return true;
    }

    /**
     * @return \Closure
     */
    protected function getSaveCallback(): \Closure
    {
        return function () {
            $this->model->save();

            return $this->model;
        };
    }

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        if (!$this->model->exists) {
            return false;
        }

        $rootMData = $this->getDataForRootModel($data);

        $this->model->fill($rootMData);

        $this->attach($data, $this->getSaveCallback(), AttachHandler::UPDATE, function (\Throwable $e) {
            throw new RepositoryException(sprintf('AttachException process UPDATE: [%s] message: %s', get_class($e), $e->getMessage()));
        });

        return true;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->model->exists) {
            return false;
        }

        return $this->model->delete();
    }

    /**
     * @return Builder
     */
    protected function detectBuilder(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @param Builder|null $query
     * @param int $limit
     * @return Builder
     */
    protected function prepareBuilder(?Builder $query): Builder
    {
        return $query ?? $this->model->newQuery();
    }

    /**
     * @param int $option
     * @return \Closure
     * @throws RepositoryException
     */
    protected function switchCallSelect(int $option): \Closure
    {
        switch ($option) {
            case self::ALL:
                return function (Builder $query) {
                    return (new Pages($query, $this->limit, Utils::getRepoInstanceClosure(static::class)))->getPage();
                };
            case self::ONE:
                return function (Builder $query) {
                    return ($model = $query->first())?$this->setModel($model):null;
                };
            case self::FIRST_OR_FAIL:
                return function (Builder $query) {
                    return ($model = $query->firstOrFail())?$this->setModel($model):null;
                };
            default:
                throw new RepositoryException('Options not available');
        }
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     * @return IRepository
     */
    protected function setModel(Model $model): IRepository
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param array $fields
     * @return IMap
     * @throws RepositoryException
     */
    public function map(array $fields = []): IMap
    {
        return $this->instanceMap($fields);
    }

    /**
     * @return Builder
     */
    public function getBuilder(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @param array $fields
     * @return IMap
     * @throws RepositoryException
     */
    protected function instanceMap(array $fields): IMap
    {
        if (($map = $this->mapClass()) instanceof Map) {
            throw new RepositoryException('Map '.$map.' must be instanceof '.Map::class);
        }

        return new $map($this, $fields);
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    protected function instanceModel(): Model
    {
        if (($model = $this->modelClass()) instanceof Model) {
            throw new RepositoryException('Model '.$model.' must be instanceof '.Model::class);
        }

        return new $model();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getDataForRootModel(array $data): array
    {
        $base = [];

        foreach ($this->model->getFillable() as $key) {
            if (array_key_exists($key, $data)) {
                $base[$key] = $data[$key];
            }
        }

        return $base;
    }

    /**
     * @return string
     */
    abstract protected function modelClass(): string;

    /**
     * @return string
     */
    abstract protected function mapClass(): string;
}