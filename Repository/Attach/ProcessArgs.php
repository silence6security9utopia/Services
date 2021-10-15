<?php


namespace Silence\Service\Repository\Attach;


class ProcessArgs implements IProcessArgs
{
    /**
     * @var IOptions
     */
    protected IOptions $options;

    /**
     * @var int
     */
    protected int $storageType;

    /**
     * @var string
     */
    protected string $entity;

    /**
     * @var array
     */
    protected array $data;

    /**
     * ProcessArgs constructor.
     * @param IOptions $options
     * @param string $entity
     * @param int $storageType
     * @param array $data
     */
    public function __construct(
        IOptions $options,
        string $entity,
        int $storageType,
        array $data
    )
    {
        $this->options = $options;
        $this->storageType = $storageType;
        $this->entity = $entity;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getStorageType(): int
    {
        return $this->storageType;
    }

    /**
     * @return IOptions
     */
    public function options(): IOptions
    {
        return $this->options;
    }
}