<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ComplexTypeProcessor extends ObjectProcessor
{
    /**
     * @var \ReflectionProperty[]
     */
    protected $reflectionProperties = [];

    /**
     * @var string
     */
    protected $defaultClassName;

    /**
     * @var ComplexType[]
     */
    protected $types = [];

    /**
     * ComplexTypeProcessor constructor.
     *
     * @param ObjectSchema $schema
     *
     * @throws \ReflectionException
     */
    public function __construct(ObjectSchema $schema)
    {
        parent::__construct($schema);

        $type = $schema->getComplexType();

        if (null === $type) {
            throw new \InvalidArgumentException("Schema does not have a complex type");
        }

        $this->defaultClassName        = $type->getClassName();
        $this->types[$type->getName()] = $type;

        foreach ($type->getParents() as $parentType) {
            $this->types[$parentType->getName()] = $parentType;
        }

        while (count($type->getChildren()) > 0) {
            foreach ($type->getChildren() as $childType) {
                $this->types[$childType->getName()] = $childType;
            }
            $type = $childType;
        }

        foreach ($this->types as $type) {
            $this->prepareReflectionPropertiesForType($type);
        }
    }

    protected function dehydrateObject($object): \stdClass
    {
        /** @var ObjectSchema $objectSchema */
        $objectSchema = $this->schema;

        $type = $objectSchema->getComplexType();

        $className = $type->getClassName();

        // Iterate up to find common type
        // TODO: make recursive
        if (!$object instanceof $className) {
            foreach ($type->getParents() as $parentType) {
                $objectSchema = $parentType->getSchema();
                $className    = $parentType->getClassName();
                if (!$object instanceof $className) {
                    throw new \OutOfBoundsException();
                }
            }
        }

        // Object is already an instance of current schema. Find best match.
        do {
            $canDescend = false;
            foreach ($type->getChildren() as $childType) {
                $childClassName = $childType->getClassName();
                if ($object instanceof $childClassName) {
                    $canDescend   = true;
                    $className    = $childClassName;
                    $objectSchema = $childType->getSchema();
                    $type         = $objectSchema->getComplexType();
                }
            }
            if (!$canDescend) {
                break;
            }

        } while (count($type->getChildren()) > 0);

        $node = (object)[
            'x-type-name' => $type->getName(),
        ];

        $lineage = [];

        // Determine lineage
        $traverseUp = function (ComplexType $type) use (&$lineage, &$traverseUp) {

            $lineage[] = $type;
            $parents   = $type->getParents();

            if (count($parents)) {
                $traverseUp($parents[0]);
            }
        };

        $traverseUp($type);

        $lineage = array_reverse($lineage);

        $applyProperties = function ($object, $node) use ($lineage) {
            foreach ($lineage as $type) {
                $className    = $type->getClassName();
                $objectSchema = $type->getSchema();

                foreach ($objectSchema->getPropertySchemas() as $name => $propertySchema) {
                    if (!$this->hasReflectionProperty($className, $name)) {
                        if (!isset($this->defaults[$name])) {
                            continue;
                        }
                        $value = $this->defaults[$name];
                    } else {
                        $value = $this->getReflectionProperty($className, $name)->getValue($object);
                    }

                    if ($this->shouldFilterOutputValue($objectSchema->getPropertySchema($name), $value)) {
                        continue;
                    }

                    $node->$name = $this->dehydrateProperty($name, $value);
                }
            }
        };

        $applyProperties($object, $node);

        return $node;
    }

    /**
     * @param string $className
     * @param string $propertyName
     *
     * @return \ReflectionProperty
     */
    protected function getReflectionProperty(string $className, string $propertyName): \ReflectionProperty
    {
        return $this->reflectionProperties[$className][$propertyName];
    }

    /**
     * @param string $className
     * @param string $propertyName
     *
     * @return bool
     */
    protected function hasReflectionProperty(string $className, string $propertyName): bool
    {
        return isset($this->reflectionProperties[$className][$propertyName]);
    }

    /**
     * @param ComplexType $type
     *
     * @throws \ReflectionException
     */
    private function prepareReflectionPropertiesForType(ComplexType $type)
    {
        $className = $type->getClassName();

        $reflector                              = new \ReflectionClass($className);
        $this->reflectionProperties[$className] = [];

        foreach ($reflector->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $this->reflectionProperties[$className][$reflectionProperty->getName()] = $reflectionProperty;
        }

        while ($reflector = $reflector->getParentClass()) {
            foreach ($reflector->getProperties() as $reflectionProperty) {
                $reflectionProperty->setAccessible(true);
                $this->reflectionProperties[$className][$reflectionProperty->getName()] = $reflectionProperty;
            }
        }
    }
}
