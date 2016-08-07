<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * Represents standard JSON Schema but with support for complex types
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ScalarSchema extends Schema
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $format;

    /**
     * ScalarSchema constructor.
     */
    public function __construct(\stdClass $definition)
    {
        parent::__construct($definition);
        $this->format = isset($definition->format) ? $definition->format : null;
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
}
