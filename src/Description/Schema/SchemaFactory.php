<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Description\Schema;

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
     * @var array
     */
    private $definitions = [];

    /**
     * @param \stdClass|null $definition
     *
     * @return Schema
     */
    public function create(\stdClass $definition = null): Schema
    {
        if (!$definition) {
            $definition       = (object)[];
            $definition->type = Schema::TYPE_ANY;
        }
        if (!isset($definition->type)) {
            $definition       = clone $definition;
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

        $index = array_search($definition, $this->definitions);

        if (false === $index) {
            if ($definition->type == Schema::TYPE_OBJECT) {
                $propertySchemas = (object)[];

                foreach (isset($definition->properties) ? $definition->properties : [] as $attributeName => $propertyDefinition) {
                    $propertySchemas->$attributeName = $this->create($propertyDefinition);
                }

                if (isset($definition->allOf)) {
                    foreach ($definition->allOf as $nested) {
                        foreach (isset($nested->properties) ? $nested->properties : [] as $attributeName => $propertyDefinition) {
                            $propertySchemas->$attributeName = $this->create($propertyDefinition);
                        }
                    }
                    unset($definition->type);
                }

                $schema = new ObjectSchema($definition, $propertySchemas);
            } elseif ($definition->type == Schema::TYPE_ARRAY) {
                $itemsSchema = isset($definition->items) ? $this->create($definition->items) : null;
                $schema      = new ArraySchema($definition, $itemsSchema);
            } elseif ($definition->type == Schema::TYPE_ANY) {
                $schema = new AnySchema($definition);
            } else {
                $schema = new ScalarSchema($definition);
            }

            $this->definitions[] = $definition;
            $this->schemas[]     = $schema;

            return $schema;
        }

        return $this->schemas[$index];
    }
}
