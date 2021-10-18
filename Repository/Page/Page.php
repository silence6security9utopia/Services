<?php


namespace Silence\Service\Repository\Page;


use Illuminate\Support\Collection;

/**
 * @method array toArray()
 * @method string toJson($options = 0)
 *
 * @package Silence\Service\Repository\Page
 * @see Collection
 */
class Page implements IPage
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var Pages
     */
    protected Pages $list;

    /**
     * Page constructor.
     * @param Collection $collection
     * @param Pages $list
     */
    public function __construct(Collection $collection, Pages $list)
    {
        $this->collection = $collection;
        $this->list = $list;
    }

    /**
     * @return Page|null
     */
    public function next(): ?Page
    {
        return ($page = $this->list->getPage())?null:$page;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param \Closure $func
     */
    public function allMap(\Closure $func): void
    {
        $page = $this;

        do {
            $collection = $page->collection;

            $collection->each($func);
        } while($page = $this->next());
    }

    public function __call($name, $arguments)
    {
        return $this->collection->{$name}(...$arguments);
    }
}