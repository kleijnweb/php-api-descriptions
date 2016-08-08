<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Description\Schema;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;

/**
 * Represents standard JSON Schema but with support for complex types
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ObjectSchema extends Schema
{
    /**
     * @var \stdClass|null
     */
    protected $propertySchemas;

    /**
     * @var ComplexType
     */
    protected $complexType;

    /**
     * @var string|null
     */
    protected $xType;

    /**
     * @var string|null
     */
    protected $xRefId;

    /**
     * ObjectSchema constructor.
     *
     * @param \stdClass      $definition
     * @param null|\stdClass $propertySchemas
     * @param ComplexType    $complexType
     * @param null|string    $xType
     * @param null|string    $xRefId
     */
    public function __construct(
        \stdClass $definition,
        $propertySchemas = null,
        ComplexType $complexType = null,
        $xType = null,
        $xRefId = null
    ) {
        $definition->type = Schema::TYPE_OBJECT;

        parent::__construct($definition);

        $this->propertySchemas = $propertySchemas;
        $this->complexType     = $complexType;
        $this->xType           = $xType;
        $this->xRefId          = $xRefId;
    }

    /**
     * @return \stdClass
     */
    public function getPropertySchemas(): \stdClass
    {
        return $this->propertySchemas;
    }

    /**
     * @param string $name
     *
     * @return Schema
     */
    public function getPropertySchema(string $name): Schema
    {
        if (!isset($this->propertySchemas->$name)) {
            throw new \OutOfBoundsException("Property '$name' does not exist");
        }

        return $this->propertySchemas->$name;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPropertySchema(string $name): bool
    {
        return isset($this->propertySchemas->$name);
    }

    /**
     * @param ComplexType $complexType
     *
     * @return Schema
     */
    public function setComplexType(ComplexType $complexType): Schema
    {
        if ($this->complexType && $this->complexType !== $complexType) {
            throw new \LogicException("Cannot change complex type of schema");
        }
        $this->complexType = $complexType;

        return $this;
    }

    /**
     * @param ComplexType $type
     *
     * @return bool
     */
    public function isComplexType(ComplexType $type): bool
    {
        return $type === $this->complexType;
    }

    /**
     * @return bool
     */
    public function hasComplexType(): bool
    {
        return null !== $this->complexType;
    }

    /**
     * @return ComplexType
     */
    public function getComplexType(): ComplexType
    {
        return $this->complexType;
    }

    /**
     * @return string|null
     */
    public function getXType()
    {
        return $this->xType;
    }

    /**
     * @return string|null
     */
    public function getXRefId()
    {
        return $this->xRefId;
    }

    /**
     * @param string|ComplexType $type
     *
     * @return bool
     */
    public function isType($type): bool
    {
        if ($type instanceof ComplexType) {
            return $this->isComplexType($type);
        }

        return $this->isPrimitive($type);
    }
}
