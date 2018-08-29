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
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ObjectProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\StrictSimpleObjectProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class StrictSimpleObjectFactory extends ObjectFactory
{
    const PRIORITY = 600;

    /**
     * @param Schema $schema
     * @return bool
     */
    public function supports(Schema $schema): bool
    {
        /** @var ObjectSchema $schema */
        if (!parent::supports($schema) || $schema->hasComplexType()) {
            return false;
        }

        if (!isset($schema->getDefinition()->additionalProperties)
            || $schema->getDefinition()->additionalProperties) {
            return false;
        }

        return true;
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
     * @return ObjectProcessor
     */
    protected function instantiate(ObjectSchema $schema, ProcessorBuilder $builder): ObjectProcessor
    {
        return new StrictSimpleObjectProcessor($schema);
    }
}
