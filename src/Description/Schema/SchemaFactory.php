<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Schema;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ClassNameResolver;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SchemaFactory
{
    /**
     * @var array
     */
    private $schemas = [];

    /**
     * @var ComplexType[]
     */
    private $complexTypes = [];

    /**
     * @var ObjectSchema[]
     */
    private $typedSchemas = [];

    /**
     * @var array
     */
    private $definitions = [];

    /**
     * @var ClassNameResolver
     */
    private $classNameResolver;

    /**
     * @param \stdClass|null $definition
     * @param string|null    $name
     *
     * @return Schema
     */
    public function create(\stdClass $definition = null, string $name = null): Schema
    {
        if (!$definition) {
            $definition       = (object)[];
            $definition->type = Schema::TYPE_ANY;
        }
        if (!isset($definition->type)) {
            $definition = clone $definition;
            if (isset($definition->allOf)) {
                foreach ($definition->allOf as $nested) {
                    if (isset($nested->type)) {
                        $definition->type = $nested->type;
                    }
                }
            }
            if (!isset($definition->type)) {
                $definition->type = Schema::TYPE_STRING;
            }
        }
        if (isset($definition->properties)) {
            $definition->type = 'object';
        }

        $index = var_export($definition, true);

        if (!isset($this->schemas[$index])) {
            if ($definition->type == Schema::TYPE_OBJECT) {
                $propertySchemas = (object)[];

                if (isset($definition->properties)) {
                    foreach ($definition->properties as $attributeName => $propertyDefinition) {
                        $propertySchemas->$attributeName = $this->create($propertyDefinition);
                    }
                }

                if (isset($definition->allOf)) {
                    foreach ($definition->allOf as $nested) {
                        if (isset($nested->{'x-ref-id'})) {
                            continue;
                        }
                        if (isset($nested->properties)) {
                            foreach ($nested->properties as $attributeName => $propertyDefinition) {
                                $propertySchemas->$attributeName = $this->create($propertyDefinition);
                            }
                        }
                    }
                    unset($definition->type);
                }

                if ($name === null && isset($definition->{'x-ref-id'})) {
                    $name = $this->getTypeNameFromRefId($definition);
                }

                $schema = new ObjectSchema($definition, $propertySchemas, null, $name);

                if (null !== $name) {
                    if (isset($this->typedSchemas[$name])) {
                        $a = var_export($schema, true);
                        $b = var_export($this->typedSchemas[$name], true);
                        $a2 = var_export($definition, true);
                        $b2 = var_export($this->typedSchemas[$name]->getDefinition(), true);
                        throw new \InvalidArgumentException("Type '$name' already exists");
                    }
                    $this->typedSchemas[$name] = $schema;
                }

            } elseif ($definition->type == Schema::TYPE_ARRAY) {
                $itemsSchema = isset($definition->items) ? $this->create($definition->items) : null;
                $schema      = new ArraySchema($definition, $itemsSchema);
            } elseif ($definition->type == Schema::TYPE_ANY) {
                $schema = new AnySchema($definition);
            } else {
                $schema = new ScalarSchema($definition);
            }

            $this->schemas[$index] = $schema;

            return $schema;
        }

        return $this->schemas[$index];
    }

    /**
     * @return ComplexType[]
     */
    public function resolveTypes(): array
    {
        foreach ($this->typedSchemas as $name => $schema) {
            $schema->setComplexType(new ComplexType(
                $name,
                $schema,
                $this->classNameResolver->resolve($name)
            ));
        }

        foreach ($this->typedSchemas as $name => $schema) {
            $definition = $schema->getDefinition();

            if (isset($definition->allOf)) {
                foreach ($definition->allOf as $partial) {
                    if (isset($partial->{'x-ref-id'})) {
                        $this->typedSchemas[$schema->getXType()]
                            ->getComplexType()
                            ->addParent(
                                $this->typedSchemas[$this->getTypeNameFromRefId($partial)]->getComplexType()
                            );
                    }
                }
            }
        }

        return array_map(function(ObjectSchema $schema){
            return $schema->getComplexType();
        }, $this->typedSchemas);
    }

    /**
     * @param ClassNameResolver $classNameResolver
     */
    public function setClassNameResolver(ClassNameResolver $classNameResolver)
    {
        $this->classNameResolver = $classNameResolver;
    }

    private function getTypeNameFromRefId(\stdClass $definition)
    {
        return substr($definition->{'x-ref-id'}, strrpos($definition->{'x-ref-id'}, '/') + 1);
    }
}
