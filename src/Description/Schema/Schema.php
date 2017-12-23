<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Schema;

use KleijnWeb\PhpApi\Descriptions\Description\Element;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\VisiteeMixin;

/**
 * Represents standard JSON Schema but with support for complex types
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class Schema implements Element
{
    use VisiteeMixin;

    const TYPE_ANY    = 'any';
    const TYPE_ARRAY  = 'array';
    const TYPE_BOOL   = 'boolean';
    const TYPE_INT    = 'integer';
    const TYPE_NUMBER = 'number';
    const TYPE_NULL   = 'null';
    const TYPE_OBJECT = 'object';
    const TYPE_STRING = 'string';

    const FORMAT_DATE      = 'date';
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
     * @var mixed
     */
    protected $default;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @var bool
     */
    protected $writeOnly = false;

    /**
     * @var array
     */
    protected $examples = [];

    /**
     * Schema constructor.
     *
     * @param \stdClass $definition
     */
    public function __construct(\stdClass $definition)
    {
        foreach (array_keys(get_object_vars($this)) as $propertyName) {
            if (isset($definition->$propertyName)) {
                $this->$propertyName = $definition->$propertyName;
            }
        }
        $this->definition = $definition;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @return bool
     */
    public function isWriteOnly(): bool
    {
        return $this->writeOnly;
    }

    /**
     * @return array
     */
    public function getExamples(): array
    {
        return $this->examples;
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
