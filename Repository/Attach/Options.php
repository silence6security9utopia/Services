<?php


namespace Silence\Service\Repository\Attach;


class Options implements IOptions
{
    /**
     * @var int|string
     */
    protected $section;

    /**
     * @var string|null
     */
    protected $fk;

    /**
     * @var string|null
     */
    protected ?string $methodModelRelation;

    /**
     * @var bool
     */
    protected bool $isRequire;

    /**
     * @var bool
     */
    protected bool $isRepository;

    /**
     * @var IBelongsToMany|null
     */
    protected ?IBelongsToMany $belongsToManyOptions;

    /**
     * @var bool
     */
    protected bool $isMany;

    /**
     * Options constructor.
     * @param array $options
     * @throws OptionsException
     */
    public function __construct(array $options)
    {
        $this->section = $options[self::SECTION] ?? null;
        $this->fk = $options[self::FK] ?? null;
        $this->methodModelRelation = $options[self::METHOD_MODEL_RELATION] ?? null;
        $this->isRequire = $options[self::IS_REQUIRE] ?? false;
        $this->isRepository = $options[self::IS_REPOSITORY] ?? false;
        $this->isMany = $options[self::IS_MANY] ?? false;
        $this->belongsToManyOptions = isset($options[self::BELONGS_TO_MANY])?new BelongsToManyOptions($options[self::BELONGS_TO_MANY]):null;

        $this->validate();
    }

    /**
     * @throws OptionsException
     */
    protected function validate(): void
    {
        $this->validateSection();
        $this->validateForeignKey();
    }

    /**
     * @throws OptionsException
     */
    protected function validateSection(): void
    {
        if (is_string($this->section) || is_int($this->section)) {
            return;
        }

        throw new OptionsException('Option section key must be of type string or int');
    }

    /**
     * @throws OptionsException
     */
    protected function validateForeignKey(): void
    {
        if (is_string($this->fk) || is_null($this->fk)) {
            return;
        }

        throw new OptionsException('Option foreign key must be of type string or null');
    }

    /**
     * @return int|string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return string|null
     */
    public function getFk(): ?string
    {
        return $this->fk;
    }

    /**
     * @return bool
     */
    public function isRequire(): bool
    {
        return $this->isRequire;
    }

    /**
     * @return bool
     */
    public function isRepository(): bool
    {
        return $this->isRepository;
    }

    /**
     * @return bool
     */
    public function isMany(): bool
    {
        return $this->isMany;
    }

    /**
     * @return string|null
     */
    public function getRMethod(): ?string
    {
        return $this->methodModelRelation;
    }

    /**
     * @return IBelongsToMany|null
     */
    public function getBelongsToManyOptions(): ?IBelongsToMany
    {
        return $this->belongsToManyOptions;
    }
}