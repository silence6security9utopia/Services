<?php


namespace Silence\Service\Repository\Page;


use Illuminate\Support\Collection;

interface IPage
{
    /**
     * @return Page|null
     */
    public function next(): ?Page;

    /**
     * @return Collection
     */
    public function collection(): Collection;

    /**
     * @param \Closure $func
     */
    public function allMap(\Closure $func): void;
}