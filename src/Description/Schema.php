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
class Schema implements Element
{
    use VisiteeMixin;

    const TYPE_ANY = 'any';
    const TYPE_ARRAY = 'array';
    const TYPE_BOOL = 'boolean';
    const TYPE_INT = 'integer';
    const TYPE_NUMBER = 'number';
    const TYPE_NULL = 'null';
    const TYPE_OBJECT = 'object';
    const TYPE_STRING = 'string';

    const FORMAT_DATE = 'date';
    const FORMAT_DATE_TIME = 'date-time';

    const FORMAT_INT32 = 'int32';
    const FORMAT_INT64 = 'int64';

    /**
     * @var Schema|null
     */
    protected $itemsSchema;

    /**
     * @var \stdClass|null
     */
    protected $propertySchemas;

    /**
     * @var \stdClass
     */
    protected $definition;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var string|null
     */
    protected $xType;

    /**
     * @var string|null
     */
    protected $xRefId;

    /**
     * @var array
     */
    private static $schemas = [];

    /**
     * @var array
     */
    private static $definitions = [];

    /**
     * @param \stdClass|null $definition
     *
     * @return Schema
     */
    public static function get(\stdClass $definition = null): Schema
    {
        if (!$definition) {
            $definition       = (object)[];
            $definition->type = self::TYPE_ANY;
        }
        if (!isset($definition->type)) {
            $definition       = clone $definition;
            $definition->type = self::TYPE_STRING;
        }
        if (isset($definition->properties)) {
            $definition->type = 'object';
        }

        $index = array_search($definition, self::$definitions);

        if (false === $index) {
            $schema              = new Schema($definition);
            self::$definitions[] = $definition;
            self::$schemas[]     = $schema;

            return $schema;
        }

        return self::$schemas[$index];
    }

    /**
     * Schema constructor.
     *
     * @param \stdClass $definition
     */
    private function __construct(\stdClass $definition)
    {
        if ($definition->type == self::TYPE_OBJECT) {
            $this->propertySchemas = (object)[];

            foreach (isset($definition->properties) ? $definition->properties : [] as $attributeName => $propertyDefinition) {
                $this->propertySchemas->$attributeName = self::get($propertyDefinition);
            }
        }

        if ($definition->type == self::TYPE_ARRAY && isset($definition->items)) {
            $this->itemsSchema = self::get($definition->items);
        }

        $this->definition = $definition;
        $this->type       = $definition->type;

        $this->format = isset($definition->format) ? $definition->format : null;
        $this->xType  = isset($definition->{'x-type'}) ? $definition->{'x-type'} : null;
        $this->xRefId = isset($definition->{'x-ref-id'}) ? $definition->{'x-ref-id'} : null;
    }

    /**
     * @return bool
     */
    public function isDateTime(): bool
    {
        return $this->isType(self::TYPE_STRING)
        && ($this->hasFormat(self::FORMAT_DATE) || $this->hasFormat(self::FORMAT_DATE_TIME));
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * @param string $type
     *
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return bool
     */
    public function hasFormat(string $format): bool
    {
        return $this->format === $format;
    }

    /**
     * @return Schema
     */
    public function getItemsSchema(): Schema
    {
        return $this->itemsSchema;
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
     * @return \stdClass
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
