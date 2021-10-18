<?php


namespace Silence\Service\Map;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Silence\Service\Repository\IRepository;
use Silence\Service\Repository\Page\Page;
use Silence\Service\Repository\Page\Pages;
use Silence\Service\Repository\Utils;

class Map implements IMap
{
    /**
     * @var IRepository
     */
    protected IRepository $repository;

    /**
     * @var array
     */
    protected array $fields;

    /**
     * @var array
     */
    protected static array $modelWithRepo = [];

    /**
     * Map constructor.
     * @param IRepository $repository
     * @param array $fields
     */
    public function __construct(IRepository $repository, array $fields = [])
    {
        $this->repository = $repository;
        $this->fields = $fields;
    }

    /**
     * @param string $field
     * @return Model|mixed|Page|null
     */
    public function __get(string $field)
    {
        return $this->getAttribute($field);
    }

    /**
     * @param string $field
     * @return mixed
     */
    protected function getAttribute(string $field)
    {
        $model = $this->repository->getModel();

        if ($model->isRelation($field)) {
            $related = $model->{$field}();

            return $this->isManyRelated($related)?
                $this->collection($related):
                $this->hasOne($related);
        }

        if (!is_null($item = $model->getAttribute($field))) {
            return $item;
        }

        return null;
    }

    /**
     * @param $hasOne
     * @return Model|null
     */
    protected function hasOne($hasOne): ?Model
    {
        return ((!is_null($repoClass = $this->getRepositoryByModel($hasOne->getModel())))?
            Utils::getRepoInstanceClosure($repoClass)($hasOne->getResults()):
            $hasOne->getResults());
    }

    /**
     * @param HasMany|BelongsToMany $collection
     * @return Page
     */
    protected function collection($collection): Page
    {
        return (new Pages(
            $collection->getQuery(),
            100,
            ((!is_null($repoClass = $this->getRepositoryByModel($collection->getModel())))?Utils::getRepoInstanceClosure($repoClass):null),
        ))->getPage();
    }

    /**
     * @param Model $model
     * @return string|null
     */
    protected function getRepositoryByModel(Model $model): ?string
    {
        return array_key_exists($class = get_class($model), static::$modelWithRepo)?static::$modelWithRepo[$class]:null;
    }

    /**
     * @param $relation
     * @return bool
     */
    public function isManyRelated($relation): bool
    {
        return ($relation instanceof HasMany || $relation instanceof BelongsToMany);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $model = $this->repository->getModel();

        $base = $model->toArray();

        foreach ($this->mergeToRelationFields() as $relation) {
            if (!array_key_exists($relation, $base)) {
                $related = $model->{$relation}();

                if ($this->isManyRelated($related)) {
                    $base[$relation] = $this->collection($related);
                } else {
                    $base[$relation] = (($resModel = $this->hasOne($related)) instanceof Model)?$resModel->toArray():null;
                }
            }
        }

        return $base;
    }

    /**
     * @return array
     */
    public function mergeToRelationFields(): array
    {
        return [
            //
        ];
    }
}