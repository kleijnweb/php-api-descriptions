<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ComplexType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ObjectSchema
     */
    private $schema;

    /**
     * @var ComplexType[]
     */
    private $parents = [];

    /**
     * @var ComplexType[]
     */
    private $children = [];

    /**
     * @var null|string
     */
    private $className;

    public function __construct(string $name, ObjectSchema $schema, string $className)
    {
        $this->name      = $name;
        $this->schema    = $schema;
        $this->className = $className;
    }

    /**
     * @param ComplexType $child
     *
     * @return ComplexType
     */
    public function addChild(ComplexType $child): ComplexType
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * @param ComplexType $parent
     *
     * @return ComplexType
     */
    public function addParent(ComplexType $parent): ComplexType
    {
        $this->parents[] = $parent;
        return $this;
    }

    /**
     * @return ComplexType[]
     */
    public function getParents(): array
    {
        return $this->parents;
    }

    /**
     * @return ComplexType[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return null|string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ObjectSchema
     */
    public function getSchema(): ObjectSchema
    {
        return $this->schema;
    }
}
