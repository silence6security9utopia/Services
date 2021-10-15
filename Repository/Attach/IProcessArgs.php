<?php


namespace Silence\Service\Repository\Attach;


interface IProcessArgs
{
    /**
     * @return int
     */
    public function getStorageType(): int;

    /**
     * @return string
     */
    public function getEntity(): string;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @return IOptions
     */
    public function options(): IOptions;
}