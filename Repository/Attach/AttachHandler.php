<?php


namespace Silence\Service\Repository\Attach;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Silence\Service\Repository\IRepository;
use Silence\Service\Repository\Repository;

class AttachHandler
{
    /**
     * @var Model|null
     */
    protected ?Model $rootModel = null;

    /**
     * @var int
     */
    protected int $process;

    public const UPDATE = 12;
    public const INSERT = 13;

    protected const REPOSITORY = 14;
    protected const MODEL = 15;

    /**
     *
     */
    public const ALIAS = [
        self::INSERT => 'Insert',
        self::UPDATE => 'Update',
        self::MODEL => 'Model',
        self::REPOSITORY => 'Repository'
    ];

    /**
     * AttachHandler constructor.
     * @param int $process
     */
    public function __construct(int $process)
    {
        $this->process = $process;
    }

    /**
     * @param array $data
     * @param \Closure $rootModelSave
     * @param array $relations
     * @param int $process
     * @param \Closure $callReject
     */
    public static function processAttach(array $data, \Closure $rootModelSave, array $relations, int $process, \Closure $callReject)
    {
         $handler = new self($process);

         $handler->attach($data, $rootModelSave, $relations, $callReject);
    }

    /**
     * @param array $data
     * @param $saveCall
     * @param array $relations
     * @param \Closure $callReject
     */
    protected function attach(array $data, $saveCall, array $relations, \Closure $callReject)
    {
        try {
            DB::beginTransaction();

            $this->saveRootModel($saveCall);

            foreach ($relations as $entity => $options) {
                $options = $this->makeOptions($options);

                if (array_key_exists($section = $options->getSection(), $data)) {
                    $dataSection = $data[$section];

                    $this->callProcess(new ProcessArgs(
                        $options,
                        $entity,
                        $this->getStorageType($options->isRepository()),
                        $dataSection
                    ));
                } else {
                    if ($this->process === self::UPDATE) {
                        continue;
                    }

                    if (!$options->isRequire()) {
                        continue;
                    }

                    throw new AttachException('Attach for entity '.$entity.' is require. Section '.$section.' is not found.');
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $callReject($e);
        }
    }

    /**
     * @param bool $isRepository
     * @return int
     */
    protected function getStorageType(bool $isRepository): int
    {
        return $isRepository?self::REPOSITORY:self::MODEL;
    }

    /**
     * @param \Closure $closure
     */
    protected function saveRootModel(\Closure $closure)
    {
        $this->rootModel = $closure();
    }

    /**
     * @param array $options
     * @return IOptions
     * @throws OptionsException
     */
    protected function makeOptions(array $options): IOptions
    {
        return new Options($options);
    }

    /**
     * @param string $repository
     * @return IRepository
     * @throws AttachException
     */
    protected function makeRepository(string $repository, ?Model $model = null): IRepository
    {
        if (!(($repo = new $repository($model)) instanceof Repository)) {
            throw new AttachException('Repository '.$repository.' must be instanceof '.Repository::class);
        }

        return $repo;
    }

    /**
     * @param string $model
     * @return Model
     * @throws AttachException
     */
    protected function makeModel(string $model): Model
    {
        if (!(($model = new $model()) instanceof Model)) {
            throw new AttachException('Model '.$model.' must be instanceof '.Model::class);
        }

        return $model;
    }

    /**
     * @param IProcessArgs $processArgs
     * @throws AttachException
     */
    protected function processInsertForModel(IProcessArgs $processArgs)
    {
        $options = $processArgs->options();
        $data = $processArgs->getData();

        $model = $this->makeModel($modelClass = $processArgs->getEntity());

        $relationMethod = (!is_null($method = $options->getRMethod()) && method_exists($model, $method))?$method:(string) $options->getSection();

        $relation = $this->rootModel->{$relationMethod}();

        if ($options->isMany()) {
            $relation->saveMany(array_map(function ($arrayData) use ($modelClass) {
                return ($this->makeModel($modelClass))->fill($arrayData);
            }, $data));
        } else {
            $relation->save($model->fill($data));
        }
    }

    /**
     * @param IProcessArgs $processArgs
     * @throws AttachException
     */
    protected function processInsertForRepository(IProcessArgs $processArgs)
    {
        $data = $processArgs->getData();
        $options = $processArgs->options();
        $repoClass = $processArgs->getEntity();

        if (is_null($fk = $options->getFk())) {
            throw new AttachException('Options Foreign key is require for repository ('.$repoClass.')');
        }

        $rootId = $this->rootModel->id;

        if ($options->isMany()) {
            foreach ($data as $v) {
                $v[$fk] = $rootId;
                $this->makeRepository($repoClass)->insert($v);
            }
        } else {
            $dataSection[$fk] = $rootId;
            $this->makeRepository($repoClass)->insert($dataSection);
        }
    }

    /**
     * @param IProcessArgs $processArgs
     * @throws AttachException
     */
    protected function processUpdateForModel(IProcessArgs $processArgs)
    {
        $relation = $this->rootModel->{$methodName = $this->getRelationMethodName($processArgs->options())};

        if ($relation instanceof Model) {
            $relation->fill($processArgs->getData())
                    ->save();
        } elseif ($relation instanceof Collection) {
            $ids = [];

            foreach ($processArgs->getData() as $data) {
                if (array_key_exists('id', $data)) {
                    if ($model = $relation->find($id = $data['id'])) {
                        $ids[] = $id;
                        $model->fill($data)
                            ->save();
                    } else {
                        throw new AttachException(sprintf('Model by id %d not found. For entity %s', $id, $processArgs->getEntity()));
                    }
                } else {
                    $model = ($this->makeModel($processArgs->getEntity()))
                        ->fill($data);

                    $this->rootModel->{$methodName}()->save($model);
                }
            }

            $relation->each(function (Model $model) use ($ids) {
                if (!in_array($model->getKey(), $ids, true)) {
                    $model->delete();
                }
            });
        } else {
            throw new AttachException(sprintf('Relation method %s returned %s.Must be returned Model or Collection', $methodName, gettype($relation)));
        }
    }

    /**
     * @param IProcessArgs $processArgs
     * @throws AttachException
     */
    protected function processUpdateForRepository(IProcessArgs $processArgs)
    {
        $relation = $this->rootModel->{$methodName = $this->getRelationMethodName($processArgs->options())};

        if ($relation instanceof Model) {
            $this->makeRepository($processArgs->getEntity(), $relation)
                ->update($processArgs->getData());
        } elseif ($relation instanceof Collection) {
            $ids = [];

            foreach ($processArgs->getData() as $data) {
                if (array_key_exists('id', $data)) {
                    if ($model = $relation->find($id = $data['id'])) {
                        $ids[] = $id;
                        $this->makeRepository($processArgs->getEntity(), $model)
                            ->update($data);
                    } else {
                        throw new AttachException(sprintf('Model by id %d not found. For entity %s', $id, $processArgs->getEntity()));
                    }
                } else {
                    $data[$processArgs->options()->getFk()] = $this->rootModel->id;

                    $this->makeRepository($processArgs->getEntity())
                        ->insert($data);
                }
            }

            $relation->each(function (Model $model) use ($ids) {
                if (!in_array($model->getKey(), $ids, true)) {
                    $model->delete();
                }
            });
        } else {
            throw new AttachException(sprintf('Relation method %s returned %s.Must be returned Model or Collection', $methodName, gettype($relation)));
        }
    }

    /**
     * @param IOptions $options
     * @return string
     */
    protected function getRelationMethodName(IOptions $options): string
    {
        return (!is_null($options->getRMethod()))?$options->getRMethod():(string) $options->getSection();
    }

    /**
     * @param int $storageEntity
     * @return string
     */
    protected function getProcessMethodName(int $storageEntity): string
    {
        return sprintf('process%sFor%s', $this->getProcessAlias(), $this->getStorageEntityAlias($storageEntity));
    }

    /**
     * @param IProcessArgs $processArgs
     */
    protected function callProcess(IProcessArgs $processArgs): void
    {
        $methodName = $this->getProcessMethodName($processArgs->getStorageType());

        $this->{$methodName}($processArgs);
    }

    /**
     * @return string
     */
    protected function getProcessAlias(): string
    {
        return self::ALIAS[$this->process];
    }

    /**
     * @param int $storageEntity
     * @return string
     */
    protected function getStorageEntityAlias(int $storageEntity): string
    {
        return self::ALIAS[$storageEntity];
    }
}