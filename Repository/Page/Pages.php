<?php


namespace Silence\Service\Repository\Page;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Pages
{
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var \Closure
     */
    protected \Closure $repoFunc;

    /**
     * @var int
     */
    protected int $limit;

    /**
     * @var int
     */
    protected int $page = 1;

    /**
     * @var null|int
     */
    protected $countPrevPage = null;

    /**
     * Pages constructor.
     * @param Builder $query
     * @param \Closure $repoFunc
     * @param $limit
     */
    public function __construct(Builder $query, \Closure $repoFunc, $limit)
    {
        $this->query = $query;
        $this->repoFunc = $repoFunc;
        $this->limit = $limit;
    }

    /**
     * @return Page|null
     */
    public function getPage(): ?Page
    {
        if (!is_null($this->countPrevPage) && $this->countPrevPage < $this->limit) {
            return null;
        }

        /** @var Collection $collection */
        $collection = $this->query->forPage($this->page++, $this->limit)->get();

        if (($this->countPrevPage = $collection->count()) === 0) {
            return null;
        }

        return new Page($collection->map($this->repoFunc), $this);
    }
}