<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Standard\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiParameter extends Parameter
{
    /**
     * OpenApiParameter constructor.
     *
     * @param \stdClass $definition
     */
    public function __construct(\stdClass $definition)
    {
        $this->name             = $definition->name;
        $this->in               = $definition->in;
        $this->collectionFormat = isset($definition->collectionFormat) ? $definition->collectionFormat : null;
        $this->required         = isset($definition->required) && $definition->required;
        $this->enum             = isset($definition->enum) ? $definition->enum : null;
        $this->pattern          = isset($definition->pattern) ? $definition->pattern : null;

        if ($this->isIn(self::IN_BODY)) {
            $definition->schema       = isset($definition->schema) ? $definition->schema : (object)[];
            $definition->schema->type = 'object';
        }
        if (isset($definition->schema)) {
            $this->schema = Schema::get($definition->schema);
        } else {
            $this->schema = $this->createSchema($definition);
        }
    }

    /**
     * @param \stdClass $definition
     *
     * @return Schema
     */
    protected function createSchema(\stdClass $definition): Schema
    {
        // Remove non-JSON-Schema properties
        $schemaDefinition     = clone $definition;
        $swaggerPropertyNames = [
            'name',
            'in',
            'description',
            'required',
            'allowEmptyValue',
            'collectionFormat'
        ];
        foreach ($swaggerPropertyNames as $propertyName) {
            if (property_exists($schemaDefinition, $propertyName)) {
                unset($schemaDefinition->$propertyName);
            }
        }

        return Schema::get($schemaDefinition);
    }
}
