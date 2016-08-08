<?php declare(strict_types = 1);
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
     * ComplexType constructor.
     *
     * @param string       $name
     * @param ObjectSchema $schema
     */
    public function __construct(string $name, ObjectSchema $schema)
    {
        $this->name   = $name;
        $this->schema = $schema;
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
