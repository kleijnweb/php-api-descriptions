<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\VisiteeMixin;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Parameter implements Element
{
    use VisiteeMixin;

    const IN_BODY   = 'body';
    const IN_PATH   = 'path';
    const IN_QUERY  = 'query';
    const IN_HEADER = 'header';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var string|null
     */
    protected $collectionFormat;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var string
     */
    protected $in;

    /**
     * Parameter constructor.
     *
     * @param string $name
     * @param bool   $required
     * @param Schema $schema
     * @param string $in
     * @param string $collectionFormat
     */
    public function __construct(string $name, bool $required, Schema $schema, string $in, $collectionFormat = null)
    {
        $this->name             = $name;
        $this->collectionFormat = $collectionFormat;
        $this->schema           = $schema;
        $this->in               = $in;
        $this->required         = $required;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return string|null
     */
    public function getCollectionFormat()
    {
        return $this->collectionFormat;
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
    public function getIn(): string
    {
        return $this->in;
    }
}
