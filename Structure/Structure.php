<?php


namespace Silence\Service\Structure;


abstract class Structure
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {

        }
    }

    public function validate()
    {

    }

    public function toArray(): array
    {

    }

    /**
     * @return array
     */
    protected abstract function getValidateRules(): array;
}