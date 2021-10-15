<?php


namespace Silence\Service\Structure;


abstract class Structure
{
    /**
     * @var string|null
     */
    protected static ?string $entityAlias = null;

    /**
     * Structure constructor.
     * @param array $data
     * @throws StructureException
     */
    public function __construct(array $data)
    {
        $this->processSetParams($data);
    }

    /**
     * @param array $data
     * @throws StructureException
     */
    protected function processSetParams(array $data)
    {
        foreach ($data as $property => $value) {

            if ($this->setMethod($property, $value)) {
                continue;
            }


            if ($this->setProperty($property, $value)) {
                continue;
            }

            $this->throwException(sprintf('Trying to set an unknown property %s', $property));
        }

        $this->validate();
    }



    /**
     * @param string $property
     * @param $value
     * @return bool
     */
    protected function setMethod(string $property, $value): bool
    {
        if (method_exists($this, $method = 'set'.ucfirst($property))) {
            $this->{$method}($value);

            return true;
        }

        return false;
    }

    /**
     * @param string $property
     * @param $value
     * @return bool
     */
    protected function setProperty(string $property, $value): bool
    {
        if (property_exists($this, $property)) {
            $this->{$property} = $value;

            return true;
        }

        return false;
    }

    protected function validate()
    {
        foreach ($this->validateRules() as $property => $rule) {
            if (property_exists($this, $property)) {
                $this->throwException(sprintf());
            }
        }
    }

    /**
     * @param string $message
     * @param int $status
     * @throws StructureException
     */
    protected function throwException(string $message, $status = 401)
    {
        throw new StructureException($message.' : '.$this->getStringAlias(), $status);
    }

    public function toArray(): array
    {

    }

    /**
     * @return string
     */
    public function getStringAlias(): string
    {
        return static::$entityAlias?:static::class;
    }

    /**
     * @return array
     */
    protected abstract function validateRules(): array;
}