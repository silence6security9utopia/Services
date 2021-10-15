<?php


namespace Silence\Service\Map;

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
     * Map constructor.
     * @param IRepository $repository
     * @param array $fields
     */
    public function __construct(IRepository $repository, array $fields = [])
    {
        $this->repository = $repository;
        $this->fields = $fields;
    }

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
                Utils::getRepoInstanceClosure(get_class($this->repository))($related);
        }

        if (!is_null($item = $model->getAttribute($field))) {
            return $item;
        }

        return null;
    }

    /**
     * @param $collection
     * @return Page
     */
    protected function collection($collection): Page
    {
        return (new Pages($collection->getQuery(), Utils::getRepoInstanceClosure(get_class($this->repository)), 100))->getPage();
    }

    /**
     * @param $relation
     * @return bool
     */
    public function isManyRelated($relation): bool
    {
        return ($relation instanceof HasMany || $relation instanceof BelongsToMany);
    }

    public function toArray(): array
    {

    }

    public function mergeToRelationFields(): array
    {
        return [
        ];
    }
}