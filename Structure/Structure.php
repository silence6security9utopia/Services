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

    /**
     * @throws StructureException
     */
    protected function validate()
    {
        foreach ($this->validateRules() as $property => $rule) {
            if (property_exists($this, $property)) {
                $this->throwException(sprintf('Undefind property %s, [process validation]', $property), 500);
            }

            if (is_bool($res = $rule($property)) && $res === true) {
                continue;
            }

            $this->throwException(sprintf('Property %s no valid: message = %s', $property, (string)$res));
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

    /**
     * @param bool $setNull
     * @return array
     * @throws StructureException
     */
    public function toArray(bool $setNull = true): array
    {
        $base = [];

        $properties = (new \ReflectionClass($this))
            ->getProperties(\ReflectionProperty::IS_PUBLIC);

        $map = $this->getMapper();

        foreach ($properties as $property) {
            $nameProperty = $property->name;

            if (!$property->isInitialized($this)) {
                $this->throwException(sprintf('Require property [%s]', $nameProperty));
                continue;
            }

            $value = $property->getValue($this);

            if ($value instanceof Structure) {
                $base[$nameProperty] = $value->toArray($setNull);
            } elseif (is_array($value)) {
                $base[$nameProperty] = $map($value);
            } else {
                if (is_null($value)) {
                    if ($setNull) {
                        $base[$nameProperty] = $value;
                    }
                } else {
                    $base[$nameProperty] = $value;
                }
            }
        }

        return $base;
    }

    /**
     * @return \Closure
     */
    protected function getMapper(): \Closure
    {
        return $mapper = static function ($value) use (&$mapper) {
            return array_map(static fn ($v) => (is_array($v) === true) ? $mapper($v) : (($v instanceof Structure) ? $v->toArray() : $v), $value);
        };
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
    protected function validateRules(): array
    {
        return [
            //
        ];
    }
}