<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ObjectProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ObjectFactory implements Factory
{
    /**
     * @param Schema           $schema
     * @param ProcessorBuilder $builder
     * @return ObjectProcessor|null
     */
    public function create(Schema $schema, ProcessorBuilder $builder)
    {
        if (!$this->supports($schema)) {
            return null;
        }

        /** @var ObjectSchema $objectSchema */
        $objectSchema = $schema;
        $processor = $this->instantiate($objectSchema, $builder);

        foreach ($objectSchema->getPropertySchemas() as $propertyName => $propertySchema) {
            $processor->setPropertyProcessor($propertyName, $builder->build($propertySchema));
        }

        return $processor;
    }

    /**
     * @param Schema $schema
     * @return bool
     */
    public function supports(Schema $schema)
    {
        return $schema instanceof ObjectSchema;
    }

    /**
     * @param ObjectSchema     $schema
     * @param ProcessorBuilder $builder
     * @return ObjectProcessor
     */
    abstract protected function instantiate(ObjectSchema $schema, ProcessorBuilder $builder): ObjectProcessor;
}
