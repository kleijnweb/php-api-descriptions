<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description;

use KleijnWeb\ApiDescriptions\Description\Visitor\VisiteeMixin;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Parameter implements Element
{
    use VisiteeMixin;

    const IN_BODY = 'body';
    const IN_PATH = 'path';
    const IN_QUERY = 'query';
    const IN_HEADER = 'header';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $collectionFormat;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var string
     */
    protected $in;

    /**
     * @var string|null
     */
    protected $enum;

    /**
     * @var string|null
     */
    protected $pattern;

    /**
     * Definition constructor.
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
     * @return string|null
     */
    public function getEnum()
    {
        return $this->enum;
    }

    /**
     * @return string|null
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return string|null
     */
    public function getCollectionFormat()
    {
        return $this->collectionFormat;
    }

    /**
     * @return string
     */
    public function getIn(): string
    {
        return $this->in;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $location
     *
     * @return bool
     */
    public function isIn(string $location): bool
    {
        return $this->in === $location;
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
