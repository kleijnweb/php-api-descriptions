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
abstract class Parameter implements Element
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
}
