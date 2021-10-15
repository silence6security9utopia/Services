<?php


namespace Silence\Service\Repository\Attach;


interface IOptions
{
    /**
     * Extract from data by key
     */
    public const SECTION = 11;

    /**
     * Method for relation
     */
    public const METHOD_MODEL_RELATION = 16;

    /**
     * Require relation
     */
    public const IS_REQUIRE = 12;

    /**
     * Many attach for relation
     */
    public const IS_MANY = 13;

    /**
     * Repository relation.Default MODEL
     */
    public const IS_REPOSITORY = 14;

    /**
     * Foreign key fro repository
     */
    public const FK = 15;

    /**
     * @return int|string
     */
    public function getSection();

    /**
     * @return string|null
     */
    public function getFk(): ?string;

    /**
     * @return bool
     */
    public function isRequire(): bool;

    /**
     * @return bool
     */
    public function isMany(): bool;

    /**
     * @return bool
     */
    public function isRepository(): bool;

    /**
     * @return string|null
     */
    public function getRMethod(): ?string;
}