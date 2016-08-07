<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Schema;

use KleijnWeb\ApiDescriptions\Description\Element;
use KleijnWeb\ApiDescriptions\Description\Visitor\VisiteeMixin;

/**
 * Represents standard JSON Schema but with support for complex types
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class Schema implements Element
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
     * @var \stdClass
     */
    protected $definition;

    /**
     * @var string
     */
    protected $type;

    /**
     * Schema constructor.
     *
     * @param \stdClass $definition
     */
    public function __construct(\stdClass $definition)
    {
        $this->type       = $definition->type;
        $this->definition = $definition;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isPrimitive(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \stdClass
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function isType($type)
    {
        return $this->isPrimitive($type);
    }
}
