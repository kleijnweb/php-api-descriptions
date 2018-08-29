<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ComplexTypePropertyProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ObjectProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ComplexTypeFactory extends ObjectFactory
{
    const PRIORITY = 400;

    /**
     * @param Schema $schema
     * @return bool
     */
    public function supports(Schema $schema)
    {
        /** @var ObjectSchema $schema */
        return parent::supports($schema) && $schema->hasComplexType();
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * @param ObjectSchema     $schema
     * @param ProcessorBuilder $builder
     *
     * @return ObjectProcessor
     * @throws \ReflectionException
     */
    protected function instantiate(ObjectSchema $schema, ProcessorBuilder $builder): ObjectProcessor
    {
        return new ComplexTypePropertyProcessor($schema);
    }
}
