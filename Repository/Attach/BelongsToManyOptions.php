<?php


namespace Silence\Service\Repository\Attach;


class BelongsToManyOptions implements IBelongsToMany
{
    /**
     * @var string|mixed|null
     */
    protected ?string $method;

    /**
     * BelongsToManyOptions constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->method = $options[self::METHOD] ?? null;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}