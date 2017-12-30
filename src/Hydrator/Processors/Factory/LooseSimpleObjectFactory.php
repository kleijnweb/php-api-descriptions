<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\AnyProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\LooseSimpleObjectProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ObjectProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class LooseSimpleObjectFactory extends ObjectFactory
{
    const PRIORITY = 500;

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

        if (isset($schema->getDefinition()->additionalProperties)
            && !$schema->getDefinition()->additionalProperties) {
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
    public function instantiate(ObjectSchema $schema, ProcessorBuilder $builder): ObjectProcessor
    {
        /** @var AnyProcessor $anyProcessor */
        $anyProcessor = $builder->build(new AnySchema());

        return new LooseSimpleObjectProcessor($schema, $anyProcessor);
    }
}
