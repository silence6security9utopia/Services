<?php


namespace Silence\Service\Repository\Attach;


interface IBelongsToMany
{
    /**
     *
     */
    public const METHOD = 11;

    /**
     * @return string
     */
    public function getMethod(): ?string;
}